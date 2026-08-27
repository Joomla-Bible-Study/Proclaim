const { test } = require('@playwright/test');
test('panel with image checks', async ({ page }) => {
    await page.setViewportSize({ width: 1500, height: 1900 });
    await page.goto('/administrator/index.php?option=com_proclaim&task=cwmadmin.edit&id=1');
    const panel = page.locator('.card').filter({ hasText: 'System Health' }).first();
    await panel.waitFor({ timeout: 20000 });
    await panel.scrollIntoViewIfNeeded();
    await panel.screenshot({ path: '/tmp/health-panel2.png' });
});
