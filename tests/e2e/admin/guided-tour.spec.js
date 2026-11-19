/**
 * E2E — the control panel's Start Tour button
 *
 * Everything the button does comes from Joomla's System - Guided Tours plugin:
 * the plugin loads the click handler and serves the tour. With the plugin off,
 * a button that still renders does nothing when clicked, so it must not be
 * offered at all.
 *
 * ⚠️ Which half of the test runs depends on the site. CI's disposable site
 * turns the plugin off (see e2e.yml), so there it proves the button is hidden.
 * A dev site with the plugin on proves the button still starts the tour.
 */

const { test, expect } = require('@playwright/test');

const CPANEL = '/administrator/index.php?option=com_proclaim&view=cwmcpanel';

// Core's "Take a Tour" header menu uses the same class with data-id, so match
// Proclaim's own button by its uid.
const START_TOUR = ".button-start-guidedtour[data-gt-uid='com_proclaim_whats_new_10_1']";

test.describe('Start Tour', () => {
    test('is offered only while the Guided Tours plugin can run it', async ({ page }) => {
        await page.goto(CPANEL);

        // The plugin publishes this option on every admin page it runs on.
        const pluginRuns = await page.evaluate(
            () => Boolean(window.Joomla && Joomla.getOptions('com_guidedtours.token', ''))
        );
        const button = page.locator(START_TOUR);

        // Say which half ran, so a report can't pass for the wrong reason unnoticed.
        test.info().annotations.push({ type: 'guided-tours-plugin', description: pluginRuns ? 'on' : 'off' });

        if (!pluginRuns) {
            await expect(button).toHaveCount(0);

            return;
        }

        await expect(button).toBeVisible();
        await button.click();

        // Shepherd renders each tour step as a .shepherd-element dialog.
        await expect(page.locator('.shepherd-element')).toBeVisible({ timeout: 15000 });
    });
});
