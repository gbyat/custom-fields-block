# Remote Deployment Script für Multi-Server
# Verwendung: .\deploy-remote.ps1

param(
    [string]$FtpServer = "",
    [string]$Username = "",
    [string]$Password = "",
    [string]$RemotePath = "/wp-content/plugins/custom-fields-block/",
    [switch]$UseSFTP = $false
)

Write-Host "Remote Deployment"
Write-Host "===================="

# 1. Build erstellen
Write-Host "Erstelle Production Build..."
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "Build fehlgeschlagen!"
    exit 1
}

# 2. Temporäres Verzeichnis erstellen
$TempDir = ".\temp-deploy"
if (Test-Path $TempDir) {
    Remove-Item $TempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $TempDir -Force

Write-Host "Kopiere Plugin-Dateien..."

# Benötigte Dateien kopieren
Copy-Item "custom-fields-block.php" -Destination $TempDir -Force
Copy-Item "README.md" -Destination $TempDir -Force
Copy-Item "INSTALLATION.md" -Destination $TempDir -Force
Copy-Item "build\*" -Destination $TempDir -Recurse -Force
if (Test-Path "languages") {
    Copy-Item "languages" -Destination $TempDir -Recurse -Force
}

# 3. SFTP/FTP Upload
if ($UseSFTP) {
    Write-Host "SFTP Upload..."
    
    # SFTP mit WinSCP
    $WinScpPath = "C:\Program Files (x86)\WinSCP\WinSCP.com"
    if (Test-Path $WinScpPath) {
        Write-Host "Verwende WinSCP für SFTP..."
        
        $Script = @"
option batch abort
option confirm off
open sftp://$Username@$FtpServer -password=$Password
cd $RemotePath
put $TempDir\* ./
exit
"@
        
        try {
            $Script | & $WinScpPath
            if ($LASTEXITCODE -eq 0) {
                Write-Host "SFTP Upload erfolgreich!"
            }
            else {
                Write-Host "SFTP Upload fehlgeschlagen!"
                exit 1
            }
        }
        catch {
            Write-Host "SFTP Fehler: $($_.Exception.Message)"
            exit 1
        }
    }
    else {
        Write-Host "WinSCP nicht gefunden!"
        Write-Host "Bitte installieren Sie WinSCP von: https://winscp.net/"
        Write-Host "Oder verwenden Sie einen anderen SFTP-Client."
        exit 1
    }
}
else {
    Write-Host "FTP Upload..."
    
    # FTP Upload mit PowerShell
    try {
        $ftp = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath")
        $ftp.Credentials = New-Object System.Net.NetworkCredential($Username, $Password)
        $ftp.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftp.UsePassive = $true
        $ftp.UseBinary = $true
        $ftp.KeepAlive = $false
        
        try {
            $response = $ftp.GetResponse()
            $response.Close()
        }
        catch {
            # Verzeichnis existiert bereits
        }
        
        # Dateien hochladen
        Get-ChildItem $TempDir -Recurse | ForEach-Object {
            if ($_.PSIsContainer) {
                # Verzeichnis erstellen
                $dirPath = $_.FullName.Replace($TempDir, "").Replace("\", "/")
                if ($dirPath) {
                    $ftp = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath$dirPath")
                    $ftp.Credentials = New-Object System.Net.NetworkCredential($Username, $Password)
                    $ftp.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftp.UsePassive = $true
                    $ftp.UseBinary = $true
                    $ftp.KeepAlive = $false
                    try {
                        $response = $ftp.GetResponse()
                        $response.Close()
                    }
                    catch {
                        # Verzeichnis existiert bereits
                    }
                }
            }
            else {
                # Datei hochladen
                $filePath = $_.FullName.Replace($TempDir, "").Replace("\", "/")
                $ftp = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath$filePath")
                $ftp.Credentials = New-Object System.Net.NetworkCredential($Username, $Password)
                $ftp.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                $ftp.UsePassive = $true
                $ftp.UseBinary = $true
                $ftp.KeepAlive = $false
                
                $stream = $ftp.GetRequestStream()
                $fileBytes = [System.IO.File]::ReadAllBytes($_.FullName)
                $stream.Write($fileBytes, 0, $fileBytes.Length)
                $stream.Close()
                
                $response = $ftp.GetResponse()
                $response.Close()
                
                Write-Host "  $filePath hochgeladen."
            }
        }
    }
    catch {
        Write-Host "FTP Upload fehlgeschlagen: $($_.Exception.Message)"
        exit 1
    }
}

# 4. Aufräumen
Remove-Item $TempDir -Recurse -Force

Write-Host "Plugin erfolgreich deployed!"
Write-Host ""
Write-Host "Nächste Schritte:"
Write-Host "1. Gehen Sie zu WordPress Admin -> Plugins"
Write-Host "2. Aktivieren Sie 'Custom Fields Block'"
Write-Host "3. Testen Sie den Block im Gutenberg Editor" 