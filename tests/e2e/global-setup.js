/**
 * Playwright global setup — authenticates against the dev-site admins and
 * saves session storage state for reuse across all admin specs.
 *
 * Runs once before the entire test suite. Three behaviours matter here, all
 * from #1342 — a transient login failure used to cost the whole run:
 *
 *   - Only the sites the selected projects actually use are authenticated.
 *     Which sites those are is read from the resolved config: a project that
 *     declares a storageState needs its site's admin session, one that
 *     doesn't (the site-* projects) needs nothing. `--project=admin-j6`
 *     therefore never touches j5-dev.
 *   - A still-valid saved session is reused instead of re-authenticating.
 *     Joomla admin sessions outlive a test run by default, so back-to-back
 *     runs skip the login dance (and its flake surface) entirely.
 *   - A login that does run is retried with backoff before it fails the run.
 *     The observed failure mode is a rejection that succeeds moments later
 *     with identical credentials — a flake, not a misconfiguration.
 *
 * Configuration follows a two-layer approach:
 *   1. build.dist.properties — defaults (committed)
 *   2. build.properties      — local overrides for credentials (gitignored)
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

function loadProps(root) {
    const dist = parseProperties(path.join(root, 'build.dist.properties'));
    const local = parseProperties(path.join(root, 'build.properties'));
    return { ...dist, ...local };
}

/**
 * Project names passed on the command line, e.g. --project=admin-j6.
 *
 * Playwright hands globalSetup the full resolved config with every project
 * in it, regardless of any --project filter (upstream: playwright#14212),
 * so the filter has to be recovered from argv. Empty array = no filter.
 */
function selectedProjectNames() {
    const names = [];
    const argv = process.argv;

    for (let i = 0; i < argv.length; i += 1) {
        if (argv[i] === '--project' && argv[i + 1]) {
            names.push(argv[i + 1]);
        } else if (argv[i].startsWith('--project=')) {
            names.push(argv[i].slice('--project='.length));
        }
    }

    return names;
}

/**
 * Whether a saved storage state still holds an authenticated admin session.
 *
 * One page load either way: reusing costs the same navigation the login
 * would start with, and skips the credential dance that follows.
 */
async function stateStillValid(browser, baseUrl, storageStatePath) {
    if (!fs.existsSync(storageStatePath)) {
        return false;
    }

    const ctx = await browser.newContext({ ignoreHTTPSErrors: true, storageState: storageStatePath });

    try {
        const page = await ctx.newPage();
        await page.goto(`${baseUrl}/administrator/index.php`, {
            waitUntil: 'domcontentloaded',
            timeout: 15000,
        });

        // The login form appearing means the session expired; treat any
        // navigation failure the same way and fall through to a fresh login.
        const loginVisible = await page.locator('#form-login')
            .isVisible({ timeout: 3000 })
            .catch(() => true);

        return !loginVisible;
    } catch {
        return false;
    } finally {
        await ctx.close();
    }
}

async function loginAdmin(browser, baseUrl, username, password, storageStatePath) {
    const ctx = await browser.newContext({ ignoreHTTPSErrors: true });

    try {
        const page = await ctx.newPage();

        // Load the login page
        await page.goto(`${baseUrl}/administrator/index.php`, { waitUntil: 'networkidle' });

        // Confirm the login form is present
        const loginVisible = await page.locator('#form-login').isVisible({ timeout: 10000 }).catch(() => false);
        if (!loginVisible) {
            console.log(`  Already authenticated at ${baseUrl}, saving state.`);
            await ctx.storageState({ path: storageStatePath });
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
            throw new Error(
                `Login failed for ${baseUrl} — credentials rejected or form not submitted.\n` +
                `Check builder.j5dev.username / builder.j5dev.password (or j6dev) in build.properties.\n` +
                `Current URL: ${page.url()}`
            );
        }

        await ctx.storageState({ path: storageStatePath });
        console.log(`  Saved auth state → ${path.basename(storageStatePath)}`);
    } finally {
        await ctx.close();
    }
}

/**
 * loginAdmin with retries. The whole suite hangs off this succeeding, so a
 * transient rejection gets a second and third chance before it is allowed
 * to be fatal.
 */
async function loginAdminWithRetry(browser, baseUrl, username, password, storageStatePath) {
    const attempts = 3;

    for (let attempt = 1; attempt <= attempts; attempt += 1) {
        try {
            await loginAdmin(browser, baseUrl, username, password, storageStatePath);
            return;
        } catch (err) {
            if (attempt === attempts) {
                throw err;
            }
            const delayMs = attempt * 2500;
            console.warn(
                `  Login attempt ${attempt}/${attempts} failed (${String(err.message || err).split('\n')[0]}); `
                + `retrying in ${delayMs / 1000}s…`
            );
            await new Promise((resolve) => { setTimeout(resolve, delayMs); });
        }
    }
}

module.exports = async function globalSetup(config) {
    const root = path.join(__dirname, '../..');
    const props = loadProps(root);

    const authDir = path.join(__dirname, '.auth');
    if (!fs.existsSync(authDir)) {
        fs.mkdirSync(authDir, { recursive: true });
    }

    // The sites this repo can authenticate against, keyed by the storage
    // state file the Playwright projects reference.
    const sites = [
        {
            label: 'j5-dev',
            url: props['builder.j5dev.url'] || 'https://j5-dev.local:8890',
            username: props['builder.j5dev.username'] || props['builder.joomla_username'],
            password: props['builder.j5dev.password'] || props['builder.joomla_password'],
            stateFile: 'admin-j5.json',
        },
        {
            label: 'j6-dev',
            url: props['builder.j6dev.url'] || 'https://j6-dev.local:8890',
            username: props['builder.j6dev.username'] || props['builder.joomla_username'],
            password: props['builder.j6dev.password'] || props['builder.joomla_password'],
            stateFile: 'admin-j6.json',
        },
    ];

    // Authenticate only the sites the selected projects consume. A project
    // needs a site's admin session exactly when it declares that site's
    // storageState; the site-* projects declare none and need none.
    const selected = selectedProjectNames();
    const projects = (config.projects || [])
        .filter((p) => !selected.length || selected.includes(p.name));

    const needed = sites.filter((site) => projects.some(
        (p) => p.use && typeof p.use.storageState === 'string' && p.use.storageState.endsWith(site.stateFile)
    ));

    if (!needed.length) {
        console.log('\nGlobal setup: no selected project needs an admin session; nothing to authenticate.\n');
        return;
    }

    const missing = needed.filter((site) => !site.username || !site.password);
    if (missing.length) {
        throw new Error(
            `Missing admin credentials for ${missing.map((s) => s.label).join(', ')}.\n` +
            'Set builder.j5dev.username / builder.j5dev.password (and j6dev) in build.properties.\n' +
            'Or set builder.joomla_username / builder.joomla_password as a shared fallback.'
        );
    }

    // globalSetup runs outside the project fixtures, so the `use.channel`
    // setting in playwright.config.js does not reach this call — it has to be
    // repeated. Without it this launches Playwright's default headless
    // browser, chrome-headless-shell, while every test runs real Chromium.
    //
    // Two reasons that matters: the shell may not be installed at all (this
    // failed here with "Executable doesn't exist"), and authenticating in one
    // browser while testing in another is a difference worth not having in a
    // login flow.
    const browser = await chromium.launch({ channel: 'chromium' });

    try {
        for (const site of needed) {
            const statePath = path.join(authDir, site.stateFile);

            if (await stateStillValid(browser, site.url, statePath)) {
                console.log(`Reusing valid admin session for ${site.label} (${path.basename(statePath)}).`);
                continue;
            }

            console.log(`\nAuthenticating against ${site.label} (${site.url})…`);
            await loginAdminWithRetry(browser, site.url, site.username, site.password, statePath);
        }
    } finally {
        await browser.close();
    }

    console.log('Global setup complete.\n');
};
