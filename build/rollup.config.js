/* jshint node: true, esversion: 11 */
"use strict";

const resolve = require('@rollup/plugin-node-resolve');
const terser = require('@rollup/plugin-terser');
const fs = require('fs');
const path = require('path');

// Define source directories
const sourceDirs = [
    path.resolve(__dirname, '../media/js'),
];

// Helper to find files
const getFiles = (dir) => {
    if (!fs.existsSync(dir)) return [];
    
    return fs.readdirSync(dir).filter(file => {
        const fullPath = path.join(dir, file);
        
        // Skip directories (including vendor, node_modules, etc.)
        if (fs.statSync(fullPath).isDirectory()) {
            return false;
        }

        return file.endsWith('.js') &&
               !file.includes('.min.') &&
               !file.match(/[-.]es5[-.]/) &&
               !file.endsWith('.d.ts');
    }).map(file => ({
        name: file,
        path: path.join(dir, file),
        dir: dir
    }));
};

// Collect all files
const allFiles = sourceDirs.flatMap(dir => getFiles(dir));

module.exports = allFiles.flatMap(fileObj => {
    const name = path.basename(fileObj.name, '.js');
    const outputDir = fileObj.dir;
    const variableName = name.replace(/-/g, '_');

    return [
        // Minified
        {
            input: fileObj.path,
            output: {
                file: path.join(outputDir, `${name}.min.js`),
                format: 'iife',
                name: variableName,
                sourcemap: true
            },
            plugins: [
                resolve(),
                terser()
            ]
        }
    ];
});
