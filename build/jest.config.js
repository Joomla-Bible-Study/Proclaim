/**
 * Jest configuration for Proclaim JavaScript testing
 * Optimized for PhpStorm integration
 * @see https://jestjs.io/docs/configuration
 *
 * @type {import('jest').Config}
 */
module.exports = {
    // Use jsdom for DOM testing
    testEnvironment: 'jsdom',

    // Test file patterns
    testMatch: [
        '<rootDir>/tests/js/**/*.test.js',
        '<rootDir>/tests/js/**/*.spec.js'
    ],

    // Module paths for imports
    moduleDirectories: ['node_modules', 'media/js'],

    // Setup files to run before each test file
    setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],

    // Transform JS files with Babel
    transform: {
        '^.+\\.js$': 'babel-jest'
    },

    // Files to ignore during transformation
    transformIgnorePatterns: [
        '/node_modules/',
        '\\.min\\.js$',
        '\\.es5\\.js$'
    ],

    // Coverage configuration
    collectCoverageFrom: [
        'media/js/**/*.js',
        '!media/js/**/*.min.js',
        '!media/js/**/*.es5.js',
        '!media/js/**/*.es5.min.js'
    ],
    coverageDirectory: 'build/reports/coverage-js',
    coverageReporters: ['text', 'lcov', 'html'],

    // Coverage thresholds (adjust as test coverage grows)
    coverageThreshold: {
        global: {
            statements: 0,
            branches: 0,
            functions: 0,
            lines: 0
        }
    },

    // Clear mocks between tests
    clearMocks: true,

    // Verbose output for better PhpStorm integration
    verbose: true,

    // Reporter for PhpStorm (uses built-in Jest reporter)
    reporters: ['default'],

    // Root directory
    rootDir: '..',

    // Module name mapper for any aliases
    moduleNameMapper: {
        '^@js/(.*)$': '<rootDir>/media/js/$1'
    },

    // Timeout for async tests
    testTimeout: 10000
};