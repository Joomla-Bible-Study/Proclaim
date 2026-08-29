/**
 * @jest-environment jsdom
 */

/**
 * The media browsers prefill their search box with the message title, so
 * opening Browse on a sermon searches for that sermon rather than showing the
 * whole channel. It stopped working because they read the wrong element.
 *
 * Joomla 5's `ModalSelectField` renders the readable title in the input
 * carrying the field's own id, with class `js-input-title`, and puts the
 * numeric value in `<id>_id`:
 *
 *   <input class="form-control js-input-title" id="jform_study_id" readonly>
 *   <input type="hidden" id="jform_study_id_id" class="modal-value js-input-value">
 *
 * The browsers read `jform_study_id_name` — the Joomla 3/4 `modal_article`
 * convention, which has not existed since the field was replaced. There is no
 * such element, `getElementById` returned null, the guard treated that as "no
 * title available", and the box opened empty with nothing reported.
 *
 * ⚠️ These build the fixture from core's own layout markup rather than from
 * what the code happens to look for. A fixture written to match the code is how
 * this survived: the existing suite asserts against an
 * `<input id="jform_study_id_name">` that Joomla never renders.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

/**
 * The markup Joomla 5 emits for a ModalSelectField, per
 * layouts/joomla/form/field/modal-select.php.
 *
 * @param {string} fieldId The field id, e.g. 'jform_study_id'.
 * @param {string} title   The readable title it is showing.
 * @returns {string} The markup.
 */
function modalSelectMarkup(fieldId, title) {
    return `
        <div class="input-group">
            <input class="form-control js-input-title" type="text"
                   value="${title}" readonly id="${fieldId}" name="${fieldId}">
            <input type="hidden" id="${fieldId}_id" class="modal-value js-input-value" value="7">
        </div>
    `;
}

describe('Media browsers prefill the search box from the message title', () => {
    beforeEach(() => {
        jest.resetModules();

        global.Joomla = {
            Text: { _: (key) => key },
            getOptions: () => '',
            renderMessages: () => {},
        };

        window.cwmAlert = jest.fn(() => Promise.resolve());
        global.fetch = jest.fn(() => Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ success: true, data: {} }),
        }));
    });

    afterEach(() => {
        document.body.innerHTML = '';
        delete window.Proclaim;
        delete global.fetch;
    });

    // ⚠️ The three modules do not agree on the property name — Wistia calls it
    // searchName where the others call it searchQuery. Parameterised rather
    // than normalised, because asserting a name the module does not use would
    // fail for a reason unrelated to the bug under test.
    describe.each([
        ['YoutubeBrowser', 'addon-youtube-browser', 'searchQuery'],
        ['VimeoBrowser', 'addon-vimeo-browser', 'searchQuery'],
        ['WistiaBrowser', 'addon-wistia-browser', 'searchName'],
    ])('%s', (globalName, moduleName, queryProp) => {
        /**
         * Load a browser module against the current DOM.
         *
         * @returns {object|undefined} The browser object, if it registered one.
         */
        function loadBrowser() {
            jest.isolateModules(() => {
                require(`../../build/media_source/js/${moduleName}.es6.js`);
            });

            return (window.Proclaim || {})[globalName];
        }

        it('reads the title from the element Joomla actually renders', async () => {
            document.body.innerHTML = `
                <input name="jform[server_id]" value="123">
                ${modalSelectMarkup('jform_study_id', 'The Prodigal Son')}
                <input name="jform[params][filename]" value="">
            `;

            const browser = loadBrowser();

            if (!browser || typeof browser.open !== 'function') {
                throw new Error(`${globalName} did not register an open() — the fixture is wrong, not the code.`);
            }

            browser.serverId = 123;
            await browser.open();

            expect(browser[queryProp]).toBe('The Prodigal Son');
        });

        it('does not prefill from the placeholder text', async () => {
            document.body.innerHTML = `
                <input name="jform[server_id]" value="123">
                ${modalSelectMarkup('jform_study_id', 'Select a Message')}
                <input name="jform[params][filename]" value="">
            `;

            const browser = loadBrowser();
            browser.serverId = 123;
            await browser.open();

            expect(browser[queryProp]).not.toBe('Select a Message');
        });
    });
});
