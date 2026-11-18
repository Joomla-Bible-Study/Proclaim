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
const SETTINGS = '/administrator/index.php?option=com_proclaim&task=cwmadmin.edit&id=1';
const NAME = 'zz e2e type-swap fixture';

const FIELD = '.js-modal-content-select-field:has(#jform_type_id)';

// The picker's iframe.
//
// ⚠️ Not `page.locator('joomla-dialog iframe').elementHandle()` then
// `.contentFrame()`. `joomla-dialog.w-c.es6.js` builds this iframe by
// `document.createElement('iframe')`, sets `.src` on the still-detached node,
// *then* appends it to the DOM — so the element Playwright can query for
// exists before the browser has necessarily settled on the frame that will
// carry the real navigation. `contentFrame()` resolves to a Frame object at
// that instant; if Chromium recreates the frame's execution context once the
// navigation actually starts (routine for a fresh iframe going from
// about:blank to a real document), the captured Frame is now stale and
// `waitForLoadState()` on it waits for an event that will never fire there.
//
// Proven from a CI trace (#2122), not theorised: the failure was
// `frame.waitForLoadState: Test timeout of 30000ms exceeded`, while the page
// snapshot taken at that same timeout showed the iframe's content already
// fully rendered and interactive — the document had loaded; the *handle*
// pointed at the wrong Frame instance.
//
// `frameLocator()` has no equivalent failure mode: it never captures a
// static Frame reference, so every action re-resolves whichever frame
// currently matches the selector.
function dialogFrame(page) {
    return page.frameLocator('joomla-dialog iframe');
}

// Click a type card and wait for the dialog to actually close as a result.
//
// A second, independent race lives one level up from the Frame-staleness one
// above: `admin/tmpl/cwmservers/types.php` binds its click handling *inside*
// `DOMContentLoaded`, delegated on `document` —
//
//     document.addEventListener('DOMContentLoaded', function () {
//         document.addEventListener('click', function (e) { ... choose(...) });
//     });
//
// — and `useScript('core')` on the same page can still be executing when the
// cards are already visible and clickable. Playwright's own actionability
// checks (visible, stable, receives events) are about the target element,
// not about whether some unrelated document-level listener has been
// registered yet, so they cannot see this gap. A click that lands first is
// not slow to be handled — nothing is listening yet, so it is dropped
// outright, which is why the previous failure mode was a hang for the full
// timeout rather than an eventual close.
//
// Retrying the click itself, against the observable effect (the dialog
// closing), is safe rather than merely convenient: `choose()` only ever runs
// from inside that delegated listener, so every click thrown before it is
// registered is a true no-op, never a double-fire. At most one attempt in
// the loop ever reaches `choose()`.
async function clickTypeCard(page, key) {
    const button = dialogFrame(page).locator(`[data-type-payload="${key}"]`).first();
    const dialog = page.locator('joomla-dialog');

    await expect(async () => {
        await button.click();
        await expect(dialog).toHaveCount(0, { timeout: 1000 });
    }).toPass({ timeout: 10000 });
}

// Pick a type from the open dialog and wait for its addon fields to actually
// land in the region — "region visible" alone is the empty shell before the
// fetch resolves.
async function pickType(page, key, addonField) {
    await clickTypeCard(page, key);
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

// Set Proclaim's Simple Mode and return the value as found, so a caller can
// put the site back the way it was.
//
// Simple Mode drops every fieldset an addon marks simplemode="hide". YouTube
// keeps its conditional fields and its media field in ones that are, so with
// it on there is nothing for the widget probes below to bind to (#2121).
//
// Writes only when the value actually has to change. Saving this form rewrites
// the whole settings row, and a run has no business doing that to a site that
// is already configured the way the test needs; the no-change path leaves the
// edit view by its own cancel task instead.
async function setSimpleMode(page, value) {
    await page.goto(SETTINGS, { waitUntil: 'domcontentloaded' });

    // Both halves of the pair, exactly one of them selected: a positive signal
    // that the settings form rendered at all. Reading :checked straight off a
    // login page, or off a param stored as "" rather than "0"/"1", reports a
    // selector that matched nothing instead of the reason it did not.
    const radios = page.locator('input[name="jform[params][simple_mode]"]');
    await expect(radios).toHaveCount(2);
    await expect(radios.and(page.locator(':checked'))).toHaveCount(1);

    const found = await radios.and(page.locator(':checked')).inputValue();

    if (found === value) {
        await page.evaluate(() => Joomla.submitbutton('cwmadmin.cancel'));
        await page.waitForLoadState('networkidle');

        return found;
    }

    await page.evaluate((v) => {
        const radio = document.querySelector(`input[name="jform[params][simple_mode]"][value="${v}"]`);
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
    }, value);

    // save, not apply: nothing below needs the form left open.
    await page.evaluate(() => Joomla.submitbutton('cwmadmin.save'));
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#system-message-container')).toContainText(/saved/i);

    return found;
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

    // The dialog closes from choose() itself, before the (here, failing)
    // fetch even starts — so the same retry-until-closed click is correct
    // regardless of the route above.
    await clickTypeCard(page, 'local');

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
    //
    // Two of the three live in simplemode="hide" fieldsets, so this needs
    // Simple Mode off to have anything to look at. Restore it either way:
    // it is a site-wide setting, not this test's to leave changed.
    const wasSimple = await setSimpleMode(page, '0');

    try {
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

        // Assert the probe exists before reading through it: a region without
        // a conditional field is a finding, and it should read as one rather
        // than as a TypeError inside a page.evaluate (#2121).
        await expect(page.locator(showon).first()).toBeAttached();

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
    } finally {
        await setSimpleMode(page, wasSimple);
    }
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
