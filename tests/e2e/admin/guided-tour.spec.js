/**
 * E2E — starting a guided tour from the control panel
 *
 * Everything here comes from Joomla's System - Guided Tours plugin: the plugin
 * loads the click handler and serves the tour. With the plugin off, a button
 * that still renders does nothing when clicked, so it must not be offered, and
 * a post-install link (?startTour=...) must not ask for a tour.
 *
 * A post-install link starts its tour through core's own click handler, so the
 * second test is the one place that shows core accepting that start.
 *
 * ⚠️ Which half of each test runs depends on the site. CI's disposable site
 * turns the plugin off (see e2e.yml), so there they prove nothing is offered.
 * A dev site with the plugin on proves the tours still start.
 */

const { test, expect } = require('@playwright/test');

const CPANEL = '/administrator/index.php?option=com_proclaim&view=cwmcpanel';

// Core's "Take a Tour" header menu uses the same class with data-id, so match
// Proclaim's own button by its uid.
const START_TOUR = ".button-start-guidedtour[data-gt-uid='com_proclaim_whats_new_10_1']";

/**
 * Whether the Guided Tours plugin ran on the page, recorded on the test so a
 * report shows which half ran and can't pass for the wrong reason unnoticed.
 *
 * @param   {import('@playwright/test').Page}  page  The page.
 *
 * @returns {Promise<boolean>}
 */
const pluginRuns = async (page) => {
    // The plugin publishes this option on every admin page it runs on.
    const runs = await page.evaluate(() => Boolean(window.Joomla && Joomla.getOptions('com_guidedtours.token', '')));

    test.info().annotations.push({ type: 'guided-tours-plugin', description: runs ? 'on' : 'off' });

    return runs;
};

test.describe('Start Tour', () => {
    test('is offered only while the Guided Tours plugin can run it', async ({ page }) => {
        await page.goto(CPANEL);

        const button = page.locator(START_TOUR);

        if (!(await pluginRuns(page))) {
            await expect(button).toHaveCount(0);

            return;
        }

        await expect(button).toBeVisible();
        await button.click();

        // Shepherd renders each tour step as a .shepherd-element dialog.
        await expect(page.locator('.shepherd-element')).toBeVisible({ timeout: 15000 });
    });

    test('a startTour=1 link starts the What\'s New tour only while the plugin can run it', async ({ page }) => {
        await page.goto(`${CPANEL}&startTour=1`);

        if (!(await pluginRuns(page))) {
            // Nothing asks core for a tour, so there is nothing to wait out.
            const cpanel = await page.evaluate(() => Joomla.getOptions('com_proclaim.cpanel', {}));

            expect(cpanel.startTour).toBeUndefined();

            return;
        }

        // Core opens on a step showing the tour's title. A symlinked dev site lacks
        // the tour's .ini in the system language folder and shows the key instead.
        await expect(page.locator('.shepherd-element')).toContainText(
            /What's New in Proclaim 10\.1|COM_PROCLAIM_TOUR_WHATS_NEW_TITLE/,
            { timeout: 15000 }
        );
    });
});
