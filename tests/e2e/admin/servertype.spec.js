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

// A form POST (trash/delete) navigates on its own; a goto fired into that
// in-flight navigation aborts. Retry the goto until it settles.
async function gotoList(page, url) {
    for (let i = 0; i < 3; i++) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            return;
        } catch (e) {
            if (i === 2) throw e;
            await page.waitForTimeout(500);
        }
    }
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

test("a swapped-in type's own field widgets come alive without a reload", async ({ page }) => {
    // showon (conditional fields), the media picker and an addon's inline
    // script (YouTube's Test API) all bind at page load. After an in-place
    // swap they must work anyway — see the view's asset pre-load and the
    // fragment script re-execution.
    await page.goto(ADD, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('joomla-dialog iframe')).toBeVisible();
    await pickType(page, 'local', 'jform[params][delete_files]');
    await reopenPicker(page);
    await pickType(page, 'youtube', 'jform[params][api_key]');

    const showon = '#server-tabset-region [data-showon]';
    const groupDisplay = () => page.evaluate((s) => {
        const el = document.querySelector(s);
        return getComputedStyle(el.closest('.control-group') || el).display;
    }, showon);

    // A live-event field is hidden until stream_mode is 'direct' — proof
    // showon.js loaded and wired the swapped-in markup.
    await expect.poll(groupDisplay).toBe('none');
    await page.evaluate(() => {
        const sm = document.querySelector('#server-tabset-region [name="jform[params][stream_mode]"]');
        sm.value = 'direct';
        sm.dispatchEvent(new Event('change', { bubbles: true }));
    });
    await expect.poll(groupDisplay).not.toBe('none');

    // The media picker script is present for the swapped-in media field.
    expect(await page.evaluate(() => [...document.scripts].some((s) => /joomla-field-media/.test(s.src)))).toBe(true);

    // The addon's inline Test API handler was re-executed: clicking it writes
    // into its result area instead of doing nothing.
    await page.evaluate(() => document.getElementById('youtube-test-api-btn').click());
    await expect(page.locator('#youtube-test-api-result')).not.toBeEmpty();
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
    await gotoList(page, LIST + "&filter[published]=&filter[search]=" + encodeURIComponent(NAME));
    await page.locator('tbody tr', { hasText: NAME }).locator('a[href*="cwmserver.edit"]').first().click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#jform_type_id')).toHaveValue('local');
    await page.evaluate(() => Joomla.submitbutton('cwmserver.cancel'));
    await page.waitForLoadState('networkidle');

    // Trash, then delete permanently, so the fixture never outlives the test.
    await gotoList(page, LIST + "&filter[published]=&filter[search]=" + encodeURIComponent(NAME));
    await page.locator('tbody tr', { hasText: NAME }).locator('input[name="cid[]"]').check();
    await page.evaluate(() => Joomla.submitform('cwmservers.trash', document.getElementById('adminForm')));
    await page.waitForLoadState('networkidle');

    await gotoList(page, LIST + '&filter[published]=-2&filter[search]=' + encodeURIComponent(NAME));
    await page.locator('tbody tr', { hasText: NAME }).locator('input[name="cid[]"]').check();
    page.once('dialog', (d) => d.accept());
    await page.evaluate(() => Joomla.submitform('cwmservers.delete', document.getElementById('adminForm')));
    await page.waitForLoadState('networkidle');

    await gotoList(page, LIST + '&filter[published]=-2&filter[search]=' + encodeURIComponent(NAME));
    await expect(page.locator('tbody tr', { hasText: NAME })).toHaveCount(0);
});
