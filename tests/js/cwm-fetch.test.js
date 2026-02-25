/**
 * @jest-environment jsdom
 */

/**
 * Jest tests for cwm-fetch.es6.js
 *
 * Tests timeout, retry, backoff, abort, gatekeeper detection, and session expiry.
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */

// Mock Joomla global
global.Joomla = {
    renderMessages: jest.fn(),
    Text: { _: jest.fn((key) => key) },
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
        jest.clearAllMocks();
        global.fetch = jest.fn();
    });

    afterEach(() => {
        delete global.fetch;
    });

    // =========================================================================
    // Presets
    // =========================================================================

    describe('Presets', () => {
        test('exports timeout presets', () => {
            expect(ProclaimFetch.ADMIN_TIMEOUT).toBe(30000);
            expect(ProclaimFetch.FRONTEND_TIMEOUT).toBe(15000);
            expect(ProclaimFetch.LONG_TIMEOUT).toBe(60000);
        });

        test('exports fetch and fetchJson methods', () => {
            expect(typeof ProclaimFetch.fetch).toBe('function');
            expect(typeof ProclaimFetch.fetchJson).toBe('function');
        });
    });

    // =========================================================================
    // fetch() — basic behavior
    // =========================================================================

    describe('fetch()', () => {
        test('returns Response on success', async () => {
            const resp = mockResponse('ok', { status: 200 });
            global.fetch.mockResolvedValue(resp);

            const result = await ProclaimFetch.fetch('/api/test');

            expect(result).toBe(resp);
            expect(global.fetch).toHaveBeenCalledTimes(1);
        });

        test('passes fetch options through', async () => {
            const resp = mockResponse('ok', { status: 200 });
            global.fetch.mockResolvedValue(resp);

            await ProclaimFetch.fetch('/api/test', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            });

            const callArgs = global.fetch.mock.calls[0];
            expect(callArgs[0]).toBe('/api/test');
            expect(callArgs[1].method).toBe('POST');
            expect(callArgs[1].headers['Content-Type']).toBe('application/json');
        });
    });

    // =========================================================================
    // Timeout
    // =========================================================================

    describe('Timeout', () => {
        /** Mock fetch that rejects when the signal is aborted (like real fetch) */
        function signalAwareMock() {
            return jest.fn((_url, opts) => new Promise((_resolve, reject) => {
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

            await expect(
                ProclaimFetch.fetch('/api/slow', {}, { timeout: 50, retries: 0 }),
            ).rejects.toThrow(/timed out/i);
        });

        test('timeout error has TimeoutError name', async () => {
            global.fetch = signalAwareMock();

            try {
                await ProclaimFetch.fetch('/api/slow', {}, { timeout: 50, retries: 0 });
                throw new Error('Should have thrown');
            } catch (err) {
                expect(err.name).toBe('TimeoutError');
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

            global.fetch
                .mockRejectedValueOnce(networkError)
                .mockResolvedValueOnce(resp);

            const result = await ProclaimFetch.fetch(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            expect(result).toBe(resp);
            expect(global.fetch).toHaveBeenCalledTimes(2);
        });

        test('does not retry on AbortError', async () => {
            const abortError = new DOMException('Aborted', 'AbortError');

            global.fetch.mockRejectedValue(abortError);

            await expect(
                ProclaimFetch.fetch('/api/test', {}, { retries: 2, retryDelay: 10 }),
            ).rejects.toThrow();

            expect(global.fetch).toHaveBeenCalledTimes(1);
        });

        test('exhausts retries then throws', async () => {
            const networkError = new TypeError('Failed to fetch');

            global.fetch.mockRejectedValue(networkError);

            await expect(
                ProclaimFetch.fetch(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10, backoffMultiplier: 1 },
                ),
            ).rejects.toThrow('Failed to fetch');

            // initial + 2 retries = 3 calls
            expect(global.fetch).toHaveBeenCalledTimes(3);
        });
    });

    // =========================================================================
    // Abort
    // =========================================================================

    describe('Abort', () => {
        test('respects caller AbortController signal', async () => {
            // Mock that rejects when signal aborts (like real fetch)
            global.fetch = jest.fn((_url, opts) => new Promise((_resolve, reject) => {
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

            await expect(fetchPromise).rejects.toThrow();
        });

        test('does not retry when caller aborts', async () => {
            const controller = new AbortController();
            controller.abort();

            global.fetch.mockRejectedValue(new DOMException('Aborted', 'AbortError'));

            await expect(
                ProclaimFetch.fetch(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10, signal: controller.signal },
                ),
            ).rejects.toThrow();

            expect(global.fetch).toHaveBeenCalledTimes(1);
        });
    });

    // =========================================================================
    // fetchJson() — JSON parsing
    // =========================================================================

    describe('fetchJson()', () => {
        test('returns parsed JSON on success', async () => {
            const payload = { success: true, data: [1, 2, 3] };
            global.fetch.mockResolvedValue(mockResponse(
                payload,
                { status: 200, headers: { 'Content-Type': 'application/json' } },
            ));

            const result = await ProclaimFetch.fetchJson('/api/data');

            expect(result).toEqual(payload);
        });

        test('throws on HTTP 4xx', async () => {
            global.fetch.mockResolvedValue(mockResponse('Not Found', {
                status: 404,
                statusText: 'Not Found',
                headers: { 'Content-Type': 'text/plain' },
            }));

            await expect(
                ProclaimFetch.fetchJson('/api/missing', {}, { retries: 0 }),
            ).rejects.toThrow(/404/);
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

            global.fetch
                .mockResolvedValueOnce(htmlResp)
                .mockResolvedValueOnce(jsonResp);

            const result = await ProclaimFetch.fetchJson(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            expect(result).toEqual({ success: true });
            expect(global.fetch).toHaveBeenCalledTimes(2);
        });

        test('throws after exhausting retries on gatekeeper', async () => {
            const htmlResp = mockResponse(
                '<html><body>Maintenance</body></html>',
                { status: 200, headers: { 'Content-Type': 'text/html' } },
            );

            global.fetch.mockResolvedValue(htmlResp);

            await expect(
                ProclaimFetch.fetchJson(
                    '/api/test', {},
                    { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
                ),
            ).rejects.toThrow(/HTML/i);

            // initial + 1 retry = 2 calls
            expect(global.fetch).toHaveBeenCalledTimes(2);
        });
    });

    // =========================================================================
    // Session Expiry
    // =========================================================================

    describe('Session expiry', () => {
        test('shows Joomla message on 401', async () => {
            global.fetch.mockResolvedValue(mockResponse('Unauthorized', {
                status: 401,
                headers: { 'Content-Type': 'text/plain' },
            }));

            await expect(
                ProclaimFetch.fetchJson('/api/protected', {}, { retries: 2 }),
            ).rejects.toThrow(/Session expired/);

            expect(Joomla.renderMessages).toHaveBeenCalledWith(
                expect.objectContaining({ error: expect.any(Array) }),
            );

            // Session expiry is NOT retried
            expect(global.fetch).toHaveBeenCalledTimes(1);
        });

        test('shows Joomla message on 403', async () => {
            global.fetch.mockResolvedValue(mockResponse('Forbidden', {
                status: 403,
                headers: { 'Content-Type': 'text/plain' },
            }));

            await expect(
                ProclaimFetch.fetchJson('/api/protected', {}, { retries: 2 }),
            ).rejects.toThrow(/Session expired/);

            expect(Joomla.renderMessages).toHaveBeenCalled();
            expect(global.fetch).toHaveBeenCalledTimes(1);
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

            global.fetch
                .mockResolvedValueOnce(errorResp)
                .mockResolvedValueOnce(okResp);

            const result = await ProclaimFetch.fetchJson(
                '/api/test', {},
                { retries: 1, retryDelay: 10, backoffMultiplier: 1 },
            );

            expect(result).toEqual({ success: true });
            expect(global.fetch).toHaveBeenCalledTimes(2);
        });

        test('does not retry 4xx errors in fetchJson', async () => {
            global.fetch.mockResolvedValue(mockResponse('Bad Request', {
                status: 400,
                statusText: 'Bad Request',
                headers: { 'Content-Type': 'text/plain' },
            }));

            await expect(
                ProclaimFetch.fetchJson(
                    '/api/test', {},
                    { retries: 2, retryDelay: 10 },
                ),
            ).rejects.toThrow(/400/);

            expect(global.fetch).toHaveBeenCalledTimes(1);
        });
    });

    // =========================================================================
    // Backoff multiplier
    // =========================================================================

    describe('Backoff', () => {
        test('delays increase with backoff multiplier', async () => {
            const networkError = new TypeError('Failed to fetch');
            const resp = mockResponse('ok', { status: 200 });

            global.fetch
                .mockRejectedValueOnce(networkError)
                .mockRejectedValueOnce(networkError)
                .mockResolvedValueOnce(resp);

            const start = Date.now();
            await ProclaimFetch.fetch(
                '/api/test', {},
                { retries: 2, retryDelay: 50, backoffMultiplier: 2 },
            );
            const elapsed = Date.now() - start;

            // Should have waited ~50ms + ~100ms = ~150ms minimum
            expect(elapsed).toBeGreaterThanOrEqual(100);
            expect(global.fetch).toHaveBeenCalledTimes(3);
        });
    });
});
