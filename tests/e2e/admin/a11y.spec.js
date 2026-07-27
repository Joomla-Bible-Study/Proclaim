/**
 * E2E — WCAG 2.2 AA, administrator views
 *
 * Scans the screens a church administrator uses to run Proclaim. Confined to
 * `section#content`, which is the region the component renders: the Atum
 * template's sidebar, toolbar and header belong to Joomla, and their markup is
 * neither ours to fix nor fair to judge us on.
 *
 * Admin pages need an authenticated session; the admin-* Playwright projects
 * supply one via storageState, so these run under the same auth as the rest of
 * the admin suite.
 */

const { test, expect } = require('@playwright/test');
const { expectNoViolations } = require('../helpers/axe');

const PROCLAIM_CONTENT = 'section#content';

/**
 * The admin screens worth holding to AA, and how to reach them.
 *
 * This list also carries the "does the page render" duty that the per-entity
 * specs used to hold: each case navigates and requires `section#content`
 * before scanning, so a screen that 500s or renders blank fails here.
 */
const SCREENS = [
    ['control panel', 'cwmadmin'],
    ['sermon list', 'cwmmessages'],
    ['teacher list', 'cwmteachers'],
    ['series list', 'cwmseries'],
    ['media file list', 'cwmmediafiles'],
    ['template list', 'cwmtemplates'],
    ['analytics dashboard', 'cwmanalytics'],
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

    test('sermon edit form meets WCAG AA', async ({ page }) => {
        // Form controls are where labelling and grouping failures concentrate,
        // and where a screen-reader user is most likely to be blocked outright.
        await page.goto('/administrator/index.php?option=com_proclaim&task=cwmmessage.add', {
            waitUntil: 'networkidle',
        });

        await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

        // Two widgets here are rendered by TinyMCE and Joomla's Choices.js —
        // third-party code we neither author nor can patch:
        //
        //   .tox-tinymce   role="application" without its required ARIA
        //                  attributes, and editor iframes with no title
        //   .choices       the remove-button is under the 24x24 minimum of
        //                  SC 2.5.8
        //
        // Both are reported upstream. Excluded by selector rather than by
        // disabling the rules, so `aria-required-attr`, `frame-title` and
        // `target-size` still apply to Proclaim's own markup on this page.
        //
        // The subform tooltip icon is deliberately NOT excluded. Joomla core
        // emits it with aria-hidden="true" and tabindex="0" at once, but the
        // decision to render it is ours — message.xml selects the
        // repeatable-table layout and scripture_row.xml gives bible_version a
        // description. com_proclaim.subform-tooltip-a11y repairs it at runtime,
        // and this assertion is what proves the repair still works.
        await expectNoViolations(page, expect, {
            include: PROCLAIM_CONTENT,
            exclude: ['.tox-tinymce', '.choices'],
        });
    });
});
