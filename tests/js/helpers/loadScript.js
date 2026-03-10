const fs = require('fs');
const path = require('path');

/**
 * Helper to load and execute a JavaScript file in the global context
 */
function loadScript(scriptPath) {
    const fullPath = path.resolve(__dirname, '../../..', scriptPath);
    const code = fs.readFileSync(fullPath, 'utf8');
    const fn = new Function(code);
    fn.call(global);
}

/**
 * Load cwmcore source and expose its global functions.
 * Uses the committed ES6 source (not the built media/ output which is gitignored).
 */
function loadCwmCore() {
    const scriptPath = path.resolve(__dirname, '../../../build/media_source/js/cwmcore.es6.js');
    const code = fs.readFileSync(scriptPath, 'utf8');

    try {
        // Execute the script. It's an IIFE that defines functions globally or inside its scope.
        // In our case, utility functions are now OUTSIDE the IIFE in cwmcore.js (generated from es6)
        // or we made them global.
        
        const fn = new Function('window', 'document', 'navigator', 'fetch', code);
        const mockWindow = global;
        const mockDocument = global.document;
        const mockNavigator = { maxTouchPoints: 0 };
        const mockFetch = () => Promise.resolve({ ok: true, text: () => Promise.resolve('') });
        
        fn.call(global, mockWindow, mockDocument, mockNavigator, mockFetch);

        // Map from global
        const exports = {
            decOnly: global.decOnly,
            bandwidth: global.bandwidth,
            transferFileSize: global.transferFileSize,
            ProclaimA11y: global.ProclaimA11y
        };

        return exports;
    } catch (e) {
        console.error('Error loading cwmcore.js for tests:', e);
        throw e;
    }
}

module.exports = { loadScript, loadCwmCore };
