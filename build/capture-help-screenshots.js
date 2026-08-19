#!/usr/bin/env node

/**
 * Capture the screenshots the wiki help pages embed.
 *
 * Help pages are only as current as their images, and a hand-captured set goes
 * stale silently -- the text gets updated, the picture keeps showing last
 * year's toolbar. So the images are generated, not collected: re-run this and
 * every shot matches the code that is checked out.
 *
 * ⚠️ Captures from a site seeded with `seed-dev-data.php --demo`, never from
 * j5-dev or j6-dev. Those are copies of a real church's site -- real people's
 * names, real contact details, real server credentials -- and these images are
 * published to a public wiki.
 *
 * Usage:
 *   php build/seed-dev-data.php --site=j62-dev --demo
 *   node build/capture-help-screenshots.js
 *   node build/capture-help-screenshots.js --only=messages,message
 *   php build/seed-dev-data.php --site=j62-dev --remove
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

const fs = require('fs');
const path = require('path');
const { chromium } = require('@playwright/test');
const { loadProps } = require('../tests/e2e/helpers/properties');

const ROOT = path.resolve(__dirname, '..');
const OUT = path.resolve(ROOT, '../Proclaim.wiki/images');
const SITE = 'j62dev';

/**
 * Which screens to capture, and what to call the resulting file.
 *
 * `view` is the admin view name. `tabs` lists the Joomla tab labels to capture
 * individually -- Joomla's own help does one image per tab on an edit form, so
 * a reader comparing screens sees the same granularity they are used to.
 */
const TARGETS = [
    { key: 'proclaim', view: 'cwmcpanel' },
    { key: 'messages', view: 'cwmmessages' },
    { key: 'message', task: 'cwmmessage.edit', id: 3, tabs: true },
    { key: 'teachers', view: 'cwmteachers' },
    { key: 'teacher', task: 'cwmteacher.edit', id: 1, tabs: true },
    { key: 'series', view: 'cwmseries' },
    { key: 'serie', task: 'cwmserie.edit', id: 1, tabs: true },
    { key: 'topics', view: 'cwmtopics' },
    { key: 'topic', task: 'cwmtopic.edit', id: 1, tabs: true },
    { key: 'locations', view: 'cwmlocations' },
    { key: 'location', task: 'cwmlocation.edit', id: 1, tabs: true },
    { key: 'mediafiles', view: 'cwmmediafiles' },
    { key: 'servers', view: 'cwmservers' },
    { key: 'messagetypes', view: 'cwmmessagetypes' },
    { key: 'podcasts', view: 'cwmpodcasts' },
    { key: 'podcast', task: 'cwmpodcast.edit', id: 1, tabs: true },
    { key: 'templates', view: 'cwmtemplates' },
    { key: 'template', task: 'cwmtemplate.edit', id: 1, tabs: true },
    { key: 'templatecodes', view: 'cwmtemplatecodes' },
    { key: 'comments', view: 'cwmcomments' },
    { key: 'playlists', view: 'cwmplaylists' },
    { key: 'admin', view: 'cwmadmin', tabs: true },
    { key: 'cwmassets', view: 'cwmassets' },
    { key: 'cwmpermissions', view: 'cwmpermissions' },
    { key: 'cwmbackup', view: 'cwmbackup' },
    { key: 'cwmarchive', view: 'cwmarchive' },
];

/**
 * Log in to the administrator and leave the context authenticated.
 *
 * Credentials come from build.properties, which is gitignored -- the same
 * source tests/e2e/global-setup.js reads for the other dev sites.
 */
async function login(page, baseUrl, user, pass) {
    await page.goto(`${baseUrl}/administrator/index.php`, { waitUntil: 'domcontentloaded' });

    if (await page.locator('#mod-login-username').count()) {
        await page.fill('#mod-login-username', user);
        await page.fill('#mod-login-password', pass);
        await Promise.all([
            page.waitForURL(/administrator/, { timeout: 30000 }).catch(() => {}),
            page.click('#btn-login-submit'),
        ]);
        await page.waitForLoadState('load').catch(() => {});
    }

    if (await page.locator('#mod-login-username').count()) {
        throw new Error('login did not take -- check builder.j62dev.username/password');
    }
}

/**
 * Hide the chrome that is noise in a help image.
 *
 * The sidebar and the Joomla header are the same on every screen and eat
 * roughly a third of the width; dropping them means the actual subject renders
 * larger at the same image size.
 */
const HIDE_CSS = `
    #sidebar, .sidebar-wrapper, #wrapper > .header, .header,
    joomla-toolbar-button[task="help"], #system-message-container { display: none !important; }
    #wrapper, .container-fluid { margin-left: 0 !important; padding-left: 0 !important; }
`;

async function shoot(page, file, locator) {
    const target = locator || page.locator('#content, .com-content, main').first();
    const dest = path.join(OUT, file);

    if (await target.count()) {
        await target.screenshot({ path: dest });
    } else {
        await page.screenshot({ path: dest, fullPage: false });
    }

    const kb = Math.round(fs.statSync(dest).size / 1024);
    console.log(`  ${file}  ${kb}kb`);
}

(async () => {
    const props = loadProps(ROOT);
    const baseUrl = props[`builder.${SITE}.url`];
    const user = props[`builder.${SITE}.username`];
    const pass = props[`builder.${SITE}.password`];

    if (!baseUrl || !user || !pass) {
        throw new Error(`missing builder.${SITE}.url/username/password in build.properties`);
    }

    const only = (process.argv.find(a => a.startsWith('--only=')) || '').replace('--only=', '');
    const wanted = only ? only.split(',').map(s => s.trim()) : null;

    fs.mkdirSync(OUT, { recursive: true });

    const browser = await chromium.launch({ channel: 'chromium' });
    const context = await browser.newContext({
        ignoreHTTPSErrors: true,
        viewport: { width: 1440, height: 1000 },
        deviceScaleFactor: 2,
    });
    const page = await context.newPage();

    await login(page, baseUrl, user, pass);
    console.log(`authenticated against ${baseUrl}`);

    for (const t of TARGETS) {
        if (wanted && !wanted.includes(t.key)) {
            continue;
        }

        let url = `${baseUrl}/administrator/index.php?option=com_proclaim`;

        // An edit screen is reached by task, not by view+layout: view+layout
        // redirects straight back to the list, which silently yields a
        // duplicate of the list image rather than an error.
        url += t.task ? `&task=${t.task}` : `&view=${t.view}`;

        if (t.id) {
            url += `&id=${t.id}`;
        }

        console.log(`${t.key}:`);

        try {
            await page.goto(url, { waitUntil: 'load' });
            // Joomla admin keeps a keepalive request open, so networkidle never
            // fires -- wait for the content region instead.
            await page.locator('#content, main').first()
                .waitFor({ state: 'visible', timeout: 15000 }).catch(() => {});
            await page.addStyleTag({ content: HIDE_CSS });
            await page.waitForTimeout(600);

            await shoot(page, `help-${t.key}.png`);

            if (t.tabs) {
                // Only the form's own tabs. A permissions grid nests a second
                // joomla-tab with one tab per user group, and a ColorPicker
                // nests another -- capturing those yields a dozen images of the
                // same grid. Depth in the joomla-tab tree is the discriminator.
                const indices = await page.evaluate(() => {
                    const out = [];

                    document.querySelectorAll('button[role="tab"]').forEach((btn, i) => {
                        const owner = btn.closest('joomla-tab');
                        const nested = owner && owner.parentElement
                            && owner.parentElement.closest('joomla-tab');

                        if (owner && !nested) {
                            out.push([i, btn.innerText.trim()]);
                        }
                    });

                    return out;
                });

                const all = page.locator('button[role="tab"]');

                for (const [i, label] of indices) {
                    const slug = label.toLowerCase()
                        .replace(/\(.*?\)/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-|-$/g, '');

                    if (!slug) {
                        continue;
                    }

                    try {
                        await all.nth(i).click({ timeout: 5000 });
                    } catch (e) {
                        console.log(`  -- ${slug}: tab would not open, skipped`);
                        continue;
                    }

                    await page.waitForTimeout(350);
                    await shoot(page, `help-${t.key}-${slug}.png`);
                }
            }
        } catch (e) {
            console.log(`  !! ${t.key} failed: ${e.message.split('\n')[0]}`);
        }
    }

    await browser.close();
    console.log(`\ndone -- images in ${OUT}`);
})();
