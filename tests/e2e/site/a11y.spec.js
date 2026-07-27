/**
 * E2E — WCAG 2.2 AA, public site views
 *
 * Scans the pages a visitor actually reaches. The scan is confined to
 * `.proclaim-main-content` where possible, because the surrounding page is the
 * site's Joomla template and other extensions — markup Proclaim does not own
 * and cannot fix.
 *
 * These tests degrade like the rest of the site suite: with no sermon data the
 * page still renders a container, and an empty container is still scanned.
 */

const { test, expect } = require('@playwright/test');
const { expectNoViolations } = require('../helpers/axe');

const PROCLAIM_CONTENT = '.proclaim-main-content';

test.describe('Site accessibility (WCAG 2.2 AA) @a11y', () => {
    test('sermon listing meets WCAG AA', async ({ page }) => {
        const response = await page.goto('/?option=com_proclaim&view=cwmsermons', {
            waitUntil: 'networkidle',
        });

        expect(response.status()).toBeLessThan(400);
        await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

        await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
    });

    test('sermon detail meets WCAG AA', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        const firstLink = page.locator('.proclaim-item a[href]').first();

        // Nothing to open on an empty database — skip rather than fail, the
        // same way the rest of the site suite handles missing data.
        if (!(await firstLink.count())) {
            test.skip(true, 'No sermons in this database to open');
        }

        await firstLink.click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

        await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
    });

    test('teacher listing meets WCAG AA', async ({ page }) => {
        const response = await page.goto('/?option=com_proclaim&view=cwmteachers', {
            waitUntil: 'networkidle',
        });

        expect(response.status()).toBeLessThan(400);
        await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

        await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
    });

    // The site view is cwmseriesdisplays — cwmseries is the *admin* view name
    // and 404s on the front end. Worth stating, because the two halves of the
    // component do not use matching names and the mistake looks like a bug.
    test('series listing meets WCAG AA', async ({ page }) => {
        const response = await page.goto('/?option=com_proclaim&view=cwmseriesdisplays', {
            waitUntil: 'networkidle',
        });

        expect(response.status()).toBeLessThan(400);
        await expect(page.locator(PROCLAIM_CONTENT)).toBeVisible({ timeout: 15000 });

        await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
    });
});
