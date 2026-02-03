/**
 * Tests for template-lazyload.es6.js
 * Template form lazy-loading functionality
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/template-lazyload.es6.js';

describe('template-lazyload.es6.js', () => {
    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            const result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('Lazy Loading Functionality', () => {
        beforeEach(() => {
            // Mock Joomla
            global.Joomla = {
                getOptions: jest.fn(() => 'csrf_token'),
                initCustomSelect: jest.fn()
            };

            // Set up DOM with accordion and tab structures
            document.body.innerHTML = `
                <input name="jform[id]" value="42" />

                <div data-lazy-fieldset="messages_list">
                    <div class="accordion-collapse collapse">
                        <div class="accordion-body"></div>
                    </div>
                </div>

                <div data-lazy-fieldset="study_details">
                    <div class="accordion-collapse collapse show">
                        <div class="accordion-body"></div>
                    </div>
                </div>

                <div id="tab-teachers" class="tab-pane" data-lazy-tab="teachers_list,teachers_detail">
                    <div data-fieldset-container="teachers_list"></div>
                    <div data-fieldset-container="teachers_detail"></div>
                </div>

                <button data-bs-target="#tab-teachers"></button>
            `;

            // Mock fetch
            global.fetch = jest.fn(() =>
                Promise.resolve({
                    json: () => Promise.resolve({
                        success: true,
                        html: '<div>Loaded content</div>'
                    })
                })
            );

            jest.resetModules();
        });

        afterEach(() => {
            jest.resetModules();
            delete global.Joomla;
        });

        test('should initialize without errors', () => {
            expect(() => {
                require('../../build/media_source/js/template-lazyload.es6.js');
                document.dispatchEvent(new Event('DOMContentLoaded'));
            }).not.toThrow();
        });

        test('should load fieldset that is already shown', async () => {
            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Wait for async operations
            await new Promise(resolve => setTimeout(resolve, 0));

            // The "show" accordion should trigger a fetch
            expect(global.fetch).toHaveBeenCalled();
            const fetchUrl = global.fetch.mock.calls[0][0];
            expect(fetchUrl).toContain('fieldset=study_details');
            expect(fetchUrl).toContain('id=42');
        });

        test('should load fieldset on accordion show event', async () => {
            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Clear previous calls
            global.fetch.mockClear();

            // Trigger show.bs.collapse on the first accordion
            const accordion = document.querySelector('[data-lazy-fieldset="messages_list"]');
            const collapse = accordion.querySelector('.accordion-collapse');
            collapse.dispatchEvent(new Event('show.bs.collapse'));

            await new Promise(resolve => setTimeout(resolve, 0));

            expect(global.fetch).toHaveBeenCalled();
            const fetchUrl = global.fetch.mock.calls[0][0];
            expect(fetchUrl).toContain('fieldset=messages_list');
        });

        test('should not reload already loaded fieldsets', async () => {
            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            await new Promise(resolve => setTimeout(resolve, 0));

            const callCount = global.fetch.mock.calls.length;

            // Trigger show again on same accordion
            const accordion = document.querySelector('[data-lazy-fieldset="study_details"]');
            const collapse = accordion.querySelector('.accordion-collapse');
            collapse.dispatchEvent(new Event('show.bs.collapse'));

            await new Promise(resolve => setTimeout(resolve, 0));

            // Should not have made additional calls
            expect(global.fetch.mock.calls.length).toBe(callCount);
        });

        test('should handle server errors gracefully', async () => {
            // Set up DOM without pre-shown accordions to avoid immediate loading
            document.body.innerHTML = `
                <input name="jform[id]" value="42" />
                <div data-lazy-fieldset="error_test">
                    <div class="accordion-collapse collapse">
                        <div class="accordion-body"></div>
                    </div>
                </div>
            `;

            // Mock fetch to return an error response (not a rejection)
            global.fetch = jest.fn(() =>
                Promise.resolve({
                    json: () => Promise.resolve({
                        success: false,
                        error: 'Server error occurred'
                    })
                })
            );

            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Trigger the accordion show to initiate loading
            const accordion = document.querySelector('[data-lazy-fieldset="error_test"]');
            const collapse = accordion.querySelector('.accordion-collapse');
            collapse.dispatchEvent(new Event('show.bs.collapse'));

            // Wait for async operations
            await new Promise(resolve => setTimeout(resolve, 10));

            const container = document.querySelector('[data-lazy-fieldset="error_test"] .accordion-body');
            expect(container.innerHTML).toContain('Server error occurred');
        });
    });
});
