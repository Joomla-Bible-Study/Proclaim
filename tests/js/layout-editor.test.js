/**
 * Tests for layout-editor.es6.js
 * Visual Layout Editor for Proclaim Templates
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/layout-editor.es6.js';

describe('layout-editor.es6.js', () => {
    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            const result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('LayoutEditor Class', () => {
        let mockSortable;

        beforeEach(() => {
            // Use fake timers for async initialization
            jest.useFakeTimers();

            // Mock requestIdleCallback (not available in jsdom)
            global.requestIdleCallback = (cb) => setTimeout(cb, 0);

            // Mock Sortable.js
            mockSortable = {
                create: jest.fn(() => ({
                    destroy: jest.fn()
                }))
            };
            global.Sortable = mockSortable;

            // Mock Bootstrap Modal
            global.bootstrap = {
                Modal: jest.fn(() => ({
                    show: jest.fn(),
                    hide: jest.fn()
                }))
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
                getOptions: jest.fn((key) => mockOptions[key] || {}),
                optionsStorage: mockOptions,
                Text: {
                    _: jest.fn(key => key)
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

            jest.resetModules();
        });

        afterEach(() => {
            jest.useRealTimers();
            delete global.Sortable;
            delete global.bootstrap;
            delete global.Joomla;
            delete global.requestIdleCallback;
            delete window.LayoutEditor;
            delete window.proclaimLayoutEditor;
            delete window.ProclaimLayoutEditor;
            jest.resetModules();
        });

        test('should export LayoutEditor to window', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            expect(window.LayoutEditor).toBeDefined();
        });

        test('should auto-initialize when container exists', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            expect(window.proclaimLayoutEditor).toBeDefined();
        });

        test('should create editor structure', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            const container = document.getElementById('layout-editor-container');
            expect(container.querySelector('.layout-editor')).toBeTruthy();
            expect(container.querySelector('.layout-sidebar')).toBeTruthy();
            expect(container.querySelector('.layout-canvas')).toBeTruthy();
            expect(container.querySelector('.layout-toolbar')).toBeTruthy();
        });

        test('should hide context tabs for single context', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            // With only one context (messages), tabs should be hidden
            const tabsContainer = document.querySelector('.layout-context-tabs');
            expect(tabsContainer).toBeTruthy();
            // Single context mode hides the tabs container
            expect(tabsContainer.style.display).toBe('none');
        });

        test('should create row elements in canvas', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            const rows = document.querySelectorAll('.layout-row');
            // Rows are created dynamically based on data; at minimum there's row 1 with data plus an empty row
            expect(rows.length).toBeGreaterThanOrEqual(1);
        });

        test('should initialize Sortable on palette and rows', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            // Sortable should be called for palette + rows
            expect(mockSortable.create).toHaveBeenCalled();
        });

        test('should have undo/redo buttons', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            expect(document.querySelector('.btn-undo')).toBeTruthy();
            expect(document.querySelector('.btn-redo')).toBeTruthy();
        });

        test('should have grid toggle button', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            expect(document.querySelector('.btn-grid')).toBeTruthy();
        });

        test('should have view settings button', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));
            jest.runAllTimers();

            expect(document.querySelector('.btn-view-settings')).toBeTruthy();
        });

        test('LayoutEditor constructor should accept options', () => {
            require('../../build/media_source/js/layout-editor.es6.js');

            const container = document.createElement('div');
            document.body.appendChild(container);

            const editor = new window.LayoutEditor(container, {
                numRows: 4,
                context: 'details'
            });

            expect(editor.options.numRows).toBe(4);
            expect(editor.options.context).toBe('details');
        });
    });
});
