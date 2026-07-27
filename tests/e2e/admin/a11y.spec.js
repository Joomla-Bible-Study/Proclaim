/**
 * E2E — WCAG 2.2 AA, administrator views
 *
 * Scans every screen the administrator half of the component renders: list
 * views, edit forms, and the wizards. Confined to `section#content`, which is
 * the region the component renders: the Atum template's sidebar, toolbar and
 * header belong to Joomla, and their markup is neither ours to fix nor fair to
 * judge us on.
 *
 * Admin pages need an authenticated session; the admin-* Playwright projects
 * supply one via storageState, so these run under the same auth as the rest of
 * the admin suite.
 */

const { test, expect } = require('@playwright/test');
const { expectNoViolations } = require('../helpers/axe');

const PROCLAIM_CONTENT = 'section#content';

/**
 * Three widgets on the richer edit forms are rendered by TinyMCE, CodeMirror
 * and Joomla's Choices.js — third-party code we neither author nor can patch:
 *
 *   .tox-tinymce   role="application" without its required ARIA attributes,
 *                  and editor iframes with no title
 *   .cm-editor     CodeMirror 6 (Joomla's code editor plugin) renders its
 *                  contenteditable with role="textbox" and no accessible
 *                  name; the template-code form uses editor="codemirror".
 *                  Reported upstream as joomla/joomla-cms#48153
 *   .choices       the remove-button is under the 24x24 minimum of SC 2.5.8
 *
 * All are reported upstream. Excluded by selector rather than by disabling
 * the rules, so `aria-required-attr`, `frame-title`, `target-size` and
 * `aria-input-field-name` still apply to Proclaim's own markup on every page.
 *
 * The subform tooltip icon is deliberately NOT excluded. Joomla core emits it
 * with aria-hidden="true" and tabindex="0" at once, but the decision to render
 * it is ours — message.xml selects the repeatable-table layout and
 * scripture_row.xml gives bible_version a description.
 * com_proclaim.subform-tooltip-a11y repairs it at runtime, and the sermon form
 * scan is what proves the repair still works (upstream: joomla-cms#48151,
 * tracked in #1341).
 */
const THIRD_PARTY = ['.tox-tinymce', '.cm-editor', '.choices'];

/**
 * Every list-style admin screen, and how to reach it.
 *
 * This list also carries the "does the page render" duty that the per-entity
 * specs used to hold: each case navigates and requires `section#content`
 * before scanning, so a screen that 500s or renders blank fails here.
 */
const SCREENS = [
    ['control panel', 'cwmcpanel'],
    ['admin settings', 'cwmadmin'],
    ['sermon list', 'cwmmessages'],
    ['archived sermon list', 'cwmarchive'],
    ['teacher list', 'cwmteachers'],
    ['series list', 'cwmseries'],
    ['media file list', 'cwmmediafiles'],
    ['template list', 'cwmtemplates'],
    ['template code list', 'cwmtemplatecodes'],
    ['analytics dashboard', 'cwmanalytics'],
    ['comment list', 'cwmcomments'],
    ['location list', 'cwmlocations'],
    ['message type list', 'cwmmessagetypes'],
    ['playlist list', 'cwmplaylists'],
    ['podcast list', 'cwmpodcasts'],
    ['server list', 'cwmservers'],
    ['topic list', 'cwmtopics'],
    ['backup and restore', 'cwmbackup'],
    ['install and migration', 'cwminstall'],
    ['license', 'cwmlicense'],
    ['asset repair', 'cwmassets'],
];

/**
 * Every entity edit form, reached as a fresh add — no fixture record needed,
 * and an add renders the same form an edit does.
 *
 * Form controls are where labelling and grouping failures concentrate, and
 * where a screen-reader user is most likely to be blocked outright.
 */
const FORMS = [
    ['sermon', 'cwmmessage'],
    ['comment', 'cwmcomment'],
    ['location', 'cwmlocation'],
    ['media file', 'cwmmediafile'],
    ['message type', 'cwmmessagetype'],
    ['playlist', 'cwmplaylist'],
    ['podcast', 'cwmpodcast'],
    ['series', 'cwmserie'],
    ['server', 'cwmserver'],
    ['teacher', 'cwmteacher'],
    ['template', 'cwmtemplate'],
    ['template code', 'cwmtemplatecode'],
    ['topic', 'cwmtopic'],
];

/**
 * The wizards — the most interaction-heavy UI the component ships. Only the
 * initially rendered step is scanned here; later steps swap panels with JS
 * and deserve interaction tests of their own.
 */
const WIZARDS = [
    ['setup wizard', 'index.php?option=com_proclaim&view=cwmsetupwizard'],
    ['location wizard', 'index.php?option=com_proclaim&view=cwmlocationwizard'],
    ['new sermon wizard', 'index.php?option=com_proclaim&view=cwmmessage&layout=wizard'],
];

test.describe('Admin accessibility (WCAG 2.2 AA) @a11y', () => {
    for (const [label, view] of SCREENS) {
        test(`${label} meets WCAG AA`, async ({ page }) => {
            await page.goto(`/administrator/index.php?option=com_proclaim&view=${view}`, {
                waitUntil: 'networkidle',
            });

            await expect(page).toHaveURL(/option=com_proclaim/);
            await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

            await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
        });
    }

    for (const [label, controller] of FORMS) {
        test(`${label} edit form meets WCAG AA`, async ({ page }) => {
            await page.goto(`/administrator/index.php?option=com_proclaim&task=${controller}.add`, {
                waitUntil: 'networkidle',
            });

            await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

            await expectNoViolations(page, expect, {
                include: PROCLAIM_CONTENT,
                exclude: THIRD_PARTY,
            });
        });
    }

    for (const [label, url] of WIZARDS) {
        test(`${label} meets WCAG AA`, async ({ page }) => {
            await page.goto(`/administrator/${url}`, { waitUntil: 'networkidle' });

            await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

            await expectNoViolations(page, expect, {
                include: PROCLAIM_CONTENT,
                exclude: THIRD_PARTY,
            });
        });
    }

    // mod_proclaimicon renders on Joomla's Home Dashboard, outside any
    // com_proclaim view, so none of the scans above ever reach it. Scan the
    // dashboard confined to the module's own nav — the rest of the dashboard
    // is Joomla's. The site modules need no counterpart here: they wrap in
    // .com-proclaim, so the site scans pick them up wherever they are
    // published.
    test('quick-icons module meets WCAG AA', async ({ page }) => {
        await page.goto('/administrator/index.php', { waitUntil: 'networkidle' });

        const module = page.locator('nav.quick-icons[aria-label*="Proclaim"]');

        // Not published on this dashboard — nothing to scan, and axe errors
        // on an include selector that matches nothing.
        if (!(await module.count())) {
            test.skip(true, 'mod_proclaimicon is not published on this dashboard');
        }

        await expectNoViolations(page, expect, {
            include: 'nav.quick-icons[aria-label*="Proclaim"]',
        });
    });
});
