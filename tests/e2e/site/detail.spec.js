/**
 * E2E — Site Sermon Detail
 *
 * Verifies that clicking a sermon from the listing opens its detail
 * page and that the key structural sections are present.
 *
 * Proclaim front-end uses:
 *   body.view-cwmsermon    — detail page body class
 *   .proclaim-main-content — outer content wrapper
 *   .proclaim-item         — the detail item wrapper
 */

const { test, expect } = require('@playwright/test');

test.describe('Site Sermon Detail', () => {
    test('clicking first sermon opens detail page', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        // Find the first sermon title link inside a .proclaim-item
        const firstLink = page.locator('.proclaim-item a').first();

        const count = await firstLink.count();
        if (count === 0) {
            test.skip(true, 'No sermon records found — skipping detail test');
            return;
        }

        await firstLink.click();
        await page.waitForLoadState('networkidle');

        // Detail page body carries view-cwmsermon class
        await expect(page.locator('body.view-cwmsermon')).toBeVisible({ timeout: 10000 });

        // Main content wrapper present
        await expect(page.locator('.proclaim-main-content')).toBeVisible();
    });

    test('detail page contains sermon heading', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        const firstLink = page.locator('.proclaim-item h5 a, .proclaim-item a[href*="/messages"]').first();

        if (await firstLink.count() === 0) {
            test.skip(true, 'No sermon records found — skipping detail test');
            return;
        }

        await firstLink.click();
        await page.waitForLoadState('networkidle');

        // Page must have a visible heading
        const heading = page.locator('h1, h2').first();
        await expect(heading).toBeVisible({ timeout: 10000 });
    });

    test('detail page structure is intact', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        const firstLink = page.locator('.proclaim-item a').first();

        if (await firstLink.count() === 0) {
            test.skip(true, 'No sermon records found — skipping detail test');
            return;
        }

        await firstLink.click();
        await page.waitForLoadState('networkidle');

        // Proclaim item wrapper on detail page
        await expect(page.locator('.proclaim-item').first()).toBeVisible({ timeout: 10000 });

        // Optional: scripture references, share button, or related studies
        // These may not be present for every sermon — just verify no JS errors
        const errors = await page.evaluate(() => window.__proclaim_errors || []);
        expect(errors).toHaveLength(0);
    });
});
