'use strict';

/**
 * Tests for template-lazyload.es6.js
 * Template form lazy-loading functionality
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach } = require('node:test');
const { fn, resetModules } = require('./helpers/mocks');
const { includes } = require('./helpers/assert');

describe('template-lazyload.es6.js', () => {
    describe('Lazy Loading Functionality', () => {
        beforeEach(() => {
            // Mock Joomla
            global.Joomla = {
                getOptions: fn(() => 'csrf_token'),
                initCustomSelect: fn()
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
            global.fetch = fn(() =>
                Promise.resolve({
                    json: () => Promise.resolve({
                        success: true,
                        html: '<div>Loaded content</div>'
                    })
                })
            );

            resetModules();
        });

        afterEach(() => {
            resetModules();
            delete global.Joomla;
        });

        test('should initialize without errors', () => {
            assert.doesNotThrow(() => {
                require('../../build/media_source/js/template-lazyload.es6.js');
                document.dispatchEvent(new Event('DOMContentLoaded'));
            });
        });

        test('should load fieldset that is already shown', async () => {
            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Wait for async operations
            await new Promise(resolve => setTimeout(resolve, 0));

            // The "show" accordion should trigger a fetch
            assert.ok(global.fetch.mock.callCount() > 0);
            const [fetchUrl] = global.fetch.mock.calls[0].arguments;
            includes(fetchUrl, 'fieldset=study_details');
            includes(fetchUrl, 'id=42');
        });

        test('should load fieldset on accordion show event', async () => {
            require('../../build/media_source/js/template-lazyload.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Clear previous calls
            global.fetch.mock.resetCalls();

            // Trigger show.bs.collapse on the first accordion
            const accordion = document.querySelector('[data-lazy-fieldset="messages_list"]');
            const collapse = accordion.querySelector('.accordion-collapse');
            collapse.dispatchEvent(new Event('show.bs.collapse'));

            await new Promise(resolve => setTimeout(resolve, 0));

            assert.ok(global.fetch.mock.callCount() > 0);
            const [fetchUrl] = global.fetch.mock.calls[0].arguments;
            includes(fetchUrl, 'fieldset=messages_list');
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
            assert.strictEqual(global.fetch.mock.calls.length, callCount);
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
            global.fetch = fn(() =>
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
            includes(container.innerHTML, 'Server error occurred');
        });
    });
});
