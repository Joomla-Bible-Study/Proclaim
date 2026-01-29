const fs = require('fs');
const path = require('path');
const csso = require('csso');

const cssDir = path.join(__dirname, 'media/css');

function minifyDir(dir) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            minifyDir(filePath);
        } else if (file.endsWith('.css') && !file.endsWith('.min.css')) {
            const css = fs.readFileSync(filePath, 'utf8');
            const result = csso.minify(css);
            const minFile = filePath.replace('.css', '.min.css');
            
            fs.writeFileSync(minFile, result.css);
            console.log(`Minified: ${file} -> ${path.basename(minFile)}`);
        }
    });
}

console.log('Starting CSS minification...');
if (fs.existsSync(cssDir)) {
    minifyDir(cssDir);
    console.log('CSS minification complete.');
} else {
    console.error(`Directory not found: ${cssDir}`);
}
