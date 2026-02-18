/**
 * E2E — Admin Teachers
 *
 * Smoke tests for the teacher list view and add form.
 *
 * Controller: cwmteacher
 * Form field:  jform_teachername (no underscore between "teacher" and "name")
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Teachers', () => {
    test('list view renders', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmteachers');

        await expect(page).toHaveURL(/view=cwmteachers/);
        await expect(page.locator('body.com_proclaim')).toBeVisible();
        await expect(page.locator('section#content')).toBeVisible();
    });

    test('new-teacher form renders name field', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&task=cwmteacher.add');

        // Field name is "teachername" (no underscore)
        await expect(page.locator('#jform_teachername')).toBeVisible({ timeout: 15000 });
    });
});
