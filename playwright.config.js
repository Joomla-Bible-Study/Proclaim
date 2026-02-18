/**
 * Playwright E2E configuration for Proclaim.
 *
 * Credentials and URLs are read from build.properties (gitignored).
 * Copy build.dist.properties → build.properties and fill in your values.
 */

const fs = require('fs');
const path = require('path');
const { defineConfig, devices } = require('@playwright/test');

/** Parse a Java-style .properties file into a plain object. */
function parseProperties(filePath) {
    const props = {};
    if (!fs.existsSync(filePath)) {
        return props;
    }
    const lines = fs.readFileSync(filePath, 'utf8').split('\n');
    for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            continue;
        }
        const eq = trimmed.indexOf('=');
        if (eq === -1) {
            continue;
        }
        const key = trimmed.slice(0, eq).trim();
        const value = trimmed.slice(eq + 1).trim();
        props[key] = value;
    }
    return props;
}

const props = parseProperties(path.join(__dirname, 'build.properties'));

const j5Url = props['builder.j5dev.url'] || 'https://j5-dev.local:8890';
const j6Url = props['builder.j6dev.url'] || 'https://j6-dev.local:8890';

module.exports = defineConfig({
    testDir: './tests/e2e',
    outputDir: 'test-results/',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    timeout: 30000,
    globalSetup: require.resolve('./tests/e2e/global-setup.js'),

    reporter: [
        ['list'],
        ['html', { outputFolder: 'build/reports/e2e', open: 'never' }],
    ],

    use: {
        ignoreHTTPSErrors: true,
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },

    projects: [
        {
            name: 'admin-j5',
            testMatch: '**/admin/**/*.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: j5Url,
                storageState: 'tests/e2e/.auth/admin-j5.json',
            },
        },
        {
            name: 'site-j5',
            testMatch: '**/site/**/*.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: j5Url,
            },
        },
        {
            name: 'admin-j6',
            testMatch: '**/admin/**/*.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: j6Url,
                storageState: 'tests/e2e/.auth/admin-j6.json',
            },
        },
        {
            name: 'site-j6',
            testMatch: '**/site/**/*.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: j6Url,
            },
        },
    ],
});
