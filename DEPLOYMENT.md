# 🚀 Deployment Guide - Custom Fields Block Plugin

## Übersicht

Dieses WordPress-Plugin benötigt **KEIN Gulp** - WordPress Scripts übernimmt das Build-Management.

## 📦 Deployment-Optionen

### Option 1: Remote Deployment zu all-inkl.com (Empfohlen)

#### Schritt 1: Konfiguration einrichten

Bearbeiten Sie `config.json` mit Ihren all-inkl.com Zugangsdaten:

```json
{
  "ftp": {
    "server": "ftp.ihre-domain.de",
    "username": "ihr-ftp-username",
    "password": "ihr-ftp-password",
    "remotePath": "/wp-content/plugins/custom-fields-block/",
    "useSFTP": false
  }
}
```

#### Schritt 2: Deployment ausführen

```powershell
# Mit FTP (Standard)
.\deploy-config.ps1 ftp

# Mit SFTP (falls verfügbar)
.\deploy-config.ps1 sftp

# Oder direkt mit npm
npm run deploy:remote
```

### Option 2: Manueller FTP/SFTP Upload

#### ZIP-Package erstellen:

```powershell
npm run package
```

Erstellt `custom-fields-block.zip` für Upload über:

- FTP-Client (FileZilla, WinSCP)
- WordPress Admin → Plugins → Plugin hochladen

#### Benötigte Dateien für manuellen Upload:

```
custom-fields-block/
├── custom-fields-block.php    (Hauptdatei)
├── README.md                  (Dokumentation)
├── INSTALLATION.md           (Installationsanleitung)
├── build/                    (Gebauter Code)
│   ├── index.js
│   ├── index.asset.php
│   └── block.json
└── languages/                (Übersetzungen)
    ├── custom-fields-block.pot
    └── custom-fields-block-de_DE.po
```

### Option 3: Lokales Deployment (Entwicklung)

#### XAMPP/WAMP/Laragon:

```powershell
npm run deploy:local
```

#### IIS:

```powershell
npm run deploy:production
```

## 🔧 all-inkl.com Konfiguration

### FTP-Zugangsdaten finden:

1. **all-inkl.com Kundencenter** → **Webhosting** → **Ihr Paket**
2. **FTP-Zugang** → **FTP-Benutzer** → **Passwort ändern**
3. **FTP-Server**: `ftp.ihre-domain.de` (oder in den Zugangsdaten)
4. **Benutzername**: Ihr FTP-Benutzer
5. **Passwort**: Ihr FTP-Passwort

### WordPress-Verzeichnisstruktur bei all-inkl.com:

```
/
├── wp-content/
│   ├── plugins/
│   │   └── custom-fields-block/  ← Hier deployen
│   ├── themes/
│   └── uploads/
├── wp-admin/
└── wp-includes/
```

## 📋 Deployment-Checkliste

### Vor dem Deployment:

- [ ] `config.json` mit korrekten Zugangsdaten
- [ ] `npm run build` erfolgreich ausgeführt
- [ ] `build/` Ordner existiert mit Dateien
- [ ] FTP/SFTP-Zugang getestet

### Nach dem Deployment:

- [ ] Plugin in WordPress Admin sichtbar
- [ ] Plugin aktiviert
- [ ] Block im Gutenberg Editor verfügbar
- [ ] Custom Fields funktionieren

## 🛠️ Troubleshooting

### FTP-Verbindungsfehler:

```powershell
# Testen Sie die Verbindung:
ftp ftp.ihre-domain.de
# Benutzername und Passwort eingeben
```

### SFTP-Verbindungsfehler:

```powershell
# WinSCP installieren für SFTP
# Oder auf FTP zurückfallen
```

### Plugin nicht sichtbar:

- Dateien in korrektem Verzeichnis?
- WordPress Cache geleert?
- Plugin-Ordner hat korrekten Namen?

### Build-Fehler:

```powershell
npm install
npm run build
```

## 🔒 Sicherheit

### Zugangsdaten schützen:

- `config.json` ist bereits in `.gitignore`
- Verwenden Sie sichere Passwörter
- SFTP bevorzugen wenn möglich
- Regelmäßig Passwörter ändern

### Alternative: Umgebungsvariablen

```powershell
# In PowerShell setzen:
$env:FTP_SERVER = "ftp.ihre-domain.de"
$env:FTP_USER = "ihr-username"
$env:FTP_PASS = "ihr-password"

# Dann deployen:
.\deploy-remote.ps1 -FtpServer $env:FTP_SERVER -Username $env:FTP_USER -Password $env:FTP_PASS
```

## 🔄 Continuous Deployment

### Mit GitHub Actions:

```yaml
name: Deploy to all-inkl.com
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: windows-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
        with:
          node-version: "16"
      - run: npm install
      - run: npm run build
      - run: |
          echo '{
            "ftp": {
              "server": "${{ secrets.FTP_SERVER }}",
              "username": "${{ secrets.FTP_USER }}",
              "password": "${{ secrets.FTP_PASS }}",
              "remotePath": "/wp-content/plugins/custom-fields-block/",
              "useSFTP": false
            }
          }' > config.json
      - run: .\deploy-config.ps1 ftp
```

## 📞 Support

Bei Problemen:

1. Überprüfen Sie die FTP-Zugangsdaten
2. Testen Sie die Verbindung manuell
3. Kontrollieren Sie die WordPress Debug-Logs
4. Prüfen Sie die all-inkl.com Support-Dokumentation
