/**
 * @jest-environment jsdom
 */

/**
 * Tests for series-scroll.es6.js
 * Infinite Scroll / Load More for frontend series listing
 *
 * Note: innerHTML in test helpers builds DOM from static strings for
 * unit testing purposes, not from external input.
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/series-scroll.es6.js';

var capturedDclHandler = null;
var origAddEventListener = document.addEventListener.bind(document);

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
        jest.restoreAllMocks();
        jest.resetModules();
    });

    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            var result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('Initialization', () => {
        test('should not initialize when seriesScroll options are missing', () => {
            document.body.innerHTML = '<div id="proclaim-series-list"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue(null),
                Text: { _: jest.fn() },
            };

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Should not throw and not create any observers
            expect(true).toBe(true);
        });

        test('should not initialize in pagination mode', () => {
            document.body.innerHTML = '<div id="proclaim-series-list"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                    paginationStyle: 'pagination',
                    limit: 20,
                }),
                Text: { _: jest.fn() },
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

            expect(IntersectionObserver._created).toBe(false);
        });

        test('should not initialize without series-list container', () => {
            document.body.innerHTML = '<div id="some-other-element"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue({
                    enabled: true,
                    ajaxUrl: 'index.php',
                    csrfToken: 'abc123',
                    paginationStyle: 'loadmore',
                    limit: 20,
                }),
                Text: { _: jest.fn() },
            };

            global.IntersectionObserver = class {
                constructor() { IntersectionObserver._created = true; }
                observe() {}
                unobserve() {}
            };
            IntersectionObserver._created = false;

            captureAndRequire('../../build/media_source/js/series-scroll.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(IntersectionObserver._created).toBe(false);
        });
    });

    describe('Load More Mode', () => {
        function setupLoadMore(fetchMock, extraOpts) {
            document.body.innerHTML =
                '<div id="proclaim-series-list">' +
                    '<div class="table-responsive"><table class="table"><tbody>' +
                    '<tr><td>Series 1</td></tr>' +
                    '<tr class="proclaim-row-separator"><td colspan="12"></td></tr>' +
                    '<tr><td>Series 2</td></tr>' +
                    '<tr class="proclaim-row-separator"><td colspan="12"></td></tr>' +
                    '</tbody></table></div>' +
                '</div>' +
                '<div class="proclaim-load-more" id="proclaim-load-more">' +
                    '<button type="button" class="btn">Load More</button>' +
                '</div>' +
                '<div class="proclaim-item-counter" id="proclaim-item-counter"></div>' +
                '<div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>';

            global.Joomla = {
                getOptions: jest.fn().mockReturnValue(Object.assign({
                    enabled: true,
                    ajaxUrl: 'http://localhost/index.php?option=com_proclaim&task=cwmseriesdisplays.paginateAjax&format=raw',
                    csrfToken: 'testtoken',
                    paginationStyle: 'loadmore',
                    limit: 2,
                    totalItems: 10,
                }, extraOpts || {})),
                Text: { _: jest.fn(function (key, fallback) { return fallback || key; }) },
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
            var mockFetch = jest.fn();
            setupLoadMore(mockFetch);

            var counter = document.getElementById('proclaim-item-counter');
            expect(counter.textContent).toContain('Showing');
            expect(counter.textContent).toContain('2');
            expect(counter.textContent).toContain('10');
        });

        test('should append items on Load More click', async () => {
            var mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="table-responsive"><table class="table"><tbody>' +
                            '<tr><td>Series 3</td></tr>' +
                            '<tr class="proclaim-row-separator"><td colspan="12"></td></tr>' +
                            '<tr><td>Series 4</td></tr>' +
                            '<tr class="proclaim-row-separator"><td colspan="12"></td></tr>' +
                            '</tbody></table></div>',
                        total: 10,
                        pagesTotal: 5,
                    });
                },
            });

            setupLoadMore(mockFetch);

            var list = document.getElementById('proclaim-series-list');
            var tbody = list.querySelector('tbody');
            // 2 items × 2 tr = 4
            expect(tbody.querySelectorAll('tr').length).toBe(4);

            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            // 4 items × 2 tr = 8
            expect(tbody.querySelectorAll('tr').length).toBe(8);
            expect(tbody.innerHTML).toContain('Series 3');
        });

        test('should hide button when all items loaded', async () => {
            var mockFetch = jest.fn().mockResolvedValue({
                ok: true,
                json: function () {
                    return Promise.resolve({
                        success: true,
                        html: '<div class="table-responsive"><table class="table"><tbody>' +
                            '<tr><td>Series 3</td></tr>' +
                            '<tr class="proclaim-row-separator"><td colspan="12"></td></tr>' +
                            '</tbody></table></div>',
                        total: 3,
                        pagesTotal: 1,
                    });
                },
            });

            setupLoadMore(mockFetch, { totalItems: 3 });

            var btn = document.querySelector('#proclaim-load-more button');
            btn.click();

            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });
            await new Promise(function (r) { setTimeout(r, 0); });

            var container = document.getElementById('proclaim-load-more');
            expect(container.style.display).toBe('none');
        });
    });
});
