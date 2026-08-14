/**
 * Tests for cwmadmin-*-modal.es6.js files
 * Modal selection functionality for admin views
 */

// List of modal files to test
// `args` is each script's own call signature. Servers deliberately differs:
// it passes no category, and afterwards drives the parent form to reload the
// media file against the chosen server.
const SELECT_ARGS = ['1', 'Test Title', '2', null, '/test/link', 'en-GB'];
const SERVER_ARGS = ['1', 'Test Title', '/test/link', 'en-GB'];

const MODAL_FILES = [
    { name: 'messages', file: 'build/media_source/js/cwmadmin-messages-modal.es6.js', selectFn: 'jSelectMessages', args: SELECT_ARGS },
    { name: 'series', file: 'build/media_source/js/cwmadmin-series-modal.es6.js', selectFn: 'jSelectSeries', args: SELECT_ARGS },
    { name: 'servers', file: 'build/media_source/js/cwmadmin-servers-modal.es6.js', selectFn: 'jSelectServer', args: SERVER_ARGS, parentTask: 'cwmmediafile.setServer' },
    { name: 'teachers', file: 'build/media_source/js/cwmadmin-teachers-modal.es6.js', selectFn: 'jSelectTeachers', args: SELECT_ARGS }
];

describe('Admin Modal Files', () => {
    MODAL_FILES.forEach(({ name, file, selectFn, args, parentTask }) => {
        describe(`cwmadmin-${name}-modal.es6.js`, () => {
            describe('Modal Functionality', () => {
                let closeSpy;
                let submitSpy;
                let taskInputs;
                let boundListeners;

                beforeEach(() => {
                    // Each require() adds another DOMContentLoaded listener to the
                    // shared document, so without removing them again every later
                    // modal's link is also bound by every earlier modal's script.
                    boundListeners = [];
                    const realAdd = document.addEventListener.bind(document);
                    jest.spyOn(document, 'addEventListener').mockImplementation((type, fn, opts) => {
                        boundListeners.push([type, fn, opts]);
                        realAdd(type, fn, opts);
                    });

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
                    closeSpy = jest.fn();
                    global.window.parent = {
                        Joomla: {
                            Modal: {
                                getCurrent: jest.fn(() => ({
                                    close: closeSpy
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

                    // The servers modal drives the opener's form after selecting.
                    submitSpy = jest.fn();
                    taskInputs = [{ value: '' }];
                    global.window.parent.document = {
                        getElementById: jest.fn(() => ({ submit: submitSpy })),
                        getElementsByName: jest.fn(() => taskInputs)
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
                    boundListeners.forEach(([type, fn, opts]) => document.removeEventListener(type, fn, opts));
                    document.addEventListener.mockRestore();
                    delete global.Joomla;
                    delete window[selectFn];
                    jest.resetModules();
                });

                test(`should define ${selectFn} function`, () => {
                    require(`../../${file}`);
                    expect(typeof window[selectFn]).toBe('function');
                });

                test('clicking a select-link calls the select function with its data attributes', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));

                    // Replace the real function after binding: the handler resolves
                    // window[functionName] at click time, so the spy is what it finds.
                    const selectSpy = jest.fn();
                    window[selectFn] = selectSpy;

                    document.querySelector('.select-link').dispatchEvent(
                        new MouseEvent('click', { bubbles: true, cancelable: true })
                    );

                    expect(selectSpy).toHaveBeenCalledWith(...args);
                });

                test('clicking a select-link closes the modal', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));
                    window[selectFn] = jest.fn();

                    document.querySelector('.select-link').dispatchEvent(
                        new MouseEvent('click', { bubbles: true, cancelable: true })
                    );

                    expect(closeSpy).toHaveBeenCalled();
                });

                test('clicking a select-link suppresses the placeholder href', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));
                    window[selectFn] = jest.fn();

                    const event = new MouseEvent('click', { bubbles: true, cancelable: true });
                    document.querySelector('.select-link').dispatchEvent(event);

                    expect(event.defaultPrevented).toBe(true);
                });

                // Only the servers modal reloads the opener, so that the media file
                // is re-read against the server just chosen.
                const maybe = parentTask ? test : test.skip;

                maybe('selecting a server sets the opener task and submits it', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));
                    window[selectFn] = jest.fn();

                    document.querySelector('.select-link').dispatchEvent(
                        new MouseEvent('click', { bubbles: true, cancelable: true })
                    );

                    expect(taskInputs[0].value).toBe(parentTask);
                    expect(submitSpy).toHaveBeenCalled();
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
