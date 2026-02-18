/**
 * E2E — Admin Dashboard (CPanel)
 *
 * Verifies that the Proclaim admin centre loads and its control-panel icons
 * are visible for an authenticated administrator.
 *
 * Joomla 5 admin uses body.com_proclaim for the component body class and
 * section#content as the main content wrapper.
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Dashboard', () => {
    test('loads the Proclaim control panel', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmadmin');

        // Page must respond successfully on the correct option
        await expect(page).toHaveURL(/option=com_proclaim/);

        // Joomla 5 admin: body carries the component class
        await expect(page.locator('body.com_proclaim')).toBeVisible();

        // Main content section must be present
        await expect(page.locator('section#content')).toBeVisible();
    });

    test('shows at least one CPanel icon or link', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmadmin');

        // CPanel card links (e.g., Backup / Restore, Check Assets)
        const links = page.locator('section#content a, #wrapper a');
        await expect(links.first()).toBeVisible();
    });
});
