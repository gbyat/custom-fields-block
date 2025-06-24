param(
    [string]$TargetPath = "C:\inetpub\wwwroot\wp_webentwicklerin\wp-content\plugins\custom-fields-block"
)

Write-Host "WordPress Plugin Deployment"
Write-Host "================================"

if (!(Test-Path $TargetPath)) {
    Write-Host "Erstelle Zielverzeichnis: $TargetPath"
    New-Item -ItemType Directory -Path $TargetPath -Force
}

Write-Host "Erstelle Production Build..."
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "Build fehlgeschlagen!"
    exit 1
}

Write-Host "Kopiere Plugin-Dateien..."

# Hauptdateien
Copy-Item "custom-fields-block.php" -Destination $TargetPath -Force
Copy-Item "README.md" -Destination $TargetPath -Force
Copy-Item "INSTALLATION.md" -Destination $TargetPath -Force

# Build-Dateien
Copy-Item "build\*" -Destination $TargetPath -Force -Recurse

# Languages
if (Test-Path "languages") {
    Copy-Item "languages" -Destination $TargetPath -Recurse -Force
}

Write-Host "Plugin erfolgreich deployed nach: $TargetPath"
Write-Host ""
Write-Host "Nächste Schritte:"
Write-Host "1. Gehen Sie zu WordPress Admin -> Plugins"
Write-Host "2. Aktivieren Sie 'Custom Fields Block'"
Write-Host "3. Testen Sie den Block im Gutenberg Editor" 