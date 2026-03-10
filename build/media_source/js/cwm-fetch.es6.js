/**
 * ProclaimFetch — Shared fetch wrapper with timeout, retry & resilience
 *
 * Provides fetch() with configurable timeout (AbortController-based),
 * automatic retry with exponential backoff, HTML gatekeeper detection,
 * and session expiry handling.
 *
 * Exposed as window.ProclaimFetch for cross-file usage (Rollup IIFE constraint).
 *
 * @package  Proclaim
 * @since    10.1.0
 */
(() => {
    'use strict';

    /** Timeout presets (milliseconds) */
    const ADMIN_TIMEOUT = 30000;
    const FRONTEND_TIMEOUT = 15000;
    const LONG_TIMEOUT = 60000;

    /** Default options */
    const DEFAULTS = {
        timeout: ADMIN_TIMEOUT,
        retries: 2,
        retryDelay: 1000,
        backoffMultiplier: 2,
    };

    /**
     * Determine whether a fetch error or HTTP status is retryable.
     *
     * @param {Error|null} error     The caught error, if any
     * @param {Response|null} response  The HTTP response, if any
     * @returns {boolean}
     */
    function isRetryable(error, response) {
        // Honour explicit retryable flag set by fetchJson
        if (error && error.retryable === false) {
            return false;
        }

        // Never retry user-initiated aborts
        if (error && error.name === 'AbortError') {
            return false;
        }

        // Network errors (TypeError from fetch) are retryable
        if (error && error.name === 'TypeError') {
            return true;
        }

        // Timeout errors (our custom AbortError) are retryable
        if (error && error.name === 'TimeoutError') {
            return true;
        }

        if (response) {
            // 5xx server errors are retryable
            if (response.status >= 500) {
                return true;
            }

            // 4xx client errors are NOT retryable (including 401/403)
            if (response.status >= 400 && response.status < 500) {
                return false;
            }
        }

        // Generic errors (e.g. JSON parse failures) are retryable
        if (error) {
            return true;
        }

        return false;
    }

    /**
     * Check whether a response looks like an HTML gatekeeper page
     * (login wall, maintenance page, WAF challenge) instead of expected JSON.
     *
     * @param {Response} response
     * @returns {boolean}
     */
    function isHtmlGatekeeper(response) {
        const ct = (response.headers.get('content-type') || '').toLowerCase();

        return ct.includes('text/html') && !ct.includes('json');
    }

    /**
     * Check for session expiry (401/403) and show a Joomla message.
     *
     * @param {Response} response
     * @returns {boolean}  True if session has expired
     */
    function isSessionExpired(response) {
        return response.status === 401 || response.status === 403;
    }

    /**
     * Show a session-expired message via Joomla's messaging system.
     */
    function notifySessionExpired() {
        if (typeof Joomla !== 'undefined' && Joomla.renderMessages) {
            const msg = (typeof Joomla.Text !== 'undefined' && Joomla.Text._)
                ? Joomla.Text._('JBS_CMN_SESSION_EXPIRED') || 'Your session has expired. Please reload and log in again.'
                : 'Your session has expired. Please reload and log in again.';

            Joomla.renderMessages({ error: [msg] });
        }
    }

    /**
     * Sleep for the given number of milliseconds.
     *
     * @param {number} ms
     * @returns {Promise<void>}
     */
    function sleep(ms) {
        return new Promise((resolve) => { setTimeout(resolve, ms); });
    }

    /**
     * Create a timeout-aware AbortController that links with an optional caller signal.
     *
     * @param {number} timeout       Timeout in ms
     * @param {AbortSignal|null} callerSignal  Optional caller-provided AbortSignal
     * @returns {{ controller: AbortController, clear: Function, timedOut: Function }}
     */
    function createTimeoutController(timeout, callerSignal) {
        const controller = new AbortController();
        let timer = null;
        let _timedOut = false;

        // Start the timeout
        if (timeout > 0) {
            timer = setTimeout(() => {
                _timedOut = true;
                controller.abort();
            }, timeout);
        }

        // Link caller's abort signal
        if (callerSignal) {
            if (callerSignal.aborted) {
                controller.abort();
            } else {
                callerSignal.addEventListener('abort', () => {
                    controller.abort();
                }, { once: true });
            }
        }

        return {
            controller,
            clear() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
            },
            /** @returns {boolean} Whether the abort was caused by timeout */
            timedOut() {
                return _timedOut;
            },
        };
    }

    /**
     * Create a TimeoutError from a timeout state.
     *
     * @param {number} timeout  The timeout duration in ms
     * @returns {Error}
     */
    function makeTimeoutError(timeout) {
        const err = new Error(`Request timed out after ${timeout}ms`);
        err.name = 'TimeoutError';

        return err;
    }

    /**
     * Fetch with timeout and retry.
     *
     * @param {string} url         Request URL
     * @param {RequestInit} [fetchOpts={}]  Standard fetch options
     * @param {object} [cwmOpts={}]  ProclaimFetch options (timeout, retries, retryDelay, backoffMultiplier, signal)
     * @returns {Promise<Response>}
     */
    async function cwmFetch(url, fetchOpts = {}, cwmOpts = {}) {
        const opts = { ...DEFAULTS, ...cwmOpts };
        const { timeout, retries, retryDelay, backoffMultiplier, signal: callerSignal } = opts;

        let lastError = null;
        let delay = retryDelay;

        for (let attempt = 0; attempt <= retries; attempt += 1) {
            const { controller, clear, timedOut } = createTimeoutController(timeout, callerSignal);

            try {
                const mergedOpts = {
                    ...fetchOpts,
                    signal: controller.signal,
                };

                const response = await fetch(url, mergedOpts);

                clear();

                return response;
            } catch (error) {
                clear();

                // Convert timeout aborts into TimeoutError
                const normError = timedOut() ? makeTimeoutError(timeout) : error;

                // If caller aborted, throw immediately — no retry
                if (callerSignal && callerSignal.aborted) {
                    throw normError;
                }

                if (!isRetryable(normError, null) || attempt >= retries) {
                    throw normError;
                }

                lastError = normError;

                await sleep(delay);
                delay *= backoffMultiplier;
            }
        }

        throw lastError || new Error('Fetch failed');
    }

    /**
     * Fetch JSON with timeout, retry, response validation, gatekeeper detection,
     * and session expiry handling.
     *
     * @param {string} url         Request URL
     * @param {RequestInit} [fetchOpts={}]  Standard fetch options
     * @param {object} [cwmOpts={}]  ProclaimFetch options
     * @returns {Promise<object>}  Parsed JSON
     */
    async function cwmFetchJson(url, fetchOpts = {}, cwmOpts = {}) {
        const opts = { ...DEFAULTS, ...cwmOpts };
        const { timeout, retries, retryDelay, backoffMultiplier, signal: callerSignal } = opts;

        let lastError = null;
        let delay = retryDelay;

        for (let attempt = 0; attempt <= retries; attempt += 1) {
            const { controller, clear, timedOut } = createTimeoutController(timeout, callerSignal);

            try {
                const mergedOpts = {
                    ...fetchOpts,
                    signal: controller.signal,
                };

                const response = await fetch(url, mergedOpts);

                clear();

                // Session expiry — never retry
                if (isSessionExpired(response)) {
                    notifySessionExpired();
                    throw new Error('Session expired (HTTP ' + response.status + ')');
                }

                // HTML gatekeeper — retryable
                if (isHtmlGatekeeper(response)) {
                    const err = new Error('Received HTML instead of JSON (possible login wall or maintenance page)');
                    err.isGatekeeper = true;

                    if (attempt < retries) {
                        lastError = err;
                        await sleep(delay);
                        delay *= backoffMultiplier;

                        continue;
                    }

                    throw err;
                }

                // HTTP error status
                if (!response.ok) {
                    const err = new Error('HTTP ' + response.status + ': ' + response.statusText);
                    err.status = response.status;
                    err.retryable = isRetryable(null, response);

                    if (!err.retryable || attempt >= retries) {
                        throw err;
                    }

                    lastError = err;
                    await sleep(delay);
                    delay *= backoffMultiplier;

                    continue;
                }

                // Parse JSON
                return await response.json();
            } catch (error) {
                clear();

                // Convert timeout aborts into TimeoutError
                const normError = timedOut() ? makeTimeoutError(timeout) : error;

                // If caller aborted, throw immediately
                if (callerSignal && callerSignal.aborted) {
                    throw normError;
                }

                // Session expiry errors are not retryable
                if (normError.message && normError.message.includes('Session expired')) {
                    throw normError;
                }

                if (!isRetryable(normError, null) || attempt >= retries) {
                    throw normError;
                }

                lastError = normError;

                await sleep(delay);
                delay *= backoffMultiplier;
            }
        }

        throw lastError || new Error('Fetch failed');
    }

    // Expose on window
    window.ProclaimFetch = {
        fetch: cwmFetch,
        fetchJson: cwmFetchJson,
        ADMIN_TIMEOUT,
        FRONTEND_TIMEOUT,
        LONG_TIMEOUT,
    };
})();
