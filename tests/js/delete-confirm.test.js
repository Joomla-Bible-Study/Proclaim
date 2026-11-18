'use strict';

/**
 * Regression test for #1501: delete-confirm.js located the CSRF token by
 * scanning the form for `input[type="hidden"][value="1"]`. Both the
 * cwmmediafiles and cwmmessages list templates render a
 * `delete_physical_files` hidden field with value="1" immediately before
 * the real token field, so the DOM-scan always matched the wrong field —
 * the AJAX call was sent with `&delete_physical_files=1` instead of the
 * real token, `Session::checkToken('get')` failed server-side, and the
 * "physical files will be affected" warning modal never fired.
 *
 * Fixed by having the PHP view pass the token explicitly via
 * addScriptOptions('com_proclaim.deleteConfirm', {csrfToken}) instead of
 * guessing from the DOM.
 *
 * Note: innerHTML usage in setupModule() is safe — it builds DOM for unit
 * tests from static strings, not from external input.
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, it, afterEach } = require('node:test');
const { fn, returning, clearAllMocks, isolateModules } = require('./helpers/mocks');
const { includes, excludes } = require('./helpers/assert');

function setupModule(fetchJsonMock) {
    document.body.innerHTML =
        '<form id="adminForm" action="index.php?option=com_proclaim&view=cwmmediafiles">' +
            '<input type="checkbox" name="cid[]" value="42" checked />' +
            '<input type="hidden" name="task" value="" />' +
            '<input type="hidden" name="boxchecked" value="0" />' +
            '<input type="hidden" name="delete_physical_files" value="1" />' +
            '<input type="hidden" name="abc123realtoken" value="1" />' +
        '</form>';

    global.Joomla = {
        getOptions: fn((key) => {
            if (key === 'com_proclaim.deleteConfirm') {
                return { csrfToken: 'abc123realtoken' };
            }

            return {};
        }),
        Text: { _: fn((key) => key) },
        submitbutton: fn(),
    };

    global.window.ProclaimFetch = { fetchJson: fetchJsonMock };
    global.bootstrap = { Modal: fn() };

    isolateModules(() => {
        require('../../build/media_source/js/delete-confirm.es6.js');
    });
}

describe('delete-confirm.js token selection', () => {
    afterEach(() => {
        delete global.Joomla;
        delete global.window.ProclaimFetch;
        delete global.bootstrap;
        clearAllMocks();
    });

    it('sends the real CSRF token, not the delete_physical_files hidden field, in the checkDeleteFiles request', () => {
        const fetchJsonMock = fn(returning(new Promise(() => {})));
        setupModule(fetchJsonMock);

        global.Joomla.submitbutton('cwmmediafiles.delete');

        assert.strictEqual(fetchJsonMock.mock.callCount(), 1);
        const requestedUrl = fetchJsonMock.mock.calls[0].arguments[0];

        includes(requestedUrl, 'abc123realtoken=1');
        excludes(requestedUrl, 'delete_physical_files=1');
    });

    it('omits the token param entirely when no csrfToken option is provided, rather than falling back to a DOM scan', () => {
        const fetchJsonMock = fn(returning(new Promise(() => {})));
        setupModule(fetchJsonMock);
        global.Joomla.getOptions = fn(() => ({}));

        global.Joomla.submitbutton('cwmmediafiles.delete');

        const requestedUrl = fetchJsonMock.mock.calls[0].arguments[0];
        excludes(requestedUrl, 'delete_physical_files=1');
        excludes(requestedUrl, 'abc123realtoken=1');
    });
});
