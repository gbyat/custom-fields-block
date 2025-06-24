# Multi-Server Deployment Script
# Verwendung: .\deploy-config.ps1 [server-name]
# Beispiel: .\deploy-config.ps1 webgo

param(
    [Parameter(Mandatory = $true)]
    [string]$ServerName
)

Write-Host "Multi-Server Deployment"
Write-Host "========================="

# Konfiguration laden
if (!(Test-Path "config.json")) {
    Write-Host "config.json nicht gefunden!"
    Write-Host "Bitte erstellen Sie config.json mit Ihren Server-Daten."
    exit 1
}

$config = Get-Content "config.json" | ConvertFrom-Json
$settings = $config.servers.$ServerName

if (!$settings) {
    Write-Host "Server '$ServerName' nicht in config.json gefunden!"
    Write-Host ""
    Write-Host "Verfügbare Server:"
    $config.servers.PSObject.Properties | ForEach-Object {
        Write-Host "  - $($_.Name): $($_.Value.description)"
    }
    exit 1
}

# Prüfen ob es ein lokaler Server ist
$isLocalServer = $settings.server -eq "localhost" -or $settings.server -eq ""

if ($isLocalServer) {
    Write-Host "Lokales Deployment zu IIS"
    Write-Host "Ziel: $($settings.remotePath)"
    Write-Host "Beschreibung: $($settings.description)"
    Write-Host ""
    
    # Lokales Deployment mit dem ursprünglichen Script
    & ".\deploy-local.ps1" $settings.remotePath
}
else {
    # Remote Deployment mit geladenen Einstellungen
    $params = @{
        FtpServer  = $settings.server
        Username   = $settings.username
        Password   = $settings.password
        RemotePath = $settings.remotePath
        UseSFTP    = $settings.useSFTP
    }

    Write-Host "Verbinde zu: $($settings.server)"
    Write-Host "Benutzer: $($settings.username)"
    Write-Host "Ziel: $($settings.remotePath)"
    Write-Host "Protokoll: $($(if($settings.useSFTP) { 'SFTP' } else { 'FTP' }))"
    Write-Host "Beschreibung: $($settings.description)"
    Write-Host ""

    # Remote Deployment-Script aufrufen
    & ".\deploy-remote.ps1" @params

    if ($LASTEXITCODE -ne 0) {
        Write-Host "Build fehlgeschlagen!"
        exit 1
    }
} 