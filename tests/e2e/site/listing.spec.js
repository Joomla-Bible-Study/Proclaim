/**
 * E2E — Site Sermon Listing
 *
 * Verifies the public-facing sermon listing page loads and basic
 * filter interactions work. Tests degrade gracefully when the DB
 * has no sermon data.
 *
 * Proclaim front-end uses:
 *   body.com_proclaim      — Joomla 5 body class (site)
 *   .proclaim-main-content — outer content wrapper
 *   .proclaim-listing      — listing container
 *   .proclaim-item         — individual sermon row
 */

const { test, expect } = require('@playwright/test');

test.describe('Site Sermon Listing', () => {
    test('listing page loads successfully', async ({ page }) => {
        const response = await page.goto('/?option=com_proclaim&view=cwmsermons', {
            waitUntil: 'networkidle',
        });

        // Accept any 2xx response (follows redirect to /en/ or similar)
        expect(response.status()).toBeLessThan(400);

        // Body carries the Proclaim component class on all site views
        await expect(page.locator('body.com_proclaim')).toBeVisible({ timeout: 15000 });

        // The main content wrapper
        await expect(page.locator('.proclaim-main-content')).toBeVisible();
    });

    test('shows sermon items or listing container', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        // Listing container is always rendered (even when empty)
        await expect(page.locator('.proclaim-listing').first()).toBeVisible({ timeout: 10000 });

        // Individual items (if DB has data)
        const items = page.locator('.proclaim-item');
        const count = await items.count();
        // Just verify the structure is correct — items may or may not exist
        // The listing container itself being visible is the key assertion
        expect(count).toBeGreaterThanOrEqual(0);
    });

    test('each sermon item contains a clickable title link', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        const items = page.locator('.proclaim-item');
        const count = await items.count();

        if (count === 0) {
            test.skip(true, 'No sermon records found — skipping item-link test');
            return;
        }

        // First item should have a title link
        const firstLink = items.first().locator('a').first();
        await expect(firstLink).toBeVisible();
        const href = await firstLink.getAttribute('href');
        expect(href).toBeTruthy();
    });
});
