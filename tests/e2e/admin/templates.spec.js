/**
 * E2E — Admin Templates
 *
 * Smoke test for the template list view.
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Templates', () => {
    test('list view renders', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmtemplates');

        await expect(page).toHaveURL(/view=cwmtemplates/);

        const container = page.locator('#j-main-container, .com-proclaim');
        await expect(container.first()).toBeVisible();
    });
});
