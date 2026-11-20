'use strict';

/**
 * Tests for admin-ai-models.es6.js — the AI provider model fetcher.
 *
 * The API key must never travel in the request URL. It used to be appended as
 * `&api_key=…` on a GET, which wrote the key verbatim into the web server's
 * access log on every call — a file that is read by more people, retained
 * longer, and shipped further than the component parameter the key is stored
 * in, and which is not what anyone auditing "who can see this key" would think
 * to look at. It reached browser history and any intermediate proxy too.
 *
 * ⚠️ Asserted on the request the module actually makes, not on the source text.
 * A test that greps the file for `api_key=` passes the moment the string moves
 * behind a variable, whether or not it still ends up in the URL.
 *
 * @package  Proclaim.Tests
 * @since    10.7.1
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach } = require('node:test');
const { fn, resolving, resetModules, isolateModules } = require('./helpers/mocks');
const { includes, excludes } = require('./helpers/assert');

describe('admin-ai-models', () => {
    const API_KEY = 'AIzaSyTESTKEY_must_never_reach_a_url';

    let fetchJson;

    /**
     * Build the DOM the module binds to and load it.
     *
     * @param   {string}  savedModel  A previously saved model id, or ''
     *
     * @returns {void}
     */
    function loadModule(savedModel = '') {
        document.body.innerHTML = `
            <div id="ai-models-config" data-token="csrf123" data-saved-model="${savedModel}"></div>
            <select id="jform_params_ai_provider"><option value="claude" selected>Claude</option></select>
            <select id="jform_params_ai_model"><option value="">Provider Default</option></select>
            <input id="jform_params_ai_api_key" type="password" value="${API_KEY}">
            <button id="btn-fetch-ai-models">Fetch</button>
            <span id="ai-models-status"></span>
        `;

        isolateModules(() => {
            require('../../build/media_source/js/admin-ai-models.es6.js');
        });
    }

    beforeEach(() => {
        resetModules();

        // ⚠️ Cleared, not ignored. The module serves a cached model list from
        // localStorage without making any request, so a leftover entry would
        // let a test that is supposed to observe a request pass having
        // observed nothing.
        localStorage.clear();

        fetchJson = fn(resolving({
            success: true,
            models: [{ id: 'claude-opus-5', name: 'Claude Opus 5' }],
        }));

        global.window.ProclaimFetch = { fetchJson };
        global.Joomla = { Text: { _: (k) => k } };
    });

    /**
     * Press the fetch button and wait for the request to settle.
     *
     * @returns {Promise<void>}
     */
    async function clickFetch() {
        document.getElementById('btn-fetch-ai-models').click();
        await new Promise((resolve) => { setTimeout(resolve, 0); });
    }

    test('the API key is not in the request URL', async () => {
        loadModule();
        await clickFetch();

        assert.ok(fetchJson.mock.callCount() > 0);

        const [url] = fetchJson.mock.calls[0].arguments;

        excludes(url, API_KEY);
        excludes(url, encodeURIComponent(API_KEY));
        assert.doesNotMatch(url, /api_key/i);
    });

    test('the request is a POST carrying the key in its body', async () => {
        loadModule();
        await clickFetch();

        const [, options] = fetchJson.mock.calls[0].arguments;

        assert.strictEqual(options.method, 'POST');
        assert.ok(options.body instanceof URLSearchParams);
        assert.strictEqual(options.body.get('api_key'), API_KEY);
        assert.strictEqual(options.body.get('provider'), 'claude');
    });

    /**
     * ⚠️ Not vacuous. A module that stopped sending the key at all would pass
     * both assertions above while breaking the feature, so the response has to
     * be shown to arrive and be used.
     */
    test('the fetched models still populate the dropdown', async () => {
        loadModule();
        await clickFetch();

        const options = Array.from(document.getElementById('jform_params_ai_model').options)
            .map((o) => o.value);

        includes(options, 'claude-opus-5');
    });

    test('the task is still addressed in the URL, only the secret is not', async () => {
        loadModule();
        await clickFetch();

        const [url] = fetchJson.mock.calls[0].arguments;

        includes(url, 'task=cwmadmin.fetchAiModelsXHR');
        includes(url, 'csrf123=1');
    });
});
