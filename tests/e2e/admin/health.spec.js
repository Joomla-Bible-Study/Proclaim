/**
 * E2E — System Health view
 *
 * The report is assembled from a registry rather than written into the
 * template, so "the page rendered" is not the question. What has to hold is
 * that checks reached it, that a passing one is shown rather than omitted,
 * that the active checks offer a button instead of having already run — a page
 * that quietly tested four APIs on load would look identical to one that did
 * not — and that clearing a banner reaches the controller.
 *
 * ⚠️ The last of those is here because nothing else covers it. The tasks are
 * driven by links, so the token arrives in the query string; a controller
 * checking only POST rejects every one of them, and the failure is a warning
 * banner on a page that otherwise looks perfectly correct.
 */

const { test, expect } = require('@playwright/test');

const VIEW = '/administrator/index.php?option=com_proclaim&view=cwmhealth';
const CPANEL = '/administrator/index.php?option=com_proclaim&view=cwmcpanel';

test.describe('System Health', () => {
    test('reports checks, including the ones that are passing', async ({ page }) => {
        await page.goto(VIEW);

        await expect(page.locator('section#content table tbody tr').first()).toBeVisible({
            timeout: 15000,
        });

        // A dashboard banner can only show a failure. The passing checks are
        // the point of this view, so a report without any would be the bug.
        await expect(page.getByText('Passing', { exact: false }).first()).toBeVisible();
    });

    test('an external-service check offers a button rather than a result', async ({ page }) => {
        await page.goto(VIEW);

        const testButtons = page.locator('a[href*="task=cwmhealth.test"]');

        if (!(await testButtons.count())) {
            test.skip(true, 'No API-backed media server is configured on this site');
        }

        await expect(testButtons.first()).toBeVisible();
        await expect(page.getByText('Not tested', { exact: false }).first()).toBeVisible();
    });

    test('the dashboard offers a way in', async ({ page }) => {
        await page.goto(CPANEL);

        await expect(page.locator('a[href*="view=cwmhealth"]').first()).toBeVisible({
            timeout: 15000,
        });
    });

    test('clearing a banner hides it on the dashboard and nowhere else', async ({ page }) => {
        await page.goto(CPANEL);

        const clear = page.locator('a[href*="task=cwmhealth.quieten"]');

        if (!(await clear.count())) {
            // ⚠️ Not a bare skip: no banner is only correct if the finding is
            // absent. Assert that before letting the test go green, or a
            // broken banner would read the same as a healthy site.
            await page.goto(VIEW);
            await expect(page.getByText('No servers are waiting to be migrated').first()).toBeVisible();
            test.skip(true, 'Nothing to clear: this site has no legacy servers');
        }

        await clear.first().click();

        await page.goto(CPANEL);
        await expect(page.locator('a[href*="task=cwmhealth.quieten"]')).toHaveCount(0);

        await page.goto(VIEW);
        await expect(page.locator('a[href*="task=cwmhealth.restore"]').first()).toBeVisible();

        // Put the site back the way it was found.
        await page.locator('a[href*="task=cwmhealth.restore"]').first().click();

        await page.goto(CPANEL);
        await expect(page.locator('a[href*="task=cwmhealth.quieten"]').first()).toBeVisible();
    });
});
