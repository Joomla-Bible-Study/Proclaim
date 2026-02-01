/**
 * @jest-environment jsdom
 */

/**
 * Tests for media/js/cwmcore.js
 */

const { loadCwmCore } = require('./helpers/loadScript');
const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/cwmcore.es6.js';

describe('cwmcore.js', () => {
    let exports;

    beforeAll(() => {
        // Load the script and get exports
        exports = loadCwmCore();
    });

    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            const result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('bandwidth()', () => {
        it('should convert KB to bytes', () => {
            expect(exports.bandwidth(1, 'KB')).toBe(1024);
            expect(exports.bandwidth(2, 'KB')).toBe(2048);
            expect(exports.bandwidth(0.5, 'KB')).toBe(512);
        });

        it('should convert MB to bytes', () => {
            expect(exports.bandwidth(1, 'MB')).toBe(1048576);
            expect(exports.bandwidth(2, 'MB')).toBe(2097152);
        });

        it('should convert GB to bytes', () => {
            expect(exports.bandwidth(1, 'GB')).toBe(1073741824);
            expect(exports.bandwidth(2, 'GB')).toBe(2147483648);
        });

        it('should handle lowercase type', () => {
            expect(exports.bandwidth(1, 'kb')).toBe(1024);
            expect(exports.bandwidth(1, 'mb')).toBe(1048576);
            expect(exports.bandwidth(1, 'gb')).toBe(1073741824);
        });

        it('should return error for invalid type', () => {
            expect(exports.bandwidth(1, 'TB')).toBe('error');
            expect(exports.bandwidth(1, 'invalid')).toBe('error');
        });

        it('should return error for NaN input', () => {
            expect(exports.bandwidth('abc', 'KB')).toBe('error');
            expect(exports.bandwidth(undefined, 'KB')).toBe('error');
        });

        it('should return error for empty string input', () => {
            expect(exports.bandwidth('', 'KB')).toBe('error');
        });
    });

    describe('decOnly()', () => {
        it('should remove non-numeric characters except decimal', () => {
            const input = { value: 'abc123.45def' };
            exports.decOnly(input);
            expect(input.value).toBe('123.45');
        });

        it('should keep only first decimal point', () => {
            const input = { value: '12.34.56' };
            exports.decOnly(input);
            expect(input.value).toBe('12.3456');
        });

        it('should handle integers without decimal', () => {
            const input = { value: 'test123test' };
            exports.decOnly(input);
            expect(input.value).toBe('123');
        });

        it('should handle empty string', () => {
            const input = { value: '' };
            exports.decOnly(input);
            expect(input.value).toBe('');
        });

        it('should handle string with only non-numeric chars', () => {
            const input = { value: 'abcdef' };
            exports.decOnly(input);
            expect(input.value).toBe('');
        });
    });

    describe('transferFileSize()', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <input id="Text1" value="10" />
                <select id="Select1"><option value="MB">MB</option></select>
                <input id="jform_params_size" value="" />
            `;
        });

        it('should convert file size and set form value', () => {
            const result = exports.transferFileSize();
            expect(result).toBe(true);
            expect(document.getElementById('jform_params_size').value).toBe('10485760');
        });

        it('should show alert for invalid input', () => {
            document.getElementById('Text1').value = 'abc';
            const alertMock = jest.spyOn(window, 'alert').mockImplementation(() => {});

            const result = exports.transferFileSize();

            expect(result).toBe(false);
            expect(alertMock).toHaveBeenCalledWith('Numbers only please.');

            alertMock.mockRestore();
        });
    });

    describe('ProclaimA11y', () => {
        beforeEach(() => {
            document.body.innerHTML = '';
        });

        describe('getFocusableElements()', () => {
            it('should return focusable selectors correctly', () => {
                document.body.innerHTML = `
                    <div id="container">
                        <a href="#">Link</a>
                        <button>Button</button>
                        <input type="text" />
                        <input type="hidden" />
                        <button disabled>Disabled</button>
                        <select><option>Option</option></select>
                        <textarea></textarea>
                        <div tabindex="0">Focusable div</div>
                        <div tabindex="-1">Not focusable</div>
                    </div>
                `;
                const container = document.getElementById('container');

                // Mock offsetParent for jsdom (it always returns null in jsdom)
                // In real browsers, visible elements have a non-null offsetParent
                const elements = container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])');
                elements.forEach(el => {
                    Object.defineProperty(el, 'offsetParent', {
                        get: () => document.body,
                        configurable: true
                    });
                });

                const focusable = exports.ProclaimA11y.getFocusableElements(container);

                // Should find: a, button (enabled), input (text), select, textarea, div[tabindex="0"]
                expect(focusable.length).toBe(6);
            });

            it('should exclude hidden elements', () => {
                document.body.innerHTML = `
                    <div id="container">
                        <button id="hidden" style="display:none">Hidden</button>
                        <button id="visible">Visible</button>
                    </div>
                `;
                const container = document.getElementById('container');

                // Mock offsetParent - hidden element returns null, visible returns body
                const visible = document.getElementById('visible');
                Object.defineProperty(visible, 'offsetParent', {
                    get: () => document.body,
                    configurable: true
                });
                // Hidden button keeps default null offsetParent

                const focusable = exports.ProclaimA11y.getFocusableElements(container);
                expect(focusable.length).toBe(1);
                expect(focusable[0].id).toBe('visible');
            });
        });

        describe('announce()', () => {
            it('should create announcer element if not exists', () => {
                expect(document.getElementById('proclaim-a11y-announcer')).toBeNull();

                exports.ProclaimA11y.announce('Test message');

                const announcer = document.getElementById('proclaim-a11y-announcer');
                expect(announcer).not.toBeNull();
                expect(announcer.getAttribute('aria-live')).toBe('polite');
            });

            it('should use assertive priority when specified', () => {
                exports.ProclaimA11y.announce('Urgent message', 'assertive');

                const announcer = document.getElementById('proclaim-a11y-announcer');
                expect(announcer.getAttribute('aria-live')).toBe('assertive');
            });

            it('should have visually hidden styles', () => {
                exports.ProclaimA11y.announce('Hidden message');

                const announcer = document.getElementById('proclaim-a11y-announcer');
                expect(announcer.style.position).toBe('absolute');
                expect(announcer.className).toBe('visually-hidden');
            });
        });

        describe('trapFocus()', () => {
            it('should set aria-modal and role attributes', () => {
                document.body.innerHTML = `
                    <div id="modal">
                        <button>Close</button>
                    </div>
                `;
                const modal = document.getElementById('modal');

                exports.ProclaimA11y.trapFocus(modal);

                expect(modal.getAttribute('aria-modal')).toBe('true');
                expect(modal.getAttribute('role')).toBe('dialog');
            });

            it('should accept selector string', () => {
                document.body.innerHTML = `
                    <div id="modal">
                        <button>Close</button>
                    </div>
                `;

                exports.ProclaimA11y.trapFocus('#modal');

                const modal = document.getElementById('modal');
                expect(modal.getAttribute('aria-modal')).toBe('true');
            });

            it('should store previous active element', () => {
                document.body.innerHTML = `
                    <button id="trigger">Open Modal</button>
                    <div id="modal">
                        <button>Close</button>
                    </div>
                `;
                const trigger = document.getElementById('trigger');
                trigger.focus();

                const modal = document.getElementById('modal');
                exports.ProclaimA11y.trapFocus(modal);

                expect(exports.ProclaimA11y.previousActiveElement).toBe(trigger);
            });

            it('should handle null modal gracefully', () => {
                expect(() => {
                    exports.ProclaimA11y.trapFocus(null);
                }).not.toThrow();
            });

            it('should handle non-existent selector gracefully', () => {
                expect(() => {
                    exports.ProclaimA11y.trapFocus('#non-existent');
                }).not.toThrow();
            });
        });

        describe('releaseFocus()', () => {
            it('should restore focus to previous element', () => {
                document.body.innerHTML = `
                    <button id="trigger">Open Modal</button>
                    <div id="modal">
                        <button id="modalBtn">Close</button>
                    </div>
                `;
                const trigger = document.getElementById('trigger');
                const modal = document.getElementById('modal');

                trigger.focus();
                exports.ProclaimA11y.trapFocus(modal);

                exports.ProclaimA11y.releaseFocus(modal);

                expect(document.activeElement).toBe(trigger);
                expect(exports.ProclaimA11y.previousActiveElement).toBeNull();
            });

            it('should handle null modal gracefully', () => {
                expect(() => {
                    exports.ProclaimA11y.releaseFocus(null);
                }).not.toThrow();
            });
        });
    });
});
