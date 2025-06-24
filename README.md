# Custom Fields Block

Ein WordPress Plugin, das es ermöglicht, native WordPress Custom Fields als Blöcke mit umfangreichen Typografie- und Farboptionen einzufügen.

## Features

- **Dropdown-Auswahl**: Wählen Sie aus allen verfügbaren Custom Fields des aktuellen Posts
- **Flexible Darstellung**: Als Überschrift oder Absatz anzeigen
- **Typografie-Optionen**: Schriftgröße, -gewicht, Zeilenhöhe und Buchstabenabstand
- **Farboptionen**: Text- und Hintergrundfarbe konfigurierbar
- **Abstände**: Margin und Padding für oben und unten
- **Ausrichtung**: Links, zentriert, rechts und wide-Alignment
- **Responsive Design**: Optimiert für alle Bildschirmgrößen

## Installation

### Manuelle Installation

1. Laden Sie das Plugin herunter
2. Entpacken Sie es in den `/wp-content/plugins/` Ordner
3. Navigieren Sie zu `Plugins` in Ihrem WordPress Admin
4. Aktivieren Sie das "Custom Fields Block" Plugin

### Entwicklung

1. Klonen Sie das Repository
2. Führen Sie `npm install` aus
3. Führen Sie `npm run build` aus, um die Assets zu kompilieren
4. Aktivieren Sie das Plugin in WordPress

## Verwendung

### Im Block-Editor

1. Fügen Sie einen neuen Block hinzu
2. Suchen Sie nach "Custom Field" oder "Custom Fields Block"
3. Wählen Sie das gewünschte Custom Field aus dem Dropdown
4. Konfigurieren Sie die Darstellungsoptionen:
   - **Anzeigetyp**: Absatz oder Überschrift
   - **Typografie**: Schriftgröße, -gewicht, Zeilenhöhe, Buchstabenabstand
   - **Farben**: Text- und Hintergrundfarbe
   - **Abstände**: Margin und Padding
   - **Ausrichtung**: Links, zentriert, rechts, wide

### Custom Fields erstellen

Das Plugin funktioniert mit allen nativen WordPress Custom Fields. Sie können diese erstellen über:

- **Custom Fields Plugin** (wie Advanced Custom Fields)
- **Manuell** über die WordPress Admin-Oberfläche
- **Programmatisch** mit `add_post_meta()`

## Technische Details

### Unterstützte Custom Fields

Das Plugin erkennt automatisch alle Custom Fields, die:

- Nicht mit einem Unterstrich beginnen (interne WordPress-Felder werden ignoriert)
- Dem aktuellen Post zugeordnet sind

### Block-Attribute

```json
{
  "fieldKey": "string",
  "displayType": "paragraph|heading",
  "typography": {
    "fontSize": "number",
    "fontWeight": "string",
    "lineHeight": "number",
    "letterSpacing": "number"
  },
  "colors": {
    "textColor": "string",
    "backgroundColor": "string"
  },
  "spacing": {
    "marginTop": "number",
    "marginBottom": "number",
    "paddingTop": "number",
    "paddingBottom": "number"
  },
  "alignment": "left|center|right|wide"
}
```

### CSS-Klassen

Das Plugin fügt automatisch CSS-Klassen hinzu:

- `.cfb-block` - Hauptcontainer
- `.has-text-align-{alignment}` - Ausrichtung
- `.has-text-color` - Textfarbe gesetzt
- `.has-background` - Hintergrundfarbe gesetzt

## Anpassungen

### Custom CSS

Sie können das Styling über Ihr Theme anpassen:

```css
/* Beispiel: Custom Styling für alle Custom Field Blöcke */
.cfb-block {
  font-family: "Your Custom Font", sans-serif;
}

/* Beispiel: Spezifisches Styling für Überschriften */
.cfb-block h1,
.cfb-block h2,
.cfb-block h3 {
  border-bottom: 2px solid #007cba;
  padding-bottom: 0.5rem;
}
```

### Hooks und Filter

Das Plugin bietet verschiedene Hooks für Entwickler:

```php
// Custom Fields filtern
add_filter('cfb_custom_fields', function($fields, $post_id) {
    // Ihre Logik hier
    return $fields;
}, 10, 2);

// Block-Ausgabe anpassen
add_filter('cfb_block_output', function($output, $attributes, $field_value) {
    // Ihre Logik hier
    return $output;
}, 10, 3);
```

## Browser-Unterstützung

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## WordPress-Version

- WordPress 5.8 oder höher
- PHP 7.4 oder höher

## Lizenz

GPL v2 oder höher

## Support

Bei Fragen oder Problemen erstellen Sie bitte ein Issue im GitHub Repository.

## Changelog

### Version 1.0.0

- Erste Veröffentlichung
- Grundlegende Custom Field Block-Funktionalität
- Typografie- und Farboptionen
- Responsive Design
- Wide-Alignment Support
