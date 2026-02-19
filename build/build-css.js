/* eslint-disable no-console */
"use strict";

const fs = require('fs');
const path = require('path');
const csso = require('csso');

const sourceDir = path.join(__dirname, 'media_source/css');
const outputDir = path.join(__dirname, '../media/css');

/**
 * Ensure output directory exists, creating it if necessary
 * @param {string} dir - Directory to ensure exists
 */
function ensureDir(dir) {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

/**
 * Minify CSS files from source directory and write to output directory
 * @param {string} srcDir - Source directory
 * @param {string} outDir - Output directory
 */
function minifyDir(srcDir, outDir) {
    ensureDir(outDir);
    const files = fs.readdirSync(srcDir);

    files.forEach(file => {
        const srcPath = path.join(srcDir, file);
        const stat = fs.statSync(srcPath);

        if (stat.isDirectory()) {
            minifyDir(srcPath, path.join(outDir, file));
        } else if (file.endsWith('.css') && !file.endsWith('.min.css')) {
            const css = fs.readFileSync(srcPath, 'utf8');
            const outFile = path.join(outDir, file);
            const minFile = path.join(outDir, file.replace('.css', '.min.css'));
            const mapFile = minFile + '.map';
            const mapFileName = path.basename(mapFile);

            // Copy source CSS to output directory
            fs.copyFileSync(srcPath, outFile);

            // Minify with source map generation
            const result = csso.minify(css, {
                filename: file,
                sourceMap: true
            });

            // Source map points back to the source file
            const sourceMap = JSON.parse(result.map.toString());
            sourceMap.sourceRoot = '../../build/media_source/css/';
            sourceMap.sources = [file];

            // Append source map reference to minified CSS
            const cssWithMapRef = `${result.css}\n/*# sourceMappingURL=${mapFileName} */`;

            // Write minified CSS and source map
            fs.writeFileSync(minFile, cssWithMapRef);
            fs.writeFileSync(mapFile, JSON.stringify(sourceMap));

            console.log(`Minified: ${file} -> ${path.basename(minFile)} + ${mapFileName}`);
        }
    });
}

console.log('Starting CSS minification...');
if (fs.existsSync(sourceDir)) {
    console.log('Minifying CSS files...');
    minifyDir(sourceDir, outputDir);
    console.log('CSS minification complete.');
} else {
    console.error(`Source directory not found: ${sourceDir}`);
    process.exit(1);
}
