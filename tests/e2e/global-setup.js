/**
 * Playwright global setup — authenticates against j5-dev and j6-dev admin
 * and saves session storage state for reuse across all admin specs.
 *
 * Runs once before the entire test suite.
 *
 * Uses a real browser context so that the user-agent stored in the Joomla
 * PHP session matches the Chromium user-agent used by the actual test runs.
 */

const fs = require('fs');
const path = require('path');
const { chromium } = require('@playwright/test');

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
        props[trimmed.slice(0, eq).trim()] = trimmed.slice(eq + 1).trim();
    }
    return props;
}

async function loginAdmin(browser, baseUrl, username, password, storageStatePath) {
    const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await ctx.newPage();

    // Load the login page
    await page.goto(`${baseUrl}/administrator/index.php`, { waitUntil: 'networkidle' });

    // Confirm the login form is present
    const loginVisible = await page.locator('#form-login').isVisible({ timeout: 10000 }).catch(() => false);
    if (!loginVisible) {
        console.log(`  Already authenticated at ${baseUrl}, saving state.`);
        await ctx.storageState({ path: storageStatePath });
        await ctx.close();
        return;
    }

    // Fill credentials
    await page.fill('#mod-login-username', username);
    await page.fill('#mod-login-password', password);

    // Submit the form via JS to bypass Joomla's form-validate JS which
    // can prevent submission in headless mode. form.submit() bypasses the
    // onsubmit handler but correctly includes all hidden fields (CSRF token).
    await page.evaluate(() => document.getElementById('form-login').submit());

    // Wait for the resulting page to be fully loaded
    await page.waitForLoadState('networkidle', { timeout: 25000 });

    // Verify we're no longer on the login page
    const stillOnLogin = await page.locator('#form-login').isVisible({ timeout: 2000 }).catch(() => false);
    if (stillOnLogin) {
        const url = page.url();
        await ctx.close();
        throw new Error(
            `Login failed for ${baseUrl} — credentials rejected or form not submitted.\n` +
            `Check builder.joomla_username / builder.joomla_password in build.properties.\n` +
            `Current URL: ${url}`
        );
    }

    await ctx.storageState({ path: storageStatePath });
    console.log(`  Saved auth state → ${path.basename(storageStatePath)}`);
    await ctx.close();
}

module.exports = async function globalSetup() {
    const propsPath = path.join(__dirname, '../../build.properties');
    const props = parseProperties(propsPath);

    const username = props['builder.joomla_username'];
    const password = props['builder.joomla_password'];

    if (!username || !password) {
        throw new Error(
            'Missing builder.joomla_username / builder.joomla_password in build.properties.\n' +
            'Copy build.dist.properties → build.properties and fill in your credentials.'
        );
    }

    const j5Url = props['builder.j5dev.url'] || 'https://j5-dev.local:8890';
    const j6Url = props['builder.j6dev.url'] || 'https://j6-dev.local:8890';

    const authDir = path.join(__dirname, '.auth');
    if (!fs.existsSync(authDir)) {
        fs.mkdirSync(authDir, { recursive: true });
    }

    const browser = await chromium.launch();

    try {
        console.log(`\nAuthenticating against j5-dev (${j5Url})…`);
        await loginAdmin(browser, j5Url, username, password, path.join(authDir, 'admin-j5.json'));

        console.log(`Authenticating against j6-dev (${j6Url})…`);
        await loginAdmin(browser, j6Url, username, password, path.join(authDir, 'admin-j6.json'));
    } finally {
        await browser.close();
    }

    console.log('Global setup complete.\n');
};
