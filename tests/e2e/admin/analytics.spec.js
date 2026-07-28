/**
 * E2E — Admin Analytics
 *
 * Pared down to the one thing only a browser can answer: did Chart.js run and
 * draw. The page-loads and container-visible assertions moved to the a11y
 * scan, which navigates here and fails if the page does not render.
 *
 * The KPI-card and navigation-tab tests were dropped rather than moved. Both
 * skipped whenever analytics had no data, so on a fresh install they asserted
 * nothing at all; when they did run, they restated "the page rendered" in a
 * form tied to specific class names.
 *
 * Key selectors:
 *   canvas#cwm-chart-timeseries  — Overview timeseries chart
 *   canvas[data-cwm-chart]       — any Chart.js canvas
 */

const { test, expect } = require('@playwright/test');

test.describe('Admin Analytics', () => {
    test('Chart.js renders a chart canvas', async ({ page }) => {
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmanalytics', {
            waitUntil: 'networkidle',
        });

        const timeseries = page.locator('canvas#cwm-chart-timeseries');

        // Analytics can be switched off, and an install with no data draws
        // nothing. Neither is a failure of the chart pipeline.
        if (!(await timeseries.count())) {
            test.skip(true, 'No analytics canvas — analytics disabled or no data');
        }

        await expect(timeseries).toBeVisible({ timeout: 10000 });
        await expect(page.locator('canvas[data-cwm-chart]').first()).toBeVisible();

        // A canvas element only proves the markup. Non-zero dimensions prove
        // Chart.js initialised it rather than leaving an empty placeholder —
        // the failure mode a missing or broken chart bundle actually produces.
        const box = await timeseries.boundingBox();

        expect(box.width).toBeGreaterThan(0);
        expect(box.height).toBeGreaterThan(0);
    });
});
