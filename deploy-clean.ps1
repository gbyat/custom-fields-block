# WordPress Plugin Deployment Script
# Verwendung: .\deploy-clean.ps1 [ZIELVERZEICHNIS]

param(
    [string]$TargetPath = "C:\inetpub\wwwroot\wp_webentwicklerin\wp-content\plugins\custom-fields-block"
)

Write-Host "🚀 WordPress Plugin Deployment" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green

# 1. Build erstellen
Write-Host "📦 Erstelle Production Build..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Build fehlgeschlagen!" -ForegroundColor Red
    exit 1
}

# 2. Zielverzeichnis erstellen falls nicht vorhanden
if (!(Test-Path $TargetPath)) {
    Write-Host "📁 Erstelle Zielverzeichnis: $TargetPath" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $TargetPath -Force
}

# 3. Dateien kopieren (nur die benötigten)
Write-Host "📋 Kopiere Plugin-Dateien..." -ForegroundColor Yellow

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

Write-Host "✅ Plugin erfolgreich deployed nach: $TargetPath" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Nächste Schritte:" -ForegroundColor Cyan
Write-Host "1. Gehen Sie zu WordPress Admin → Plugins" -ForegroundColor White
Write-Host "2. Aktivieren Sie 'Custom Fields Block'" -ForegroundColor White
Write-Host "3. Testen Sie den Block im Gutenberg Editor" -ForegroundColor White 