/**
 * E2E — Public site navigation and media
 *
 * What survived the pruning of listing.spec.js and detail.spec.js. Between
 * them those files held seven tests, five of which asserted some variation of
 * "the page responded and a container is visible" — exactly what the a11y scan
 * does before it scans, on both the listing and the detail page.
 *
 * Left here is what a scan cannot answer: that a listing row actually leads
 * somewhere, and that the media player initialises. Both need a real click.
 *
 * Both degrade on an empty database, as the site suite always has.
 */

const { test, expect } = require('@playwright/test');

const LISTING = '/?option=com_proclaim&view=cwmsermons';

test.describe('Site navigation', () => {
    test('a listing row links through to its sermon detail page', async ({ page }) => {
        await page.goto(LISTING, { waitUntil: 'networkidle' });

        const firstLink = page.locator('.proclaim-item a[href]').first();

        if (!(await firstLink.count())) {
            test.skip(true, 'No sermon records in this database');
        }

        // A link that exists is not a link that works: routing, SEF rules and
        // the detail view's own query all sit between the click and the page.
        await firstLink.click();
        await page.waitForLoadState('networkidle');

        await expect(page.locator('.proclaim-main-content')).toBeVisible({ timeout: 15000 });
        await expect(page.locator('h1, h2, .proclaim-detail-title').first()).toBeVisible();
    });

    test('fancybox media player opens and closes', async ({ page }) => {
        await page.goto(LISTING, { waitUntil: 'networkidle' });

        const firstLink = page.locator('.proclaim-item a[href]').first();

        if (!(await firstLink.count())) {
            test.skip(true, 'No sermon records in this database');
        }

        await firstLink.click();
        await page.waitForLoadState('networkidle');

        const playerLink = page.locator('a.fancybox_player').first();

        if (!(await playerLink.count())) {
            test.skip(true, 'This sermon has no fancybox media');
        }

        await playerLink.click();

        // Fancybox v6 mounts a .fancybox__container overlay.
        const overlay = page.locator('.fancybox__container');
        await expect(overlay).toBeVisible({ timeout: 10000 });

        // Closing matters as much as opening: an overlay that traps the user
        // is worse than one that never opened.
        await page.keyboard.press('Escape');
        await expect(overlay).not.toBeVisible({ timeout: 5000 });
    });
});
