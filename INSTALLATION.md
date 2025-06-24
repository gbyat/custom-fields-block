# Installationsanleitung - Custom Fields Block

## Voraussetzungen

- WordPress 5.8 oder höher
- PHP 7.4 oder höher
- Node.js 14 oder höher (für Entwicklung)
- npm oder yarn (für Entwicklung)

## Schnellstart

### 1. Plugin installieren

1. Laden Sie das Plugin herunter
2. Entpacken Sie es in den `/wp-content/plugins/custom-fields-block/` Ordner
3. Aktivieren Sie das Plugin in WordPress Admin → Plugins

### 2. Assets kompilieren (Entwicklung)

```bash
# In das Plugin-Verzeichnis wechseln
cd wp-content/plugins/custom-fields-block

# Abhängigkeiten installieren
npm install

# Assets kompilieren
npm run build
```

### 3. Plugin verwenden

1. Öffnen Sie einen Post oder eine Seite im Block-Editor
2. Fügen Sie einen neuen Block hinzu
3. Suchen Sie nach "Custom Field"
4. Wählen Sie das gewünschte Custom Field aus
5. Konfigurieren Sie die Darstellungsoptionen

## Entwicklung

### Entwicklungsserver starten

```bash
npm run start
```

Dies startet einen Entwicklungsserver, der automatisch Änderungen kompiliert.

### Code formatieren

```bash
npm run format
```

### Linting

```bash
# JavaScript Linting
npm run lint:js

# CSS Linting
npm run lint:css

# JavaScript Linting mit automatischer Korrektur
npm run lint:js:fix
```

## Custom Fields erstellen

### Über Advanced Custom Fields (ACF)

1. Installieren Sie das ACF Plugin
2. Erstellen Sie eine neue Feldgruppe
3. Fügen Sie Felder hinzu (z.B. Text, Textarea, etc.)
4. Weisen Sie die Feldgruppe Ihren Post-Typen zu

### Manuell über WordPress Admin

1. Bearbeiten Sie einen Post
2. Scrollen Sie nach unten zu "Custom Fields"
3. Fügen Sie neue Felder hinzu

### Programmatisch

```php
// Custom Field zu einem Post hinzufügen
add_post_meta($post_id, 'mein_feld', 'Mein Wert');

// Mehrere Werte für ein Feld
add_post_meta($post_id, 'mein_feld', 'Wert 1');
add_post_meta($post_id, 'mein_feld', 'Wert 2');
```

## Troubleshooting

### Plugin wird nicht angezeigt

1. Überprüfen Sie, ob das Plugin aktiviert ist
2. Stellen Sie sicher, dass die Assets kompiliert wurden (`npm run build`)
3. Überprüfen Sie die Browser-Konsole auf JavaScript-Fehler

### Custom Fields werden nicht angezeigt

1. Stellen Sie sicher, dass Custom Fields vorhanden sind
2. Überprüfen Sie, ob die Felder nicht mit einem Unterstrich beginnen
3. Stellen Sie sicher, dass Sie sich im richtigen Post befinden

### Styling-Probleme

1. Überprüfen Sie, ob die CSS-Dateien geladen werden
2. Stellen Sie sicher, dass Ihr Theme die Block-Styles unterstützt
3. Fügen Sie Custom CSS über Ihr Theme hinzu

### Performance-Probleme

1. Stellen Sie sicher, dass die Assets minifiziert sind
2. Überprüfen Sie die Anzahl der Custom Fields
3. Verwenden Sie Caching-Plugins

## Support

Bei Problemen:

1. Überprüfen Sie die WordPress Debug-Logs
2. Testen Sie das Plugin in einer sauberen WordPress-Installation
3. Erstellen Sie ein Issue im GitHub Repository

## Updates

### Plugin aktualisieren

1. Laden Sie die neueste Version herunter
2. Ersetzen Sie die alten Dateien
3. Führen Sie `npm install` und `npm run build` aus
4. Testen Sie die Funktionalität

### Abhängigkeiten aktualisieren

```bash
npm run packages-update
```

## Sicherheit

- Das Plugin verwendet WordPress-Nonces für Sicherheit
- Alle Ausgaben werden ordnungsgemäß escaped
- Custom Fields werden validiert
- Keine direkten Datenbankabfragen ohne Sanitization

## Performance-Tipps

1. Verwenden Sie Caching für Custom Fields
2. Minimieren Sie die Anzahl der Block-Instanzen
3. Verwenden Sie lazy loading für große Datenmengen
4. Optimieren Sie die CSS-Dateien
