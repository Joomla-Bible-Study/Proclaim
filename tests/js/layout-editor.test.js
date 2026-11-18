'use strict';

/**
 * Tests for layout-editor.es6.js
 * Visual Layout Editor for Proclaim Templates
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach, mock } = require('node:test');
const { fn, resetModules, runAllTimers } = require('./helpers/mocks');

describe('layout-editor.es6.js', () => {
    describe('LayoutEditor Class', () => {
        let mockSortable;

        beforeEach(() => {
            // Use fake timers for async initialization
            mock.timers.enable({ apis: ['setTimeout', 'setInterval', 'Date'], now: Date.now() });

            // Mock requestIdleCallback (not available in jsdom)
            global.requestIdleCallback = (cb) => setTimeout(cb, 0);

            // Mock Sortable.js
            mockSortable = {
                create: fn(() => ({
                    destroy: fn()
                }))
            };
            global.Sortable = mockSortable;

            // Mock Bootstrap Modal. A function rather than an arrow: the code
            // calls it with `new`, which node:test's mock passes through to it.
            global.bootstrap = {
                Modal: fn(function () {
                    return {
                        show: fn(),
                        hide: fn()
                    };
                })
            };

            // Mock Joomla with required options
            const mockOptions = {
                'com_proclaim.elementDefinitions': {
                    messages: {
                        label: 'Messages List',
                        prefix: '',
                        elements: [
                            { id: 'title', label: 'Title' },
                            { id: 'date', label: 'Date' },
                            { id: 'teacher', label: 'Teacher' }
                        ]
                    }
                },
                'com_proclaim.templateParams': {
                    titlerow: '1',
                    titlecol: '1',
                    titlecolspan: '12',
                    titleelement: '1',
                    titlecustom: '',
                    titlelinktype: '0'
                },
                'com_proclaim.linkTypeOptions': [
                    { value: '0', label: 'No Link' },
                    { value: '1', label: 'Link to Details' }
                ],
                'com_proclaim.dateFormatOptions': [
                    { value: '', label: 'Use Global' },
                    { value: '0', label: 'Sep 1, 2012' }
                ],
                'com_proclaim.showVersesOptions': [
                    { value: '', label: 'Use Global' },
                    { value: '0', label: 'Chapters Only' }
                ],
                'com_proclaim.elementTypeOptions': [
                    { value: '0', label: 'None' },
                    { value: '1', label: 'P' }
                ]
            };
            global.Joomla = {
                getOptions: fn((key) => mockOptions[key] || {}),
                optionsStorage: mockOptions,
                Text: {
                    _: fn(key => key)
                }
            };

            // Set up DOM
            document.body.innerHTML = `
                <div id="layout-editor-container" data-context="messages"></div>
                <form id="item-form">
                    <input name="jform[params][titlerow]" value="1" />
                    <input name="jform[params][titlecol]" value="1" />
                    <input name="jform[params][titlecolspan]" value="12" />
                    <input name="jform[params][titleelement]" value="1" />
                    <input name="jform[params][titlecustom]" value="" />
                    <input name="jform[params][titlelinktype]" value="0" />
                </form>
            `;

            resetModules();
        });

        afterEach(() => {
            mock.timers.reset();
            delete global.Sortable;
            delete global.bootstrap;
            delete global.Joomla;
            delete global.requestIdleCallback;
            delete window.LayoutEditor;
            delete window.proclaimLayoutEditor;
            delete window.ProclaimLayoutEditor;
            resetModules();
        });

        test('should export LayoutEditor to window', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            assert.notStrictEqual(window.LayoutEditor, undefined);
        });

        test('should auto-initialize when container exists', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            assert.notStrictEqual(window.proclaimLayoutEditor, undefined);
        });

        test('should create editor structure', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            const container = document.getElementById('layout-editor-container');
            assert.ok(container.querySelector('.layout-editor'));
            assert.ok(container.querySelector('.layout-sidebar'));
            assert.ok(container.querySelector('.layout-canvas'));
            assert.ok(container.querySelector('.layout-toolbar'));
        });

        test('should hide context tabs for single context', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            // With only one context (messages), tabs should be hidden
            const tabsContainer = document.querySelector('.layout-context-tabs');
            assert.ok(tabsContainer);
            // Single context mode hides the tabs container
            assert.strictEqual(tabsContainer.style.display, 'none');
        });

        test('should create row elements in canvas', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            const rows = document.querySelectorAll('.layout-row');
            // Rows are created dynamically based on data; at minimum there's row 1 with data plus an empty row
            assert.ok(rows.length >= 1);
        });

        test('should initialize Sortable on palette and rows', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            // Sortable should be called for palette + rows
            assert.ok(mockSortable.create.mock.callCount() > 0);
        });

        test('should have undo/redo buttons', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            assert.ok(document.querySelector('.btn-undo'));
            assert.ok(document.querySelector('.btn-redo'));
        });

        test('should have grid toggle button', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            assert.ok(document.querySelector('.btn-grid'));
        });

        test('should have view settings button', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            runAllTimers();

            assert.ok(document.querySelector('.btn-view-settings'));
        });

        test('LayoutEditor constructor should accept options', () => {
            require('../../build/media_source/js/layout-editor.es6.js');

            const container = document.createElement('div');
            document.body.appendChild(container);

            const editor = new window.LayoutEditor(container, {
                numRows: 4,
                context: 'details'
            });

            assert.strictEqual(editor.options.numRows, 4);
            assert.strictEqual(editor.options.context, 'details');
        });
    });
});
