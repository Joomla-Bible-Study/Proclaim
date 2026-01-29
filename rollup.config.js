const babel = require('@rollup/plugin-babel');
const resolve = require('@rollup/plugin-node-resolve');
const terser = require('@rollup/plugin-terser');
const fs = require('fs');
const path = require('path');

const sourceDir = 'media/js';

// Helper to find files
const getFiles = (dir) => {
    return fs.readdirSync(dir).filter(file => {
        return file.endsWith('.js') &&
               !file.includes('.min.') &&
               !file.match(/[-.]es5[-.]/) && // Exclude existing es5 files from being sources
               fs.statSync(path.join(dir, file)).isFile();
    });
};

const files = getFiles(sourceDir);

module.exports = files.flatMap(file => {
    const filePath = path.join(sourceDir, file);
    const name = path.basename(file, '.js');

    // Determine ES5 output filename based on existing convention
    let es5Filename = `${name}.es5.js`;
    if (fs.existsSync(path.join(sourceDir, `${name}-es5.js`))) {
        es5Filename = `${name}-es5.js`;
    }

    // Common plugins
    const plugins = [
        resolve(),
    ];

    return [
        // ES6+ Minified
        {
            input: filePath,
            output: {
                file: path.join(sourceDir, `${name}.min.js`),
                format: 'iife',
                name: name.replace(/-/g, '_'),
                sourcemap: true
            },
            plugins: [
                ...plugins,
                terser()
            ]
        },
        // ES5 Transpiled
        {
            input: filePath,
            output: {
                file: path.join(sourceDir, es5Filename),
                format: 'iife',
                name: name.replace(/-/g, '_')
            },
            plugins: [
                ...plugins,
                babel({
                    babelHelpers: 'bundled',
                    presets: ['@babel/preset-env'],
                    exclude: 'node_modules/**'
                })
            ]
        },
        // ES5 Transpiled + Minified
        {
            input: filePath,
            output: {
                file: path.join(sourceDir, es5Filename.replace('.js', '.min.js')),
                format: 'iife',
                name: name.replace(/-/g, '_'),
                sourcemap: true
            },
            plugins: [
                ...plugins,
                babel({
                    babelHelpers: 'bundled',
                    presets: ['@babel/preset-env'],
                    exclude: 'node_modules/**'
                }),
                terser()
            ]
        }
    ];
});
