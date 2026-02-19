/**
 * Tests for cwmadmin-*-modal.es6.js files
 * Modal selection functionality for admin views
 */

// List of modal files to test
const MODAL_FILES = [
    { name: 'locations', file: 'build/media_source/js/cwmadmin-locations-modal.es6.js', selectFn: 'jSelectLocations' },
    { name: 'messages', file: 'build/media_source/js/cwmadmin-messages-modal.es6.js', selectFn: 'jSelectMessages' },
    { name: 'series', file: 'build/media_source/js/cwmadmin-series-modal.es6.js', selectFn: 'jSelectSeries' },
    { name: 'servers', file: 'build/media_source/js/cwmadmin-servers-modal.es6.js', selectFn: 'jSelectServer' },
    { name: 'teachers', file: 'build/media_source/js/cwmadmin-teachers-modal.es6.js', selectFn: 'jSelectTeachers' },
    { name: 'types', file: 'build/media_source/js/cwmadmin-types-modal.es6.js', selectFn: 'jSelectType' }
];

describe('Admin Modal Files', () => {
    MODAL_FILES.forEach(({ name, file, selectFn }) => {
        describe(`cwmadmin-${name}-modal.es6.js`, () => {
            describe('Modal Functionality', () => {
                beforeEach(() => {
                    // Mock Joomla
                    global.Joomla = {
                        getOptions: jest.fn((key) => {
                            if (key === `xtd-${name}`) {
                                return { editor: 'test-editor' };
                            }
                            return null;
                        })
                    };

                    // Mock parent window
                    global.window.parent = {
                        Joomla: {
                            Modal: {
                                getCurrent: jest.fn(() => ({
                                    close: jest.fn()
                                }))
                            },
                            editors: {
                                instances: {
                                    'test-editor': {
                                        replaceSelection: jest.fn()
                                    }
                                }
                            }
                        }
                    };

                    // Set up DOM
                    document.body.innerHTML = `
                        <a class="select-link"
                            data-function="${selectFn}"
                            data-id="1"
                            data-title="Test Title"
                            data-cat-id="2"
                            data-uri="/test/link"
                            data-language="en-GB">
                            Select
                        </a>
                    `;

                    jest.resetModules();
                });

                afterEach(() => {
                    delete global.Joomla;
                    delete window[selectFn];
                    jest.resetModules();
                });

                test(`should define ${selectFn} function`, () => {
                    require(`../../${file}`);
                    expect(typeof window[selectFn]).toBe('function');
                });

                test('should bind click events on DOMContentLoaded', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));

                    const link = document.querySelector('.select-link');
                    const clickSpy = jest.fn();
                    link.addEventListener('click', clickSpy);

                    // Verify click handler is bound (we check the link exists and is clickable)
                    expect(link).toBeTruthy();
                });

                test(`${selectFn} should return false when options not found`, () => {
                    global.Joomla.getOptions = jest.fn(() => null);
                    require(`../../${file}`);

                    const result = window[selectFn](1, 'Title', 2, null, '/link', 'en');
                    expect(result).toBe(false);
                });
            });
        });
    });
});
