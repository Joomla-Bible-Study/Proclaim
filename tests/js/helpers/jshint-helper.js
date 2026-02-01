/**
 * JSHint validation helper for Jest tests
 * Validates JavaScript source files against project JSHint configuration
 */

const { JSHINT } = require('jshint');
const fs = require('fs');
const path = require('path');

// Load project JSHint configuration
const jshintrcPath = path.resolve(__dirname, '../../../.jshintrc');
const jshintConfig = JSON.parse(fs.readFileSync(jshintrcPath, 'utf8'));

/**
 * Validate a JavaScript file with JSHint
 * @param {string} filePath - Path to the JS file (relative to project root or absolute)
 * @returns {Object} - { valid: boolean, errors: Array, errorReport: string }
 */
function validateFile(filePath) {
    // Resolve path relative to project root if not absolute
    const absolutePath = path.isAbsolute(filePath)
        ? filePath
        : path.resolve(__dirname, '../../..', filePath);

    if (!fs.existsSync(absolutePath)) {
        return {
            valid: false,
            errors: [{ reason: `File not found: ${absolutePath}` }],
            errorReport: `File not found: ${absolutePath}`
        };
    }

    const source = fs.readFileSync(absolutePath, 'utf8');
    const isValid = JSHINT(source, jshintConfig);
    const errors = JSHINT.errors || [];

    // Format error report
    let errorReport = '';
    if (!isValid && errors.length > 0) {
        errorReport = errors
            .filter(err => err !== null)
            .map(err => `Line ${err.line}, Col ${err.character}: ${err.reason}`)
            .join('\n');
    }

    return {
        valid: isValid,
        errors: errors.filter(err => err !== null),
        errorReport
    };
}

/**
 * Validate JavaScript source code string with JSHint
 * @param {string} source - JavaScript source code
 * @param {Object} extraOptions - Additional JSHint options to merge
 * @returns {Object} - { valid: boolean, errors: Array, errorReport: string }
 */
function validateSource(source, extraOptions = {}) {
    const config = { ...jshintConfig, ...extraOptions };
    const isValid = JSHINT(source, config);
    const errors = JSHINT.errors || [];

    let errorReport = '';
    if (!isValid && errors.length > 0) {
        errorReport = errors
            .filter(err => err !== null)
            .map(err => `Line ${err.line}, Col ${err.character}: ${err.reason}`)
            .join('\n');
    }

    return {
        valid: isValid,
        errors: errors.filter(err => err !== null),
        errorReport
    };
}

/**
 * Jest matcher for JSHint validation
 * Usage: expect('path/to/file.js').toPassJSHint()
 */
function toPassJSHint(filePath) {
    const result = validateFile(filePath);

    if (result.valid) {
        return {
            pass: true,
            message: () => `Expected ${filePath} to fail JSHint validation`
        };
    } else {
        return {
            pass: false,
            message: () => `JSHint validation failed for ${filePath}:\n${result.errorReport}`
        };
    }
}

module.exports = {
    validateFile,
    validateSource,
    toPassJSHint,
    jshintConfig
};
