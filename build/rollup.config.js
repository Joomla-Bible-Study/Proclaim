"use strict";

const resolve = require('@rollup/plugin-node-resolve');
const terser = require('@rollup/plugin-terser');
const gzipPlugin = require('rollup-plugin-gzip').default;
const fs = require('fs');
const path = require('path');

// Source and output directories
const sourceDir = path.resolve(__dirname, 'media_source/js');
const outputDir = path.resolve(__dirname, '../media/js');

// Ensure output directory exists
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

// Get all .es6.js files from source directory
const getSourceFiles = () => {
    if (!fs.existsSync(sourceDir)) {
        console.warn(`Source directory not found: ${sourceDir}`);
        return [];
    }

    return fs.readdirSync(sourceDir)
        .filter(file => file.endsWith('.es6.js'))
        .map(file => ({
            name: file,
            path: path.join(sourceDir, file),
            // Output name without .es6 suffix
            outputName: file.replace('.es6.js', '')
        }));
};

const sourceFiles = getSourceFiles();

if (sourceFiles.length === 0) {
    console.warn('No .es6.js files found to process');
}

module.exports = sourceFiles.flatMap(fileObj => {
    const variableName = fileObj.outputName.replace(/-/g, '_');

    return [
        // Unminified version
        {
            input: fileObj.path,
            output: {
                file: path.join(outputDir, `${fileObj.outputName}.js`),
                format: 'iife',
                name: variableName,
                sourcemap: false
            },
            plugins: [
                resolve()
            ]
        },
        // Minified version with gzip
        {
            input: fileObj.path,
            output: {
                file: path.join(outputDir, `${fileObj.outputName}.min.js`),
                format: 'iife',
                name: variableName,
                sourcemap: true
            },
            plugins: [
                resolve(),
                terser({
                    format: {
                        comments: false
                    }
                }),
                gzipPlugin()
            ]
        }
    ];
});
