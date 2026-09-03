// Choosing a server type must not submit the form or reload the page (#2037).
//
// The old flow submitted the whole edit form to change type: typed work rode
// through a submit never meant to save, and every failure became a navigation.
// The picker now fetches the re-rendered tab region and swaps it in place.
// These tests drive the real picker end to end — the window sentinel below
// dies on any navigation, so they fail against the old round-trip flow.
const { test, expect } = require('@playwright/test');

const ADD = '/administrator/index.php?option=com_proclaim&task=cwmserver.add';
const LIST = '/administrator/index.php?option=com_proclaim&view=cwmservers';
const NAME = 'zz e2e type-swap fixture';

const FIELD = '.js-modal-content-select-field:has(#jform_type_id)';

// Pick a type from the open dialog and wait for its addon fields to actually
// land in the region — "region visible" alone is the empty shell before the
// fetch resolves.
async function pickType(page, key, addonField) {
    const frame = page.frameLocator('joomla-dialog iframe');
    await frame.locator(`[data-type-payload="${key}"]`).first().click();
    await expect(page.locator('joomla-dialog')).toHaveCount(0);
    await expect(page.locator(`#server-tabset-region [name="${addonField}"]`).first()).toBeAttached();
    await expect(page.locator('#jform_type_id')).toHaveValue(key);
}

// Once a value is set the "Select" button hides behind "Clear"; changing the
// choice is Clear-then-Select, exactly as a person would.
async function reopenPicker(page) {
    await page.locator(`${FIELD} [data-button-action="clear"]`).click();
    await page.locator(`${FIELD} [data-button-action="select"]`).click();
    await expect(page.locator('joomla-dialog iframe')).toBeVisible();
}

test('picking a type swaps fields in place and preserves typed work', async ({ page }) => {
    await page.goto(ADD, { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => { window.__noReload = 'held'; });

    // A new record auto-opens the picker.
    await expect(page.locator('joomla-dialog iframe')).toBeVisible();
    await pickType(page, 'local', 'jform[params][delete_files]');

    await page.fill('#jform_server_name', NAME);

    // Change of mind: reopen and pick a different type.
    await reopenPicker(page);
    await pickType(page, 'youtube', 'jform[params][api_key]');

    // The typed name survived the swap…
    await expect(page.locator('#jform_server_name')).toHaveValue(NAME);
    // …the hidden value the model reads carries the choice…
    await expect(page.locator('#jform_type_id')).toHaveValue('youtube');
    // …and the page never navigated. This is the whole point of #2037.
    expect(await page.evaluate(() => window.__noReload)).toBe('held');
    expect(page.url()).toContain('cwmserver');
});

test('a failed swap rolls back and says nothing changed', async ({ page }) => {
    await page.goto(ADD, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('joomla-dialog iframe')).toBeVisible();

    // Make the type fetch fail outright.
    await page.route('**/*cwmserver.typeFields*', (r) => r.fulfill({ status: 500, body: '' }));

    const frame = page.frameLocator('joomla-dialog iframe');
    await frame.locator('[data-type-payload="local"]').first().click();
    await expect(page.locator('joomla-dialog')).toHaveCount(0);

    // The message admits nothing changed — and nothing did: the optimistic
    // type is rolled back and no addon fields were injected, so a Save here
    // cannot persist a half-applied server (#2037 failure contract).
    await expect(page.locator('#system-message-container')).toContainText(/could not be loaded/i);
    await expect(page.locator('#jform_type_id')).toHaveValue('');
    await expect(page.locator('#server-tabset-region [name="jform[params][delete_files]"]')).toHaveCount(0);
});

test('the chosen type persists on save, then clean up', async ({ page }) => {
    await page.goto(ADD, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('joomla-dialog iframe')).toBeVisible();
    await pickType(page, 'local', 'jform[params][delete_files]');
    await page.fill('#jform_server_name', NAME);
    await page.evaluate(() => Joomla.submitbutton('cwmserver.save'));
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#system-message-container')).toContainText(/saved/i);

    // Reopen the saved record: the type reached the model via the swap path.
    await page.goto(LIST + "&filter[published]=&filter[search]=" + encodeURIComponent(NAME), { waitUntil: 'networkidle' });
    await page.locator('tbody tr', { hasText: NAME }).locator('a[href*="cwmserver.edit"]').first().click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#jform_type_id')).toHaveValue('local');
    await page.evaluate(() => Joomla.submitbutton('cwmserver.cancel'));
    await page.waitForLoadState('networkidle');

    // Trash, then delete permanently, so the fixture never outlives the test.
    await page.goto(LIST + "&filter[published]=&filter[search]=" + encodeURIComponent(NAME), { waitUntil: 'networkidle' });
    await page.locator('tbody tr', { hasText: NAME }).locator('input[name="cid[]"]').check();
    await page.evaluate(() => Joomla.submitform('cwmservers.trash', document.getElementById('adminForm')));
    await page.waitForLoadState('networkidle');

    await page.goto(LIST + '&filter[published]=-2&filter[search]=' + encodeURIComponent(NAME), { waitUntil: 'networkidle' });
    await page.locator('tbody tr', { hasText: NAME }).locator('input[name="cid[]"]').check();
    page.once('dialog', (d) => d.accept());
    await page.evaluate(() => Joomla.submitform('cwmservers.delete', document.getElementById('adminForm')));
    await page.waitForLoadState('networkidle');

    await page.goto(LIST + '&filter[published]=-2&filter[search]=' + encodeURIComponent(NAME), { waitUntil: 'networkidle' });
    await expect(page.locator('tbody tr', { hasText: NAME })).toHaveCount(0);
});
