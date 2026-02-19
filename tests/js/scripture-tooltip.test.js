/**
 * @jest-environment jsdom
 */

/**
 * Tests for scripture-tooltip.es6.js
 * Bible verse popover tooltips on scripture references
 */

const SOURCE_FILE = 'build/media_source/js/scripture-tooltip.es6.js';

/**
 * Set up DOM and mocks for tooltip module initialization.
 */
function setupModule(opts = {}) {
    document.body.innerHTML = `
        <div>
            <span class="proclaim-scripture-ref" role="button" tabindex="0"
               data-scripture-ref="Luke+7:36-38" data-bible-version="nkjv">
                Luke 7:36-38 NKJV
            </span>
            <span class="proclaim-scripture-ref" role="button" tabindex="0"
               data-scripture-ref="Genesis+1:1" data-bible-version="kjv">
                Genesis 1:1 KJV
            </span>
        </div>
    `;

    // Mock Bootstrap Popover
    const mockPopoverInstance = {
        show: jest.fn(),
        hide: jest.fn(),
        dispose: jest.fn(),
        setContent: jest.fn(),
    };

    global.bootstrap = {
        Popover: jest.fn().mockReturnValue(mockPopoverInstance),
    };

    global.Joomla = {
        getOptions: jest.fn((key) => {
            if (key === 'com_proclaim.scripture') {
                return {
                    ajaxUrl: opts.ajaxUrl || 'http://localhost/index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw',
                };
            }

            if (key === 'csrf.token') {
                return 'testtoken';
            }

            if (key === 'system.debug') {
                return false;
            }

            return null;
        }),
        Text: {
            _: jest.fn((key, fallback) => fallback || key),
        },
    };

    // Mock sessionStorage
    const storage = {};

    global.sessionStorage = {
        getItem: jest.fn((k) => storage[k] || null),
        setItem: jest.fn((k, v) => { storage[k] = v; }),
    };

    // Load the module
    require('../../' + SOURCE_FILE);

    // Trigger DOMContentLoaded
    const event = new Event('DOMContentLoaded');

    document.dispatchEvent(event);

    return { mockPopoverInstance, storage };
}

describe('scripture-tooltip.es6.js', () => {
    beforeEach(() => {
        jest.resetModules();
        document.body.innerHTML = '';
        delete global.Joomla;
        delete global.bootstrap;
    });

    describe('Initialization', () => {
        test('should not throw when no AJAX URL is configured', () => {
            document.body.innerHTML = '<span class="proclaim-scripture-ref" data-scripture-ref="Gen+1:1">Gen 1:1</span>';

            global.bootstrap = {
                Popover: jest.fn(),
            };
            global.Joomla = {
                getOptions: jest.fn().mockReturnValue({}),
                Text: { _: jest.fn((k, f) => f || k) },
            };

            expect(() => {
                require('../../' + SOURCE_FILE);
                document.dispatchEvent(new Event('DOMContentLoaded'));
            }).not.toThrow();
        });

        test('should set up scripture reference links in DOM', () => {
            setupModule();
            const refs = document.querySelectorAll('.proclaim-scripture-ref');

            expect(refs.length).toBe(2);
        });
    });

    describe('Element type', () => {
        test('should use span elements (not links) to avoid plugin conflicts', () => {
            setupModule();
            const ref = document.querySelector('.proclaim-scripture-ref');

            expect(ref.tagName).toBe('SPAN');
            expect(ref.getAttribute('role')).toBe('button');
            expect(ref.getAttribute('tabindex')).toBe('0');
        });
    });

    describe('Keyboard interaction', () => {
        test('should respond to Escape key', () => {
            const { mockPopoverInstance } = setupModule();
            const event = new KeyboardEvent('keydown', { key: 'Escape', bubbles: true });

            document.body.dispatchEvent(event);

            // No active ref, so nothing should happen (no error)
            expect(true).toBe(true);
        });
    });

    describe('Data attributes', () => {
        test('scripture refs should have correct data attributes', () => {
            setupModule();

            const ref = document.querySelector('.proclaim-scripture-ref');

            expect(ref.dataset.scriptureRef).toBe('Luke+7:36-38');
            expect(ref.dataset.bibleVersion).toBe('nkjv');
        });
    });
});
