// Choosing a server type in the admin's type-picker dialog.
//
// A new server opens the picker on top of the form, and the form validates the
// picker's own field, so a type cannot be chosen by writing to the hidden input
// behind it: the dialog has to be driven the way a person drives it.
//
// These are the same steps `tests/e2e/admin/servertype.spec.js` uses, which
// documents at length why each is written the way it is. In short: the picker is
// an iframe built after the page loads, so it is addressed through frameLocator()
// (never a captured Frame), and a card's click can land before its handler is
// attached, so the click is retried until the dialog has actually closed.
//
// servertype.spec.js keeps its own copy. Moving it onto this module is a change
// to a spec with a long flake history, so it is left for its own change.
const { expect } = require('@playwright/test');

// The picker's iframe, re-resolved on every action.
function dialogFrame(page) {
    return page.frameLocator('joomla-dialog iframe');
}

// Click a type card and wait for the dialog to close as a result.
async function clickTypeCard(page, key) {
    const button = dialogFrame(page).locator(`[data-type-payload="${key}"]`).first();
    const dialog = page.locator('joomla-dialog');

    await expect(async () => {
        await button.click();
        await expect(dialog).toHaveCount(0, { timeout: 1000 });
    }).toPass({ timeout: 10000 });
}

// Choose a type and wait for its addon's fields to land in the form. "Region
// visible" alone is the empty shell before the fetch resolves.
async function pickType(page, key, addonField) {
    await clickTypeCard(page, key);
    await expect(page.locator(`#server-tabset-region [name="${addonField}"]`).first()).toBeAttached();
    await expect(page.locator('#jform_type_id')).toHaveValue(key);
}

module.exports = { dialogFrame, clickTypeCard, pickType };
