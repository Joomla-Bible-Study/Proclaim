'use strict';

/**
 * Tests for cwm-fetch.es6.js
 *
 * Tests timeout, retry, backoff, abort, gatekeeper detection, and session expiry.
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach } = require('node:test');
const { fn, resolving, rejecting, clearAllMocks } = require('./helpers/mocks');
const { calledWith, match } = require('./helpers/assert');

// Mock Joomla global
global.Joomla = {
    renderMessages: fn(),
    Text: { _: fn((key) => key) },
};

// Load the module (IIFE sets window.ProclaimFetch)
require('../../build/media_source/js/cwm-fetch.es6.js');

/**
 * Create a mock Response-like object for jsdom (which lacks the real Response API).
 *
 * @param {string|object} body          Body content (string or object for JSON)
 * @param {object}        [init={}]     status, statusText, headers
 * @returns {object}  Mock response with ok, status, statusText, headers, json(), text()
 */
function mockResponse(body, init = {}) {
    const status = init.status || 200;
    const statusText = init.statusText || '';
    const rawHeaders = init.headers || {};

    return {
        ok: status >= 200 && status < 300,
        status,
        statusText,
        headers: {
            get(name) {
                // Case-insensitive header lookup
                const lower = name.toLowerCase();
                const key = Object.keys(rawHeaders).find((k) => k.toLowerCase() === lower);
                return key ? rawHeaders[key] : null;
            },
        },
        json() {
            const text = typeof body === 'object' ? JSON.stringify(body) : body;
            return Promise.resolve(JSON.parse(text));
        },
        text() {
            const text = typeof body === 'object' ? JSON.stringify(body) : body;
            return Promise.resolve(text);
        },
    };
}

describe('ProclaimFetch', () => {
    let ProclaimFetch;

    beforeEach(() => {
        ProclaimFetch = window.ProclaimFetch;
        clearAllMocks();
        global.fetch = fn();
    });

    afterEach(() => {
        delete global.fetch;
    });

    // =========================================================================
    // Presets
    // =========================================================================

    describe('Presets', () => {
        test('exports timeout presets', () => {
            assert.strictEqual(ProclaimFetch.ADMIN_TIMEOUT, 30000);
            assert.strictEqual(ProclaimFetch.FRONTEND_TIMEOUT, 15000);
            assert.strictEqual(ProclaimFetch.LONG_TIMEOUT, 60000);
        });

        test('exports fetch and fetchJson methods', () => {
            assert.strictEqual(typeof ProclaimFetch.fetch, 'function');
            assert.strictEqual(typeof ProclaimFetch.fetchJson, 'function');
        });
    });

    // =========================================================================
    // fetch() — basic behavior
    // =========================================================================

    describe('fetch()', () => {
        test('returns Response on success', async () => {
            const resp = mockResponse('ok', { status: 200 });
            global.fetch.mock.mockImplementation(resolving(resp));

            const result = await ProclaimFetch.fetch('/api/test');

            assert.strictEqual(result, resp);
            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });

        test('passes fetch options through', async () => {
            const resp = mockResponse('ok', { status: 200 });
            global.fetch.mock.mockImplementation(resolving(resp));

            await ProclaimFetch.fetch('/api/test', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            });

            const callArgs = global.fetch.mock.calls[0].arguments;
            assert.strictEqual(callArgs[0], '/api/test');
            assert.strictEqual(callArgs[1].method, 'POST');
            assert.strictEqual(callArgs[1].headers['Content-Type'], 'application/json');
        });
    });

    // =========================================================================
    // Timeout
    // =========================================================================

    describe('Timeout', () => {
        /** Mock fetch that rejects when the signal is aborted (like real fetch) */
        function signalAwareMock() {
            return fn((_url, opts) => new Promise((_resolve, reject) => {
                if (opts && opts.signal) {
                    const onAbort = () => {
                        reject(new DOMException('The operation was aborted.', 'AbortError'));
                    };
                    if (opts.signal.aborted) {
                        onAbort();
                        return;
                    }
                    opts.signal.addEventListener('abort', onAbort, { once: true });
                }
            }));
        }

        test('throws TimeoutError when request exceeds timeout', async () => {
            global.fetch = signalAwareMock();

            await assert.rejects(
                ProclaimFetch.fetch('/api/slow', {}, { timeout: 50, retries: 0 }),
                { message: /timed out/i },
            );
        });

        test('timeout error has TimeoutError name', async () => {
            global.fetch = signalAwareMock();

            try {
                await ProclaimFetch.fetch('/api/slow', {}, { timeout: 50, retries: 0 });
                throw new Error('Should have thrown');
            } catch (err) {
                assert.strictEqual(err.name, 'TimeoutError');
            }
        });
    });

    // =========================================================================
    // Retry with backoff
    // =========================================================================

    describe('Retry', () => {
        test('retries on network error', async () => {
            const networkError = new TypeError('Failed to fetch');
            const resp = mockResponse('ok', { status: 200 });

            // Unlike Jest's chained ...Once() calls, node:test's
            // mockImplementationOnce() names the call it applies to.
            global.fetch.mock.mockImplementationOnce(rejecting(networkError), 0);
            global.fetch.mock.mockImplementationOnce(resolving(resp), 1);

            const result = await ProclaimFetch.fetch(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            assert.strictEqual(result, resp);
            assert.strictEqual(global.fetch.mock.callCount(), 2);
        });

        test('does not retry on AbortError', async () => {
            const abortError = new DOMException('Aborted', 'AbortError');

            global.fetch.mock.mockImplementation(rejecting(abortError));

            await assert.rejects(
                ProclaimFetch.fetch('/api/test', {}, { retries: 2, retryDelay: 10 }),
            );

            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });

        test('exhausts retries then throws', async () => {
            const networkError = new TypeError('Failed to fetch');

            global.fetch.mock.mockImplementation(rejecting(networkError));

            await assert.rejects(
                ProclaimFetch.fetch(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10, backoffMultiplier: 1 },
                ),
                { message: /Failed to fetch/ },
            );

            // initial + 2 retries = 3 calls
            assert.strictEqual(global.fetch.mock.callCount(), 3);
        });
    });

    // =========================================================================
    // Abort
    // =========================================================================

    describe('Abort', () => {
        test('respects caller AbortController signal', async () => {
            // Mock that rejects when signal aborts (like real fetch)
            global.fetch = fn((_url, opts) => new Promise((_resolve, reject) => {
                if (opts && opts.signal) {
                    const onAbort = () => {
                        reject(new DOMException('The operation was aborted.', 'AbortError'));
                    };
                    if (opts.signal.aborted) {
                        onAbort();
                        return;
                    }
                    opts.signal.addEventListener('abort', onAbort, { once: true });
                }
            }));

            const controller = new AbortController();

            const fetchPromise = ProclaimFetch.fetch(
                '/api/test', {},
                { timeout: 5000, retries: 0, signal: controller.signal },
            );

            // Abort after a tiny delay
            setTimeout(() => controller.abort(), 10);

            await assert.rejects(fetchPromise);
        });

        test('does not retry when caller aborts', async () => {
            const controller = new AbortController();
            controller.abort();

            global.fetch.mock.mockImplementation(rejecting(new DOMException('Aborted', 'AbortError')));

            await assert.rejects(
                ProclaimFetch.fetch(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10, signal: controller.signal },
                ),
            );

            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });
    });

    // =========================================================================
    // fetchJson() — JSON parsing
    // =========================================================================

    describe('fetchJson()', () => {
        test('returns parsed JSON on success', async () => {
            const payload = { success: true, data: [1, 2, 3] };
            global.fetch.mock.mockImplementation(resolving(mockResponse(
                payload,
                { status: 200, headers: { 'Content-Type': 'application/json' } },
            )));

            const result = await ProclaimFetch.fetchJson('/api/data');

            assert.deepStrictEqual(result, payload);
        });

        test('throws on HTTP 4xx', async () => {
            global.fetch.mock.mockImplementation(resolving(mockResponse('Not Found', {
                status: 404,
                statusText: 'Not Found',
                headers: { 'Content-Type': 'text/plain' },
            })));

            await assert.rejects(
                ProclaimFetch.fetchJson('/api/missing', {}, { retries: 0 }),
                { message: /404/ },
            );
        });
    });

    // =========================================================================
    // HTML Gatekeeper Detection
    // =========================================================================

    describe('Gatekeeper detection', () => {
        test('retries on HTML gatekeeper response', async () => {
            const htmlResp = mockResponse(
                '<html><body>Login required</body></html>',
                { status: 200, headers: { 'Content-Type': 'text/html' } },
            );
            const jsonResp = mockResponse(
                { success: true },
                { status: 200, headers: { 'Content-Type': 'application/json' } },
            );

            global.fetch.mock.mockImplementationOnce(resolving(htmlResp), 0);
            global.fetch.mock.mockImplementationOnce(resolving(jsonResp), 1);

            const result = await ProclaimFetch.fetchJson(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            assert.deepStrictEqual(result, { success: true });
            assert.strictEqual(global.fetch.mock.callCount(), 2);
        });

        test('throws after exhausting retries on gatekeeper', async () => {
            const htmlResp = mockResponse(
                '<html><body>Maintenance</body></html>',
                { status: 200, headers: { 'Content-Type': 'text/html' } },
            );

            global.fetch.mock.mockImplementation(resolving(htmlResp));

            await assert.rejects(
                ProclaimFetch.fetchJson(
                    '/api/test', {},
                    { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
                ),
                { message: /HTML/i },
            );

            // initial + 1 retry = 2 calls
            assert.strictEqual(global.fetch.mock.callCount(), 2);
        });
    });

    // =========================================================================
    // Session Expiry
    // =========================================================================

    describe('Session expiry', () => {
        test('shows Joomla message on 401', async () => {
            global.fetch.mock.mockImplementation(resolving(mockResponse('Unauthorized', {
                status: 401,
                headers: { 'Content-Type': 'text/plain' },
            })));

            await assert.rejects(
                ProclaimFetch.fetchJson('/api/protected', {}, { retries: 2 }),
                { message: /Session expired/ },
            );

            calledWith(
                Joomla.renderMessages,
                match.objectContaining({ error: match.any(Array) }),
            );

            // Session expiry is NOT retried
            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });

        test('shows Joomla message on 403', async () => {
            global.fetch.mock.mockImplementation(resolving(mockResponse('Forbidden', {
                status: 403,
                headers: { 'Content-Type': 'text/plain' },
            })));

            await assert.rejects(
                ProclaimFetch.fetchJson('/api/protected', {}, { retries: 2 }),
                { message: /Session expired/ },
            );

            assert.ok(Joomla.renderMessages.mock.callCount() > 0);
            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });
    });

    // =========================================================================
    // 5xx retry in fetchJson
    // =========================================================================

    describe('5xx retry in fetchJson', () => {
        test('retries on 500 server error then succeeds', async () => {
            const errorResp = mockResponse('Server Error', {
                status: 500,
                statusText: 'Internal Server Error',
                headers: { 'Content-Type': 'text/plain' },
            });
            const okResp = mockResponse(
                { success: true },
                { status: 200, headers: { 'Content-Type': 'application/json' } },
            );

            global.fetch.mock.mockImplementationOnce(resolving(errorResp), 0);
            global.fetch.mock.mockImplementationOnce(resolving(okResp), 1);

            const result = await ProclaimFetch.fetchJson(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            assert.deepStrictEqual(result, { success: true });
            assert.strictEqual(global.fetch.mock.callCount(), 2);
        });

        test('does not retry 4xx errors in fetchJson', async () => {
            global.fetch.mock.mockImplementation(resolving(mockResponse('Bad Request', {
                status: 400,
                statusText: 'Bad Request',
                headers: { 'Content-Type': 'text/plain' },
            })));

            await assert.rejects(
                ProclaimFetch.fetchJson(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10 },
                ),
                { message: /400/ },
            );

            assert.strictEqual(global.fetch.mock.callCount(), 1);
        });
    });

    // =========================================================================
    // Backoff multiplier
    // =========================================================================

    describe('Backoff', () => {
        test('delays increase with backoff multiplier', async () => {
            const networkError = new TypeError('Failed to fetch');
            const resp = mockResponse('ok', { status: 200 });

            global.fetch.mock.mockImplementationOnce(rejecting(networkError), 0);
            global.fetch.mock.mockImplementationOnce(rejecting(networkError), 1);
            global.fetch.mock.mockImplementationOnce(resolving(resp), 2);

            const start = Date.now();
            await ProclaimFetch.fetch(
                '/api/test', {},
                { retries: 2, retryDelay: 50, backoffMultiplier: 2 },
            );
            const elapsed = Date.now() - start;

            // Should have waited ~50ms + ~100ms = ~150ms minimum
            assert.ok(elapsed >= 100);
            assert.strictEqual(global.fetch.mock.callCount(), 3);
        });
    });
});
