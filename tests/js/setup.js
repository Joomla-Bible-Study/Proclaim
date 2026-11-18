'use strict';

/**
 * Setup for the JS unit tests: a browser window (helpers/dom.js), the Joomla
 * and fetch mocks, and the reset between tests. Every test file requires this
 * first; Jest's config used to run it before each file (setupFilesAfterEnv).
 */

require('./helpers/dom');

const { beforeEach, afterEach } = require('node:test');
const { fn, resolving, clearAllMocks, restoreAllMocks } = require('./helpers/mocks');

// Mock Joomla global object
global.Joomla = {
    getOptions: fn((key, defaultValue) => {
        const options = {
            'csrf.token': 'test_csrf_token'
        };
        return options[key] !== undefined ? options[key] : defaultValue;
    }),
    initCustomSelect: fn(),
    JText: {
        _: fn((key) => key)
    },
    renderMessages: fn(),
    removeMessages: fn()
};

// Mock fetch API
global.fetch = fn(() =>
    Promise.resolve({
        ok: true,
        json: () => Promise.resolve({ success: true, data: {} }),
        text: () => Promise.resolve('')
    })
);

// Mock ProclaimFetch — delegates to global.fetch so existing test mocks work.
// Full ProclaimFetch behavior (timeout, retry, gatekeeper) is tested in cwm-fetch.test.js.
window.ProclaimFetch = {
    fetch: (url, fetchOpts) => global.fetch(url, fetchOpts || {}),
    fetchJson: (url, fetchOpts) => global.fetch(url, fetchOpts || {}).then((r) => r.json()),
    ADMIN_TIMEOUT: 30000,
    FRONTEND_TIMEOUT: 15000,
    LONG_TIMEOUT: 60000,
};

// Mock window.alert and window.confirm
global.alert = fn();
global.confirm = fn(() => true);

// Mock the JoomlaDialog bridge (cwm-dialog.es6.mjs) — Promise-based, like the
// real JoomlaDialog.confirm()/alert(). The bridge module is not loaded in unit
// tests, so provide the globals the migrated handlers call. cwmConfirm defaults
// to resolving true (the "OK/Yes" path); override per-test for the cancel path.
window.cwmConfirm = fn(() => Promise.resolve(true));
window.cwmAlert = fn(() => Promise.resolve());

// Mock console methods to reduce noise (but keep errors visible)
const originalError = console.error;
global.console = {
    ...console,
    log: fn(),
    warn: fn(),
    error: originalError
};

// Mock Bootstrap events
class MockBsEvent extends Event {
    constructor(type, init) {
        super(type, init);
    }
}

global.Event = MockBsEvent;

// Helper to trigger Bootstrap events
global.triggerBsEvent = (element, eventName) => {
    const event = new Event(eventName, { bubbles: true });
    element.dispatchEvent(event);
};

// Mock HTMLMediaElement methods not implemented in jsdom
window.HTMLMediaElement.prototype.pause = fn();
window.HTMLMediaElement.prototype.play = fn(resolving(undefined));
window.HTMLMediaElement.prototype.load = fn();

// Reset DOM before each test, and clear every mock's calls, as Jest's
// `clearMocks: true` config did
beforeEach(() => {
    document.body.innerHTML = '';
    clearAllMocks();
});

// Clean up after each test: put back every method a test spied on
afterEach(() => {
    restoreAllMocks();
});
