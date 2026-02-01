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

            // Mock Joomla
            global.Joomla = {
                getOptions: jest.fn(() => ({})),
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
            delete global.Sortable;
            delete global.bootstrap;
            delete global.Joomla;
            delete window.LayoutEditor;
            delete window.proclaimLayoutEditor;
            jest.resetModules();
        });

        test('should export LayoutEditor to window', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            expect(window.LayoutEditor).toBeDefined();
        });

        test('should auto-initialize when container exists', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(window.proclaimLayoutEditor).toBeDefined();
        });

        test('should create editor structure', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const container = document.getElementById('layout-editor-container');
            expect(container.querySelector('.layout-editor')).toBeTruthy();
            expect(container.querySelector('.layout-sidebar')).toBeTruthy();
            expect(container.querySelector('.layout-canvas')).toBeTruthy();
            expect(container.querySelector('.layout-toolbar')).toBeTruthy();
        });

        test('should create context tabs', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const tabs = document.querySelectorAll('.layout-context-tab');
            expect(tabs.length).toBeGreaterThan(0);
        });

        test('should create row elements in canvas', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            const rows = document.querySelectorAll('.layout-row');
            expect(rows.length).toBe(6); // Default numRows is 6
        });

        test('should initialize Sortable on palette and rows', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            // Sortable should be called for palette + rows
            expect(mockSortable.create).toHaveBeenCalled();
        });

        test('should have undo/redo buttons', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(document.querySelector('.btn-undo')).toBeTruthy();
            expect(document.querySelector('.btn-redo')).toBeTruthy();
        });

        test('should have grid toggle button', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(document.querySelector('.btn-grid')).toBeTruthy();
        });

        test('should have view toggle buttons', () => {
            require('../../build/media_source/js/layout-editor.es6.js');
            document.dispatchEvent(new Event('DOMContentLoaded'));

            expect(document.querySelector('.btn-view-visual')).toBeTruthy();
            expect(document.querySelector('.btn-view-classic')).toBeTruthy();
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
