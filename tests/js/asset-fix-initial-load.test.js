/**
 * @jest-environment jsdom
 */

/**
 * The Assets screen renders its status table from the `checklists` session
 * key, and the only thing that ever writes that key is the `checkAssetsXHR`
 * request behind the Refresh button. So before Refresh had been pressed once,
 * the view got an empty array, the template fell to its empty branch — which
 * renders a **spinner** — and nothing ever replaced it. The page said it was
 * loading and was not; the data only appeared if you pressed Refresh yourself.
 *
 * These exercise the shipped module rather than a copy of its logic, and
 * assert the request is actually issued, not merely that a method exists.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

/**
 * The markup the PHP template emits when it has no cached rows: a spinner,
 * and the Refresh button.
 *
 * @returns {void}
 */
function renderEmptyAssetsScreen() {
    document.body.innerHTML = `
        <table>
            <tbody id="asset-status-body">
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">JBS_CMN_LOADING</span>
                        </div>
                        <span class="ms-2">JBS_ADM_CHECKING_ASSETS</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" data-proclaim-action="refresh">Refresh</button>
    `;
}

/**
 * Load the module against whatever is currently in the DOM.
 *
 * @returns {void}
 */
function loadModule() {
    global.Joomla = {
        Text: { _: (key) => key },
        getOptions: () => '',
        renderMessages: () => {},
    };

    jest.isolateModules(() => {
        require('../../build/media_source/js/asset-fix.es6.js');
    });

    document.dispatchEvent(new Event('DOMContentLoaded'));
}

describe('Assets screen initial load', () => {
    beforeEach(() => {
        jest.resetModules();

        global.fetch = jest.fn(() => Promise.resolve({
            ok: true,
            json: () => Promise.resolve({
                success: true,
                data: { assets: [] },
            }),
        }));
    });

    afterEach(() => {
        delete global.fetch;
        document.body.innerHTML = '';
    });

    it('requests the asset status without waiting for a Refresh click', async () => {
        renderEmptyAssetsScreen();
        loadModule();

        // Let the promise chain started during init settle.
        await Promise.resolve();

        // Nothing pressed Refresh. If no request went out, the spinner the
        // template rendered would still be spinning — which is the bug.
        expect(global.fetch).toHaveBeenCalled();
    });

    it('replaces the spinner rather than leaving it on screen', async () => {
        renderEmptyAssetsScreen();

        expect(document.querySelector('#asset-status-body .spinner-border')).not.toBeNull();

        loadModule();

        // Let the fetch resolve and the render run.
        await Promise.resolve();
        await Promise.resolve();
        await Promise.resolve();

        // The spinner must be replaced by a result. An empty result is still
        // a result; a spinner left on screen is not.
        expect(document.querySelector('#asset-status-body .spinner-border')).toBeNull();
    });

    /**
     * ⚠️ The guard matters as much as the call. This module is loaded on more
     * than the Assets screen, and firing the request on a page with no table
     * would be a pointless round trip on every admin page that includes it.
     */
    it('issues no request on a page that has no asset table', async () => {
        document.body.innerHTML = '<div>Some other admin screen</div>';
        loadModule();

        await Promise.resolve();

        expect(global.fetch).not.toHaveBeenCalled();
    });
});
