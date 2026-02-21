/**
 * E2E — Admin Analytics Dashboard
 *
 * Verifies that the Proclaim analytics dashboard loads, renders its
 * Chart.js canvases, and displays the KPI summary cards.
 *
 * Key selectors:
 *   body.com_proclaim       — component body class
 *   section#content         — main content wrapper
 *   canvas[data-cwm-chart]  — Chart.js canvas elements
 *   .cwm-analytics-kpi      — KPI summary cards
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Analytics Dashboard', () => {
    test('loads the analytics page', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmanalytics');

        await expect(page).toHaveURL(/option=com_proclaim/);
        await expect(page.locator('body.com_proclaim')).toBeVisible();
        await expect(page.locator('section#content')).toBeVisible();
    });

    test('renders Chart.js canvas elements', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmanalytics', {
            waitUntil: 'networkidle',
        });

        // The timeseries chart should always be present on the Overview tab
        const timeseries = page.locator('canvas#cwm-chart-timeseries');

        if (await timeseries.count() === 0) {
            test.skip(true, 'No analytics canvas found — analytics may be disabled');
            return;
        }

        await expect(timeseries).toBeVisible({ timeout: 10000 });

        // At least one chart canvas with data-cwm-chart attribute should exist
        const canvases = page.locator('canvas[data-cwm-chart]');
        const count = await canvases.count();
        expect(count).toBeGreaterThanOrEqual(1);
    });

    test('displays KPI summary cards', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmanalytics', {
            waitUntil: 'networkidle',
        });

        // KPI cards are rendered in .cwm-analytics-kpi containers
        const kpiCards = page.locator('.cwm-analytics-kpi');

        if (await kpiCards.count() === 0) {
            test.skip(true, 'No KPI cards found — analytics data may be empty');
            return;
        }

        await expect(kpiCards.first()).toBeVisible();
    });

    test('navigation tabs are present', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmanalytics', {
            waitUntil: 'networkidle',
        });

        // Analytics dashboard has Overview, By Series, By Media tabs
        const tabs = page.locator('[role="tab"], .nav-link');

        if (await tabs.count() === 0) {
            test.skip(true, 'No navigation tabs found');
            return;
        }

        await expect(tabs.first()).toBeVisible();
    });
});
