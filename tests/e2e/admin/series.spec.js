/**
 * E2E — Admin Series
 *
 * Smoke tests for the series list view and add form.
 *
 * Controller: cwmserie (singular, no 's')
 * Form field:  jform_series_text
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Series', () => {
    test('list view renders', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmseries');

        await expect(page).toHaveURL(/view=cwmseries/);
        await expect(page.locator('body.com_proclaim')).toBeVisible();

        await expect(page.locator('#j-main-container, section#content').first()).toBeVisible();
    });

    test('new-series form renders title field', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&task=cwmserie.add');

        await expect(page.locator('#jform_series_text')).toBeVisible({ timeout: 15000 });
    });
});
