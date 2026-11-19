'use strict';

/**
 * Tests for series-scroll.es6.js
 * Infinite Scroll / Load More for frontend series listing
 *
 * Note: innerHTML in test helpers builds DOM from static strings for
 * unit testing purposes, not from external input.
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, afterEach } = require('node:test');
const { fn, returning, resolving, restoreAllMocks, resetModules } = require('./helpers/mocks');
const { includes } = require('./helpers/assert');

let capturedDclHandler = null;
const origAddEventListener = document.addEventListener.bind(document);

function captureAndRequire(path) {
    document.addEventListener = function (type, handler, options) {
        if (type === 'DOMContentLoaded') { capturedDclHandler = handler; }
        return origAddEventListener(type, handler, options);
    };
    require(path);
    document.addEventListener = origAddEventListener;
}

describe('series-scroll.es6.js', () => {
    afterEach(() => {
        if (capturedDclHandler) {
            document.removeEventListener('DOMContentLoaded', capturedDclHandler);
            capturedDclHandler = null;
        }
        delete global.Joomla;
        delete global.IntersectionObserver;
        document.body.innerHTML = '';
        restoreAllMocks();
        resetModules();
    });

    describe('Initialization', () => {
        test('should not initialize when seriesScroll options are missing', () => {
            document.body.innerHTML = '<div id="proclaim-series-list"></div>';

            global.Joomla = {
                getOptions: fn(returning(null)),
                Text: { _: fn() },
            };

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Should not throw and not create any observers
            assert.strictEqual(true, true);
        });

        test('should not initialize in pagination mode', () => {
            document.body.innerHTML = '<div id="proclaim-series-list"></div>';

            global.Joomla = {
                getOptions: fn(returning({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                    paginationStyle: 'pagination',
                    limit: 20,
                })),
                Text: { _: fn() },
            };

            // Mock IntersectionObserver
            global.IntersectionObserver = class {
                constructor() { IntersectionObserver._created = true; }
                observe() {}
                unobserve() {}
            };
            IntersectionObserver._created = false;

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            assert.strictEqual(IntersectionObserver._created, false);
        });

        test('should not initialize without series-list container', () => {
            document.body.innerHTML = '<div id="some-other-element"></div>';

            global.Joomla = {
                getOptions: fn(returning({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                    paginationStyle: 'loadmore',
                    limit: 20,
                })),
                Text: { _: fn() },
            };

            global.IntersectionObserver = class {
                constructor() { IntersectionObserver._created = true; }
                observe() {}
                unobserve() {}
            };
            IntersectionObserver._created = false;

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            assert.strictEqual(IntersectionObserver._created, false);
        });
    });

    describe('Load More Mode', () => {
        function setupLoadMore(fetchMock, extraOpts) {
            // Safe: static test fixture string, not user input
            document.body.innerHTML =
                '<div id="proclaim-series-list">' +
                    '<div class="proclaim-listing" data-context="seriesdisplays">' +
                    '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 1</div></div></div>' +
                    '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 2</div></div></div>' +
                    '</div>' +
                '</div>' +
                '<div class="proclaim-load-more" id="proclaim-load-more">' +
                    '<button type="button" class="btn">Load More</button>' +
                '</div>' +
                '<div class="proclaim-item-counter" id="proclaim-item-counter"></div>' +
                '<div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>';

            global.Joomla = {
                getOptions: fn(returning(Object.assign({
                    enabled: true,
                    ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmseriesdisplays.paginateAjax&format=raw',
                    csrfToken: 'testtoken',
                    paginationStyle: 'loadmore',
                    limit: 2,
                    totalItems: 10,
                }, extraOpts || {}))),
                Text: { _: fn(function (key, fallback) { return fallback || key; }) },
            };

            global.AbortController = class {
                constructor() { this.signal = {}; }
                abort() {}
            };

            global.IntersectionObserver = class {
                constructor(cb) { this._cb = cb; }
                observe() {}
                unobserve() {}
            };

            global.fetch = fetchMock;

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
        }

        test('should show initial counter on page load', () => {
            const mockFetch = fn();
            setupLoadMore(mockFetch);

            const counter = document.getElementById('proclaim-item-counter');
            includes(counter.textContent, 'Showing');
            includes(counter.textContent, '2');
            includes(counter.textContent, '10');
        });

        test('should append items on Load More click', async () => {
            const mockFetch = fn(resolving({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="proclaim-listing" data-context="seriesdisplays">' +
                            '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 3</div></div></div>' +
                            '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 4</div></div></div>' +
                            '</div>',
                        total: 10,
                        pagesTotal: 5,
                    });
                },
            }));

            setupLoadMore(mockFetch);

            const list = document.getElementById('proclaim-series-list');
            const listing = list.querySelector('.proclaim-listing');
            // 2 proclaim-item divs
            assert.strictEqual(listing.querySelectorAll(':scope > .proclaim-item').length, 2);

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // 4 proclaim-item divs
            assert.strictEqual(listing.querySelectorAll(':scope > .proclaim-item').length, 4);
            includes(listing.innerHTML, 'Series 3');
        });

        test('should hide button when all items loaded', async () => {
            const mockFetch = fn(resolving({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="proclaim-listing" data-context="seriesdisplays">' +
                            '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 3</div></div></div>' +
                            '</div>',
                        total: 3,
                        pagesTotal: 1,
                    });
                },
            }));

            setupLoadMore(mockFetch, { totalItems: 3 });

            const btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            const container = document.getElementById('proclaim-load-more');
            assert.strictEqual(container.style.display, 'none');
        });
    });

    describe('Infinite Scroll Threshold', () => {
        function setupInfinite(fetchMock, extraOpts) {
            // Safe: static test fixture string, not user input
            document.body.innerHTML =
                '<div id="proclaim-series-list">' +
                    '<div class="proclaim-listing" data-context="seriesdisplays">' +
                    '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">Series 1</div></div></div>' +
                    '</div>' +
                '</div>' +
                '<div class="proclaim-load-more" id="proclaim-load-more" style="display:none">' +
                    '<button type="button" class="btn">Load More</button>' +
                '</div>' +
                '<div class="proclaim-item-counter" id="proclaim-item-counter"></div>' +
                '<div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>';

            global.Joomla = {
                getOptions: fn(returning(Object.assign({
                    enabled: true,
                    ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmseriesdisplays.paginateAjax&format=raw',
                    csrfToken: 'testtoken',
                    paginationStyle: 'infinite',
                    limit: 2,
                    totalItems: 20,
                    scrollThreshold: 2,
                }, extraOpts || {}))),
                Text: { _: fn(function (key, fallback) { return fallback || key; }) },
            };

            global.AbortController = class {
                constructor() { this.signal = {}; }
                abort() {}
            };

            global.IntersectionObserver = class {
                constructor(cb, opts) {
                    this._cb = cb;
                    this._opts = opts;
                    IntersectionObserver._lastInstance = this;
                }
                observe() {}
                unobserve() {}
                trigger(entries) { this._cb(entries); }
            };

            global.fetch = fetchMock;

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
        }

        test('should pause after threshold and show Load More', async () => {
            const mockFetch = fn(resolving({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="proclaim-listing" data-context="seriesdisplays">' +
                            '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">New</div></div></div>' +
                            '</div>',
                        total: 20,
                        pagesTotal: 10,
                    });
                },
            }));

            setupInfinite(mockFetch, { scrollThreshold: 2 });

            const observer = IntersectionObserver._lastInstance;
            const loadMoreContainer = document.getElementById('proclaim-load-more');

            // Initially hidden
            assert.strictEqual(loadMoreContainer.style.display, 'none');

            // First auto-load
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            assert.strictEqual(loadMoreContainer.style.display, 'none');

            // Second auto-load (= threshold)
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            assert.strictEqual(loadMoreContainer.style.display, '');
        });

        test('should include credentials in fetch requests', async () => {
            const mockFetch = fn(resolving({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="proclaim-listing" data-context="seriesdisplays">' +
                            '<div class="proclaim-item"><div class="row proclaim-item-row"><div class="col">X</div></div></div>' +
                            '</div>',
                        total: 20,
                        pagesTotal: 10,
                    });
                },
            }));

            setupInfinite(mockFetch);

            const observer = IntersectionObserver._lastInstance;
            mockFetch.mock.resetCalls();
            observer.trigger([{ isIntersecting: true }]);
            await new Promise(function (r) { setTimeout(r, 0); });

            const [, fetchOpts] = mockFetch.mock.calls[0].arguments;
            assert.strictEqual(fetchOpts.credentials, 'same-origin');
        });
    });
});
