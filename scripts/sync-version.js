const fs = require('fs');
const path = require('path');

const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const version = pkg.version;

const pluginFile = path.join(__dirname, '..', 'custom-fields-block.php');
let content = fs.readFileSync(pluginFile, 'utf8');

// Plugin-Header ersetzen
content = content.replace(
    /(\* Version:\s*)([0-9]+\.[0-9]+\.[0-9]+)/,
    `$1${version}`
);

// CFB_VERSION Konstante ersetzen
content = content.replace(
    /(define\(['"]CFB_VERSION['"],\s*['"])([0-9]+\.[0-9]+\.[0-9]+)(['"]\);)/,
    `$1${version}$3`
);

fs.writeFileSync(pluginFile, content, 'utf8');
console.log(`✅ Plugin-Version auf ${version} synchronisiert.`); 