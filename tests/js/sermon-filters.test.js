'use strict';

/**
 * Tests for sermon-filters.es6.js
 * AJAX filtering and searching for frontend sermon listing
 *
 * Note: innerHTML usage in setupModule() is safe — it builds DOM for
 * unit tests from static strings, not from external input.
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, afterEach, mock } = require('node:test');
const { fn, returning, resolving, rejecting, spyOn, restoreAllMocks, resetModules } = require('./helpers/mocks');
const { includes, excludes, calledWith, match } = require('./helpers/assert');

// Track DOMContentLoaded handlers so we can clean them up between tests.
// resetModules() does NOT remove event listeners from `document`.
let capturedDclHandler = null;
const origAddEventListener = document.addEventListener.bind(document);

/**
 * Helper: set up the full DOM + mocks needed for the sermon-filters module.
 * Returns after module initialization is complete.
 *
 * @param {Function} fetchMock   The mocked fetch function
 * @param {Object}   extraOpts  Additional options to merge into sermonFilters config
 */
function setupModule(fetchMock, extraOpts) {
    const paginationStyle = (extraOpts && extraOpts.paginationStyle) || 'pagination';

    let html = '<div id="proclaim-main-content">' +
            '<form id="adminForm">' +
                '<input name="filter_search" type="text" value="" />' +
                // The inline onchange mirrors what site/forms/filter_cwmsermons.xml
                // renders — without it the fixture cannot catch double submits (#1389).
                '<select name="filter_teacher" onchange="this.form.submit();">' +
                    '<option value="">All Teachers</option>' +
                    '<option value="5">Pastor John</option>' +
                '</select>' +
            '</form>';

    if (paginationStyle === 'pagination') {
        html += '<div id="proclaim-pagination-top" class="proclaim-pagination"></div>';
    }

    html += '<div id="proclaim-sermon-list" aria-live="polite">' +
                '<div class="proclaim-listing" data-context="sermons">' +
                '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Item 1</div></div></div>' +
                '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Item 2</div></div></div>' +
                '</div>' +
            '</div>';

    if (paginationStyle === 'pagination') {
        html += '<div id="proclaim-pagination-bottom" class="proclaim-pagination"></div>';
    }

    if (paginationStyle === 'loadmore' || paginationStyle === 'infinite') {
        const hideBtn = (paginationStyle === 'infinite') ? ' style="display:none"' : '';
        html += '<div class="proclaim-load-more" id="proclaim-load-more"' + hideBtn + '>' +
                    '<button type="button" class="btn btn-outline-primary">Load More</button>' +
                '</div>';
    }

    if (paginationStyle !== 'pagination') {
        html += '<div class="proclaim-item-counter" id="proclaim-item-counter"></div>' +
                '<div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>';
    }

    html += '</div>';

    document.body.innerHTML = html;

    global.Joomla = {
        getOptions: fn(returning(Object.assign({
            enabled: true,
            ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmsermons.filterAjax&format=raw',
            // A page cached before the endpoint stopped taking a token still carries one.
            csrfToken: 'testtoken',
            paginationStyle: paginationStyle,
            limit: 2,
            totalItems: 10,
        }, extraOpts || {}))),
        Text: { _: fn(function (key, fallback) {
            return fallback || key;
        }) },
    };

    global.AbortController = class {
        constructor() { this.signal = {}; }
        abort() {}
    };

    // Mock IntersectionObserver
    global.IntersectionObserver = class {
        constructor(callback, options) {
            this._callback = callback;
            this._options = options;
            IntersectionObserver._lastInstance = this;
        }
        observe() {}
        unobserve() {}
        disconnect() {}
        trigger(entries) {
            this._callback(entries);
        }
    };

    spyOn(window.history, 'pushState', function () {});
    spyOn(window.history, 'replaceState', function () {});

    global.fetch = fetchMock;

    Element.prototype.scrollIntoView = fn();

    // Capture the DOMContentLoaded handler so we can remove it in afterEach
    document.addEventListener = function (type, handler, options) {
        if (type === 'DOMContentLoaded') {
            capturedDclHandler = handler;
        }
        return origAddEventListener(type, handler, options);
    };

    // Load and initialize the module
    require('../../build/media_source/js/sermon-filters.es6.js');
    document.dispatchEvent(new Event('DOMContentLoaded'));

    // Restore original addEventListener
    document.addEventListener = origAddEventListener;
}

/** Standard AJAX response mock — uses div structure matching Cwmlisting output */
function mockAjaxResponse(overrides) {
    return {
        ok: true,
        json: function () {
            return Promise.resolve(Object.assign({
                success: true,
                html: '<div class="proclaim-listing" data-context="sermons">' +
                    '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">New Item 1</div></div></div>' +
                    '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">New Item 2</div></div></div>' +
                    '</div>',
                pagination: '',
                pagesCounter: '',
                total: 10,
                pagesTotal: 5,
            }, overrides || {}));
        },
    };
}

describe('sermon-filters.es6.js', () => {
    afterEach(() => {
        // Remove stacked DOMContentLoaded handler from previous test
        if (capturedDclHandler) {
            document.removeEventListener('DOMContentLoaded', capturedDclHandler);
            capturedDclHandler = null;
        }
        delete global.Joomla;
        delete global.IntersectionObserver;
        if (Element.prototype.scrollIntoView) {
            delete Element.prototype.scrollIntoView;
        }
        document.body.innerHTML = '';
        restoreAllMocks();
        resetModules();
    });

    describe('Initialization', () => {
        test('should not initialize when sermonFilters options are missing', () => {
            document.body.innerHTML = '<div id="proclaim-sermon-list"></div>';

            global.Joomla = {
                getOptions: fn(returning(null)),
                Text: { _: fn() },
            };
            spyOn(window.history, 'replaceState', function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
            document.dispatchEvent(new Event('DOMContentLoaded'));

            assert.strictEqual(window.history.replaceState.mock.callCount(), 0);
        });

        test('should not initialize when enabled is false', () => {
            document.body.innerHTML = '<div id="proclaim-sermon-list"></div>';

            global.Joomla = {
                getOptions: fn(returning({
                    enabled: false,
                    ajaxUrl: 'index.php?option=com_proclaim&task=cwmsermons.filterAjax',
                    csrfToken: 'abc123',
                })),
                Text: { _: fn() },
            };
            spyOn(window.history, 'replaceState', function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
            document.dispatchEvent(new Event('DOMContentLoaded'));

            assert.strictEqual(window.history.replaceState.mock.callCount(), 0);
        });

        test('should not initialize without sermon-list container', () => {
            document.body.innerHTML = '<div id="some-other-element"></div>';

            global.Joomla = {
                getOptions: fn(returning({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                })),
                Text: { _: fn() },
            };
            spyOn(window.history, 'replaceState', function () {});

            document.addEventListener = function (type, handler, options) {
                if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
                return origAddEventListener(type, handler, options);
            };
            require('../../build/media_source/js/sermon-filters.es6.js');
            document.addEventListener = origAddEventListener;
            document.dispatchEvent(new Event('DOMContentLoaded'));

            assert.strictEqual(window.history.replaceState.mock.callCount(), 0);
        });

        test('should initialize when all requirements are met', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);

            calledWith(
                window.history.replaceState,
                { proclaimAjax: true },
                '',
                match.any(String),
            );
        });
    });

    describe('Standard Pagination Mode', () => {
        test('should trigger AJAX fetch on form submit', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);
            mockFetch.mock.resetCalls();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            assert.ok(mockFetch.mock.callCount() > 0);

            const [fetchUrl] = mockFetch.mock.calls[0].arguments;
            includes(fetchUrl, 'task=cwmsermons.filterAjax');
        });

        test('should not send a CSRF token, even one left in cached options', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);
            mockFetch.mock.resetCalls();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });

            const [fetchUrl] = mockFetch.mock.calls[0].arguments;
            excludes(fetchUrl, 'testtoken');
        });

        test('should update listing content after successful fetch', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);
            mockFetch.mock.resetCalls();

            const form = document.getElementById('adminForm');
            form.dispatchEvent(new Event('submit', { cancelable: true }));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            const list = document.getElementById('proclaim-sermon-list');
            includes(list.innerHTML, 'New Item 1');
            assert.notStrictEqual(list.querySelector('.proclaim-listing'), null);
        });
    });

    describe('Search debounce', () => {
        test('should debounce search input by 350ms', () => {
            mock.timers.enable({ apis: ['setTimeout', 'setInterval', 'Date'], now: Date.now() });

            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);
            const baselineCount = mockFetch.mock.calls.length;

            const searchInput = document.querySelector('input[name="filter_search"]');

            searchInput.value = 'G';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.value = 'Go';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.value = 'God';
            searchInput.dispatchEvent(new Event('input'));

            mock.timers.tick(200);
            assert.strictEqual(mockFetch.mock.calls.length, baselineCount);

            mock.timers.tick(200);
            assert.ok(mockFetch.mock.calls.length > baselineCount);

            mock.timers.reset();
        });
    });

    describe('Filter change', () => {
        // Regression guard for #1389: the form XML puts onchange="this.form.submit();"
        // on every select for the non-AJAX templates. Left in place it fires alongside
        // the module's own change listener, so a single change issued two requests —
        // the second aborting the first, with the server having run both queries.
        test('should issue exactly one request per dropdown change', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch);
            mockFetch.mock.resetCalls();

            const select = document.querySelector('select[name="filter_teacher"]');

            assert.strictEqual(select.getAttribute('onchange'), null);

            select.value = '5';
            select.dispatchEvent(new Event('change'));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            assert.strictEqual(mockFetch.mock.callCount(), 1);
        });
    });

    describe('Load More Mode', () => {
        test('should show initial counter on page load', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            const counter = document.getElementById('proclaim-item-counter');
            includes(counter.textContent, 'Showing');
            includes(counter.textContent, '2');
            includes(counter.textContent, '10');
        });

        test('should show Load More button when in loadmore mode', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            const btn = document.getElementById('proclaim-load-more');
            assert.notStrictEqual(btn, null);
            assert.notStrictEqual(btn.querySelector('button'), null);
        });

        test('should append items on Load More click', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            const list = document.getElementById('proclaim-sermon-list');
            const listing = list.querySelector('.proclaim-listing');
            // 2 proclaim-item divs
            assert.strictEqual(listing.querySelectorAll(':scope > .proclaim-item').length, 2);

            mockFetch.mock.resetCalls();

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // Items appended: 2 original + 2 new = 4 proclaim-item divs
            assert.strictEqual(listing.querySelectorAll(':scope > .proclaim-item').length, 4);
        });

        test('should update counter after load', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse({ total: 10, pagesTotal: 5 })));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });
            mockFetch.mock.resetCalls();

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            const counter = document.getElementById('proclaim-item-counter');
            includes(counter.textContent, 'Showing');
        });

        test('should hide Load More when all items loaded', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse({ total: 2, pagesTotal: 1 })));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });
            mockFetch.mock.resetCalls();

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            const container = document.getElementById('proclaim-load-more');
            assert.strictEqual(container.style.display, 'none');

            const counter = document.getElementById('proclaim-item-counter');
            includes(counter.textContent, 'All items loaded');
        });

        test('should reset to replace mode on filter change', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });

            // First, do a load more to accumulate
            mockFetch.mock.resetCalls();
            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            const list = document.getElementById('proclaim-sermon-list');
            const listing = list.querySelector('.proclaim-listing');
            // 4 proclaim-item divs after append
            assert.strictEqual(listing.querySelectorAll(':scope > .proclaim-item').length, 4);

            // Now change a filter — should replace, not append
            mockFetch.mock.resetCalls();
            const select = document.querySelector('select[name="filter_teacher"]');
            select.value = '5';
            select.dispatchEvent(new Event('change'));

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // After filter change, list should be replaced (fresh table with 2 items)
            includes(list.innerHTML, 'New Item 1');
        });
    });

    describe('Infinite Scroll Mode', () => {
        test('should create IntersectionObserver when in infinite mode', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            assert.notStrictEqual(IntersectionObserver._lastInstance, undefined);
        });

        test('should not show pagination containers in infinite mode', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            assert.strictEqual(document.getElementById('proclaim-pagination-top'), null);
            assert.strictEqual(document.getElementById('proclaim-pagination-bottom'), null);
        });

        test('should have sentinel element for intersection', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            const sentinel = document.getElementById('proclaim-scroll-sentinel');
            assert.notStrictEqual(sentinel, null);
        });

        test('should show counter element', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            const counter = document.getElementById('proclaim-item-counter');
            assert.notStrictEqual(counter, null);
        });

        test('should have Load More button hidden initially in infinite mode', () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'infinite' });

            const container = document.getElementById('proclaim-load-more');
            assert.notStrictEqual(container, null);
            assert.strictEqual(container.style.display, 'none');
        });

        test('should include credentials in fetch requests', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse()));
            setupModule(mockFetch, { paginationStyle: 'loadmore' });
            mockFetch.mock.resetCalls();

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });

            const [, fetchOpts] = mockFetch.mock.calls[0].arguments;
            assert.strictEqual(fetchOpts.credentials, 'same-origin');
            assert.strictEqual(fetchOpts.headers['Accept'], 'application/json');
        });
    });

    describe('Infinite Scroll Threshold', () => {
        test('should pause auto-scroll after threshold pages and show Load More button', async () => {
            // threshold=2 means after 2 auto-loaded pages, pause
            const mockFetch = fn(resolving(mockAjaxResponse({ total: 20, pagesTotal: 10 })));
            setupModule(mockFetch, { paginationStyle: 'infinite', scrollThreshold: 2 });

            const observer = IntersectionObserver._lastInstance;
            const loadMoreContainer = document.getElementById('proclaim-load-more');

            // Initially hidden
            assert.strictEqual(loadMoreContainer.style.display, 'none');

            // Simulate first scroll trigger (auto-load page 1)
            mockFetch.mock.resetCalls();
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            // Still hidden after 1 page
            assert.strictEqual(loadMoreContainer.style.display, 'none');

            // Simulate second scroll trigger (auto-load page 2 = threshold)
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            // Now the button should be visible (threshold reached)
            assert.strictEqual(loadMoreContainer.style.display, '');
        });

        test('should resume auto-scroll after manual Load More click', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse({ total: 20, pagesTotal: 10 })));
            setupModule(mockFetch, { paginationStyle: 'infinite', scrollThreshold: 1 });

            const observer = IntersectionObserver._lastInstance;
            const loadMoreContainer = document.getElementById('proclaim-load-more');

            // Trigger one auto-load to reach threshold
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            assert.strictEqual(loadMoreContainer.style.display, '');

            // Click Load More — should resume
            const btn = loadMoreContainer.querySelector('button');
            btn.click();
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // Button should be hidden again (auto-scroll resumed)
            assert.strictEqual(loadMoreContainer.style.display, 'none');
        });

        test('should not pause when threshold is 0 (unlimited)', async () => {
            const mockFetch = fn(resolving(mockAjaxResponse({ total: 20, pagesTotal: 10 })));
            setupModule(mockFetch, { paginationStyle: 'infinite', scrollThreshold: 0 });

            const observer = IntersectionObserver._lastInstance;
            const loadMoreContainer = document.getElementById('proclaim-load-more');

            // Auto-load many pages — should never show button
            for (let i = 0; i < 5; i++) {
                observer.trigger([{ isIntersecting: true }]);
                await new Promise(function (r) { setTimeout(r, 0); });
                await new Promise(function (r) { setTimeout(r, 0); });
                await new Promise(function (r) { setTimeout(r, 0); });
            }

            assert.strictEqual(loadMoreContainer.style.display, 'none');
        });
    });

    describe('Error fallback', () => {
        test('should show inline error with retry button on fetch error', { timeout: 10000 }, async () => {
            spyOn(console, 'warn', function () {});

            // All fetch calls fail (no fetch happens during init)
            const mockFetch = fn(rejecting(new TypeError('Network error')));

            setupModule(mockFetch);

            const form = document.getElementById('adminForm');

            form.dispatchEvent(new Event('submit', { cancelable: true }));

            for (let i = 0; i < 10; i++) {
                await new Promise(function (r) { setTimeout(r, 10); });
            }

            const errorDiv = document.querySelector('.proclaim-ajax-error');
            assert.notStrictEqual(errorDiv, null);
            assert.notStrictEqual(errorDiv.querySelector('.proclaim-retry-btn'), null);
        });
    });
});
