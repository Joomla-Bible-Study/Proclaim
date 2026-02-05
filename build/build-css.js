/* jshint node: true, esversion: 11 */
"use strict";

const fs = require('fs');
const path = require('path');
const csso = require('csso');

const cssDir = path.join(__dirname, '../media/css');

/**
 * Clean up orphaned source map files (legacy .css.map files from old Less build)
 * @param {string} dir - Directory to clean
 */
function cleanupOrphanedMaps(dir) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            cleanupOrphanedMaps(filePath);
        } else if (file.endsWith('.css.map') && !file.endsWith('.min.css.map')) {
            // Remove legacy .css.map files (from old Less build process)
            fs.unlinkSync(filePath);
            console.log(`Removed orphaned map: ${file}`);
        }
    });
}

/**
 * Minify CSS files and generate source maps
 * @param {string} dir - Directory to process
 */
function minifyDir(dir) {
    const files = fs.readdirSync(dir);

    files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            minifyDir(filePath);
        } else if (file.endsWith('.css') && !file.endsWith('.min.css')) {
            const css = fs.readFileSync(filePath, 'utf8');
            const minFile = filePath.replace('.css', '.min.css');
            const mapFile = minFile + '.map';
            const mapFileName = path.basename(mapFile);

            // Minify with source map generation
            const result = csso.minify(css, {
                filename: file,
                sourceMap: true
            });

            // Append source map reference to minified CSS
            const cssWithMapRef = result.css + '\n/*# sourceMappingURL=' + mapFileName + ' */';

            // Write minified CSS
            fs.writeFileSync(minFile, cssWithMapRef);

            // Write source map
            fs.writeFileSync(mapFile, result.map.toString());

            console.log(`Minified: ${file} -> ${path.basename(minFile)} + ${mapFileName}`);
        }
    });
}

console.log('Starting CSS minification...');
if (fs.existsSync(cssDir)) {
    // First, clean up orphaned legacy map files
    console.log('Cleaning up orphaned source maps...');
    cleanupOrphanedMaps(cssDir);

    // Then minify CSS files with source maps
    console.log('Minifying CSS files...');
    minifyDir(cssDir);

    console.log('CSS minification complete.');
} else {
    console.error(`Directory not found: ${cssDir}`);
}
