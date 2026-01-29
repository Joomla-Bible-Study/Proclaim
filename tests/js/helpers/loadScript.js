/**
 * Helper to load and execute a JavaScript file in the global context
 * This allows testing of scripts that define global functions
 */
const fs = require('fs');
const path = require('path');

/**
 * Load a script file and execute it in the global context
 * @param {string} scriptPath - Relative path from project root to the script
 * @returns {void}
 */
function loadScript(scriptPath) {
    const fullPath = path.resolve(__dirname, '../../..', scriptPath);
    const code = fs.readFileSync(fullPath, 'utf8');

    // Use Function constructor to execute in global scope
    // This is the safest way to load scripts that define globals
    const fn = new Function(code);
    fn.call(global);
}

/**
 * Load cwmcore.js and expose its global functions
 * Since the script uses function declarations, we need to extract and eval them separately
 * @returns {Object} Object containing the global functions
 */
function loadCwmCore() {
    const scriptPath = path.resolve(__dirname, '../../../media/js/cwmcore.js');
    const code = fs.readFileSync(scriptPath, 'utf8');

    // Extract the global function declarations (outside DOMContentLoaded)
    // These are the utility functions we want to test
    const functionMatches = code.match(/^function\s+\w+\s*\([^)]*\)\s*\{[\s\S]*?^\}/gm) || [];

    // Extract the ProclaimA11y object
    const a11yMatch = code.match(/const ProclaimA11y = \{[\s\S]*?^\};/m);

    // Build a testable module
    const testableCode = functionMatches.join('\n\n') + '\n\n' + (a11yMatch ? a11yMatch[0] : '');

    // Execute and capture the exports
    const wrappedCode = `
        ${testableCode}
        return {
            decOnly: typeof decOnly !== 'undefined' ? decOnly : undefined,
            bandwidth: typeof bandwidth !== 'undefined' ? bandwidth : undefined,
            transferFileSize: typeof transferFileSize !== 'undefined' ? transferFileSize : undefined,
            ProclaimA11y: typeof ProclaimA11y !== 'undefined' ? ProclaimA11y : undefined
        };
    `;

    const fn = new Function(wrappedCode);
    const exports = fn();

    // Expose to global for easy access in tests
    Object.keys(exports).forEach(key => {
        if (exports[key] !== undefined) {
            global[key] = exports[key];
        }
    });

    return exports;
}

module.exports = { loadScript, loadCwmCore };