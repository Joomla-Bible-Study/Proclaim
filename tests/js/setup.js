/**
 * Jest setup file for Proclaim
 * Configures the test environment with Joomla mocks and browser globals
 */

// Mock Joomla global object
global.Joomla = {
    getOptions: jest.fn((key, defaultValue) => {
        const options = {
            'csrf.token': 'test_csrf_token'
        };
        return options[key] !== undefined ? options[key] : defaultValue;
    }),
    initCustomSelect: jest.fn(),
    JText: {
        _: jest.fn((key) => key)
    },
    renderMessages: jest.fn(),
    removeMessages: jest.fn()
};

// Mock fetch API
global.fetch = jest.fn(() =>
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
global.alert = jest.fn();
global.confirm = jest.fn(() => true);

// Mock console methods to reduce noise (but keep errors visible)
const originalError = console.error;
global.console = {
    ...console,
    log: jest.fn(),
    warn: jest.fn(),
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
window.HTMLMediaElement.prototype.pause = jest.fn();
window.HTMLMediaElement.prototype.play = jest.fn().mockResolvedValue(undefined);
window.HTMLMediaElement.prototype.load = jest.fn();

// Reset DOM before each test
beforeEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
});

// Clean up after each test
afterEach(() => {
    jest.restoreAllMocks();
});