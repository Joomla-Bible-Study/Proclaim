/**
 * Playwright E2E configuration for Proclaim.
 *
 * Configuration is resolved with a two-layer approach:
 *   1. build.dist.properties — default values (committed, safe to share)
 *   2. build.properties      — local overrides for credentials/paths (gitignored)
 *
 * To get started: copy build.dist.properties → build.properties and fill in
 * per-site credentials (builder.j5dev.username, etc.) and site URLs.
 */

const { defineConfig, devices } = require('@playwright/test');
const { loadProps, installForRole } = require('./tests/e2e/helpers/properties');

const props = loadProps(__dirname);

const j5Url = props['builder.j5dev.url'] || 'https://j5-dev.local:8890';
const j6Url = props['builder.j6dev.url'] || 'https://j6-dev.local:8890';

// The role=test install — the site `composer test:install` provisions from
// the built package. Discovered by role, not by name: install naming is
// local to each build.properties. No role=test install, no API project.
const testInstall = installForRole(props, 'test');

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

        // Run new headless — real Chromium with no window — rather than
        // Playwright's default, which for `headless: true` is
        // chrome-headless-shell.
        //
        // That shell is the *old* headless implementation. Google removed old
        // headless from the Chrome binary in Chrome 132 and republished it as
        // a standalone binary for automation that wants its speed, so it is
        // supported and current — but it is not the renderer anyone visiting
        // the site is using.
        //
        // The distinction is not academic here, because two of the WCAG rules
        // we gate on measure rendered output rather than markup:
        //
        //   target-size    2.5.8, new in WCAG 2.2 — element dimensions in CSS px
        //   color-contrast 1.4.3 — computed foreground/background colours
        //
        // A pass from a renderer users do not run is a weak basis for a
        // conformance claim, and a target-size failure that only reproduces in
        // the shell would waste a morning. Accuracy is worth more than the
        // speed difference across 28 tests.
        channel: 'chromium',
    },

    projects: [
        // The J5 projects skip the accessibility specs. Proclaim renders the
        // same markup on both platforms, so scanning it twice costs a full
        // second pass of every page and reports the same violations twice.
        // What J5 is actually here to catch is Joomla-version behaviour
        // differences, which is what the functional specs cover.
        //
        // If a violation ever turns out to be J5-only, it will be a Joomla
        // template difference outside the scanned region — drop the
        // testIgnore then, deliberately.
        {
            name: 'admin-j5',
            testMatch: '**/admin/**/*.spec.js',
            testIgnore: '**/a11y.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: j5Url,
                storageState: 'tests/e2e/.auth/admin-j5.json',
            },
        },
        {
            name: 'site-j5',
            testMatch: '**/site/**/*.spec.js',
            testIgnore: '**/a11y.spec.js',
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

        // The API acceptance spec (#1330) runs against the role=test install
        // and nowhere else: its whole point is asserting the state the
        // installer produces, which the symlinked dev sites cannot represent.
        // Omitted entirely when build.properties declares no role=test site.
        ...(testInstall ? [{
            name: 'api-test',
            testMatch: '**/api/**/*.spec.js',
            use: {
                ...devices['Desktop Chrome'],
                baseURL: testInstall.url,
                storageState: 'tests/e2e/.auth/admin-test.json',
            },
        }] : []),
    ],
});
