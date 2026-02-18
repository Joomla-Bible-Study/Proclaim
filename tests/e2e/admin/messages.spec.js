/**
 * E2E — Admin Messages (Sermons)
 *
 * Smoke tests for the sermon list view and add/edit form.
 *
 * Controller name: cwmmessage (not cwmsermon)
 * List view:       view=cwmsermons
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Messages', () => {
    test('list view renders toolbar and content', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmsermons');

        await expect(page).toHaveURL(/view=cwmsermons/);

        // Body carries the component class in Joomla 5 admin
        await expect(page.locator('body.com_proclaim')).toBeVisible();

        // Main content section
        await expect(page.locator('section#content')).toBeVisible();
    });

    test('new-sermon form renders required fields', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&task=cwmmessage.add');

        // Study title field must be present
        await expect(page.locator('#jform_studytitle')).toBeVisible({ timeout: 15000 });
    });

    test('clicking first list row opens edit form', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmsermons');

        // Find the first clickable row title link in the list
        const firstRowLink = page.locator('section#content td a, section#content .list-title a').first();

        const count = await firstRowLink.count();
        if (count === 0) {
            test.skip(true, 'No sermon records found — skipping edit-row test');
            return;
        }

        await firstRowLink.click();
        await page.waitForURL(/task=cwmmessage\.edit|id=\d+/, { timeout: 15000 });
        await expect(page.locator('#jform_studytitle')).toBeVisible();
    });
});
