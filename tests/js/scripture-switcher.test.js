/**
 * @jest-environment jsdom
 */

/**
 * Tests for scripture-switcher.es6.js
 * Bible version switching with cache, rollback, and diagnostics
 *
 * Note: innerHTML usage in setupDOM() is safe — it builds DOM for
 * unit tests from static strings, not from external input.
 */

const SOURCE_FILE = 'build/media_source/js/scripture-switcher.es6.js';

// Track DOMContentLoaded handlers for cleanup
let capturedDclHandler = null;
const origAddEventListener = document.addEventListener.bind(document);

/**
 * Build the DOM fixtures for the searchable scripture switcher.
 * Includes data-current-version attribute per the PHP changes.
 * All strings are static test data, not external input.
 *
 * @param {Object} opts  Configuration options
 * @returns {void}
 */
function setupDOM(opts = {}) {
    const currentVersion = opts.currentVersion || 'kjv';
    const bodyText = opts.bodyText || '<p><sup>1</sup> In the beginning God created...</p>';
    const copyrightText = opts.copyrightText || 'Public Domain';

    // Build the scripture container with switcher inside .scripture-text
    // All content is static test fixture data
    const html = '<div class="scripture-container scripture-visible">'
        + '<div class="scripture-text">'
        + '<div class="scripture-body">' + bodyText + '</div>'
        + '<div class="scripture-copyright">' + copyrightText + '</div>'
        + '<div class="scripture-version-switcher scripture-searchable-switcher" '
        + 'data-reference="Genesis+1:1" data-message-id="42" '
        + 'data-current-version="' + currentVersion + '">'
        + '<select class="scripture-version-select" data-reference="Genesis+1:1" '
        + 'data-message-id="42" style="display:none;">'
        + '<optgroup label="English">'
        + '<option value="kjv"' + (currentVersion === 'kjv' ? ' selected' : '') + '>King James Version</option>'
        + '<option value="esv"' + (currentVersion === 'esv' ? ' selected' : '') + '>English Standard Version</option>'
        + '<option value="niv"' + (currentVersion === 'niv' ? ' selected' : '') + '>New International Version</option>'
        + '</optgroup>'
        + '</select>'
        + '<div class="scripture-dropdown">'
        + '<button type="button" class="scripture-dropdown-toggle" aria-haspopup="listbox" aria-expanded="false">'
        + '<span class="scripture-dropdown-text">King James Version</span>'
        + '</button>'
        + '<div class="scripture-dropdown-menu" role="listbox" style="display:none;">'
        + '<div class="scripture-dropdown-search">'
        + '<input type="text" class="form-control form-control-sm" placeholder="Search...">'
        + '</div>'
        + '<div class="scripture-dropdown-items">'
        + '<div class="scripture-dropdown-group" data-lang="en">'
        + '<div class="scripture-dropdown-header">English</div>'
        + '<div class="scripture-dropdown-item active" role="option" data-value="kjv" data-lang="en">King James Version</div>'
        + '<div class="scripture-dropdown-item" role="option" data-value="esv" data-lang="en">English Standard Version</div>'
        + '<div class="scripture-dropdown-item" role="option" data-value="niv" data-lang="en">New International Version</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';

    document.body.innerHTML = html;
}

/**
 * Set up Joomla globals and load the module.
 *
 * @param {Function} fetchMock  The mocked fetch function
 * @param {Object}   opts       DOM setup options
 */
function setupModule(fetchMock, opts) {
    setupDOM(opts);

    // Set up Joomla globals
    global.Joomla = {
        getOptions: function (key) {
            if (key === 'com_proclaim.scripture') {
                return { ajaxUrl: '/index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw' };
            }
            if (key === 'csrf.token') {
                return 'abc123';
            }
            if (key === 'system.debug') {
                return false;
            }
            return null;
        },
        Text: {
            _: function (key) {
                return key; // Return key itself (simulates unregistered keys)
            }
        }
    };

    global.fetch = fetchMock;

    // Intercept DOMContentLoaded
    document.addEventListener = function (type, handler) {
        if (type === 'DOMContentLoaded') {
            capturedDclHandler = handler;
        } else {
            origAddEventListener(type, handler);
        }
    };

    jest.resetModules();
    require('../../' + SOURCE_FILE);

    // Fire DOMContentLoaded
    if (capturedDclHandler) {
        capturedDclHandler();
    }

    document.addEventListener = origAddEventListener;
}

afterEach(function () {
    capturedDclHandler = null;
    document.addEventListener = origAddEventListener;
    document.body.innerHTML = '';
    delete global.Joomla;
    delete global.fetch;
    jest.restoreAllMocks();
});

// --- Initialization tests ---
describe('scripture-switcher initialization', function () {
    test('sets _currentVersion from data-current-version', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch, { currentVersion: 'kjv' });

        const switcher = document.querySelector('.scripture-searchable-switcher');
        expect(switcher._currentVersion).toBe('kjv');
    });

    test('caches initial content at init time', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch, {
            currentVersion: 'kjv',
            bodyText: '<p>Initial KJV text</p>'
        });

        // The cache is internal (WeakMap), but we can verify by clicking KJV
        // and confirming no fetch is made
        const kjvItem = document.querySelector('[data-value="kjv"]');
        kjvItem.click();

        expect(mockFetch).not.toHaveBeenCalled();
    });
});

// --- Same-version short-circuit ---
describe('same-version short-circuit', function () {
    test('clicking the currently displayed version does not trigger fetch', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch, { currentVersion: 'kjv' });

        const kjvItem = document.querySelector('[data-value="kjv"]');
        kjvItem.click();

        expect(mockFetch).not.toHaveBeenCalled();
    });

    test('clicking same version closes the menu', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch, { currentVersion: 'kjv' });

        // Open menu first
        const toggle = document.querySelector('.scripture-dropdown-toggle');
        toggle.click();
        expect(document.querySelector('.scripture-dropdown-menu').style.display).toBe('flex');

        // Click KJV (already active)
        const kjvItem = document.querySelector('[data-value="kjv"]');
        kjvItem.click();

        expect(document.querySelector('.scripture-dropdown-menu').style.display).toBe('none');
        expect(mockFetch).not.toHaveBeenCalled();
    });
});

// --- Successful fetch commits UI ---
describe('successful fetch', function () {
    test('updates body, dropdown text, and select only after success', async function () {
        const mockFetch = jest.fn().mockResolvedValue({
            ok: true,
            text: function () {
                return Promise.resolve(JSON.stringify({
                    success: true,
                    text: '<p>ESV passage text</p>',
                    copyright: 'ESV copyright notice',
                    translation: 'esv',
                    fallback: false,
                    provider: 'local',
                    requested: 'esv'
                }));
            }
        });
        setupModule(mockFetch, { currentVersion: 'kjv' });

        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();

        // Wait for async fetch to complete
        await new Promise(function (r) { setTimeout(r, 50); });

        const body = document.querySelector('.scripture-body');
        const toggleText = document.querySelector('.scripture-dropdown-text');
        const select = document.querySelector('.scripture-version-select');

        expect(body.textContent).toContain('ESV passage text');
        expect(toggleText.textContent).toBe('English Standard Version');
        expect(select.value).toBe('esv');
    });

    test('caches successful fetch for instant restore', async function () {
        let callCount = 0;
        const mockFetch = jest.fn().mockImplementation(function () {
            callCount += 1;
            return Promise.resolve({
                ok: true,
                text: function () {
                    return Promise.resolve(JSON.stringify({
                        success: true,
                        text: '<p>ESV passage text</p>',
                        copyright: 'ESV copyright',
                        translation: 'esv',
                        fallback: false,
                        provider: 'local',
                        requested: 'esv'
                    }));
                }
            });
        });
        setupModule(mockFetch, { currentVersion: 'kjv' });

        // First: click ESV
        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();
        await new Promise(function (r) { setTimeout(r, 50); });
        expect(callCount).toBe(1);

        // Switch back to KJV (cached from init)
        const kjvItem = document.querySelector('[data-value="kjv"]');
        kjvItem.click();
        await new Promise(function (r) { setTimeout(r, 50); });
        // No additional fetch because KJV was cached at init
        expect(callCount).toBe(1);

        // Switch to ESV again (cached from first fetch)
        esvItem.click();
        await new Promise(function (r) { setTimeout(r, 50); });
        // Still no additional fetch — served from cache
        expect(callCount).toBe(1);

        // Verify ESV content is displayed
        const body = document.querySelector('.scripture-body');
        expect(body.textContent).toContain('ESV passage text');
    });
});

// --- Failure rollback ---
describe('failure rollback', function () {
    test('restores original content and shows error banner on failure', async function () {
        const mockFetch = jest.fn().mockResolvedValue({
            ok: true,
            text: function () {
                return Promise.resolve(JSON.stringify({
                    success: false,
                    retryable: false,
                    message: 'No passage text returned',
                    provider: 'api_bible',
                    requested: 'esv'
                }));
            }
        });
        setupModule(mockFetch, {
            currentVersion: 'kjv',
            bodyText: '<p>Original KJV text</p>',
            copyrightText: 'Public Domain'
        });

        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();

        // Wait for fetch + retries (non-retryable so should be fast)
        await new Promise(function (r) { setTimeout(r, 100); });

        // Original content restored
        const body = document.querySelector('.scripture-body');
        expect(body.textContent).toContain('Original KJV text');

        // Dropdown text NOT changed (was never committed)
        const toggleText = document.querySelector('.scripture-dropdown-text');
        expect(toggleText.textContent).toBe('King James Version');

        // Error banner shown
        const banner = document.querySelector('.scripture-error-banner');
        expect(banner).not.toBeNull();
        expect(banner.className).toContain('alert-warning');

        // Banner has retry button
        const retryBtn = banner.querySelector('.scripture-retry-banner-btn');
        expect(retryBtn).not.toBeNull();

        // Banner has diagnostic details
        const details = banner.querySelector('details');
        expect(details).not.toBeNull();
        expect(details.textContent).toContain('ESV');
        expect(details.textContent).toContain('api_bible');
    });

    test('dropdown text and select are never changed on failure', async function () {
        const mockFetch = jest.fn().mockRejectedValue(new Error('Network error'));
        setupModule(mockFetch, { currentVersion: 'kjv' });

        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();

        // Wait for retries to exhaust
        await new Promise(function (r) { setTimeout(r, 200); });

        const toggleText = document.querySelector('.scripture-dropdown-text');
        expect(toggleText.textContent).toBe('King James Version');

        const select = document.querySelector('.scripture-version-select');
        expect(select.value).toBe('kjv');
    });
});

// --- Error banner ---
describe('error banner', function () {
    test('error banner is removed when new fetch starts', async function () {
        let callNum = 0;
        const mockFetch = jest.fn().mockImplementation(function () {
            callNum += 1;
            if (callNum <= 1) {
                // First call (ESV) fails non-retryable
                return Promise.resolve({
                    ok: true,
                    text: function () {
                        return Promise.resolve(JSON.stringify({
                            success: false,
                            retryable: false,
                            message: 'Test error',
                            provider: 'local',
                            requested: 'esv'
                        }));
                    }
                });
            }
            // Subsequent calls succeed (NIV)
            return Promise.resolve({
                ok: true,
                text: function () {
                    return Promise.resolve(JSON.stringify({
                        success: true,
                        text: '<p>NIV text</p>',
                        copyright: '',
                        translation: 'niv',
                        fallback: false,
                        provider: 'local',
                        requested: 'niv'
                    }));
                }
            });
        });
        setupModule(mockFetch, { currentVersion: 'kjv' });

        // First attempt — fails, shows banner
        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();
        await new Promise(function (r) { setTimeout(r, 100); });

        const banner = document.querySelector('.scripture-error-banner');
        expect(banner).not.toBeNull();

        // Click NIV — existing banner should be removed during loading
        const nivItem = document.querySelector('[data-value="niv"]');
        nivItem.click();

        // Wait for NIV fetch to succeed — banner from ESV should be gone
        await new Promise(function (r) { setTimeout(r, 100); });
        const bannerAfterNiv = document.querySelector('.scripture-error-banner');
        expect(bannerAfterNiv).toBeNull();

        // NIV content should be displayed
        const body = document.querySelector('.scripture-body');
        expect(body.textContent).toContain('NIV text');
    });

    test('banner includes diagnostic details section', async function () {
        const mockFetch = jest.fn().mockResolvedValue({
            ok: true,
            text: function () {
                return Promise.resolve(JSON.stringify({
                    success: false,
                    retryable: false,
                    message: 'API key expired',
                    provider: 'api_bible',
                    requested: 'esv'
                }));
            }
        });
        setupModule(mockFetch, { currentVersion: 'kjv' });

        const esvItem = document.querySelector('[data-value="esv"]');
        esvItem.click();
        await new Promise(function (r) { setTimeout(r, 100); });

        const banner = document.querySelector('.scripture-error-banner');
        expect(banner).not.toBeNull();

        const details = banner.querySelector('details');
        expect(details).not.toBeNull();
        expect(details.textContent).toContain('API key expired');
        expect(details.textContent).toContain('api_bible');
        expect(details.textContent).toContain('ESV');
    });
});

// --- Menu interaction ---
describe('menu interaction', function () {
    test('toggle opens and closes the menu', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch);

        const toggle = document.querySelector('.scripture-dropdown-toggle');
        const menu = document.querySelector('.scripture-dropdown-menu');

        // Initially closed
        expect(menu.style.display).toBe('none');

        // Click to open
        toggle.click();
        expect(menu.style.display).toBe('flex');
        expect(toggle.getAttribute('aria-expanded')).toBe('true');

        // Click to close
        toggle.click();
        expect(menu.style.display).toBe('none');
        expect(toggle.getAttribute('aria-expanded')).toBe('false');
    });

    test('items get tabindex for keyboard navigation', function () {
        const mockFetch = jest.fn();
        setupModule(mockFetch);

        const items = document.querySelectorAll('.scripture-dropdown-item');
        items.forEach(function (item) {
            expect(item.getAttribute('tabindex')).toBe('0');
        });
    });
});
