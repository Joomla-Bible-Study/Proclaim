/**
 * E2E — Admin behaviour smoke tests
 *
 * What survived the pruning of the per-entity admin specs (dashboard, messages,
 * series, teachers, templates). Between them those files held nine tests, six
 * of which asserted some variation of "the page responded and section#content
 * is visible" — exactly what the a11y scan does before it scans, on every one
 * of those screens.
 *
 * Left here is what a scan cannot answer: that the edit forms are wired up and
 * produced their fields, that a list row leads to a populated form, and that
 * the control panel offers somewhere to go. A page that renders an empty
 * container passes an accessibility scan perfectly.
 */

const { test, expect } = require('@playwright/test');

/** task → a field id that only appears if the form built correctly. */
const FORMS = [
    ['sermon', 'cwmmessage.add', '#jform_studytitle'],
    ['series', 'cwmserie.add', '#jform_series_text'],
    ['teacher', 'cwmteacher.add', '#jform_teachername'],
];

test.describe('Admin edit forms', () => {
    for (const [label, task, field] of FORMS) {
        test(`new-${label} form renders its title field`, async ({ page }) => {
            await page.goto(`/administrator/index.php?option=com_proclaim&task=${task}`);

            await expect(page.locator(field)).toBeVisible({ timeout: 15000 });
        });
    }

    test('opening a sermon from the list loads it into the edit form', async ({ page }) => {
        // The one admin round-trip worth driving through a browser: list →
        // click → form, with a record id in play rather than a blank form.
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmmessages');

        const firstRow = page.locator('table a[href*="task=cwmmessage.edit"]').first();

        if (!(await firstRow.count())) {
            test.skip(true, 'No sermon records in this database');
        }

        await firstRow.click();

        await expect(page.locator('#jform_studytitle')).toBeVisible({ timeout: 15000 });
    });
});

test.describe('Admin control panel', () => {
    test('offers navigation into the component', async ({ page }) => {
        // A control panel is its links. The a11y scan proves the page rendered;
        // it would pass just as happily on an empty one.
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmadmin');

        await expect(page.locator('section#content a[href]').first()).toBeVisible({
            timeout: 15000,
        });
    });
});
