/**
 * Tests for cwmadmin-*-batch-footer.es6.js and cwmadmin-*-batch.es6.js files
 * Batch processing functionality for admin views
 */

// List of batch files to test
const BATCH_FILES = [
    { name: 'locations', file: 'build/media_source/js/cwmadmin-locations-default-batch-footer.es6.js', task: 'cwmlocation.batch' },
    { name: 'mediafiles', file: 'build/media_source/js/cwmadmin-mediafiles-default-batch-footer.es6.js', task: 'cwmmediafile.batch' },
    { name: 'messages', file: 'build/media_source/js/cwmadmin-messages-default-batch-footer.es6.js', task: 'cwmmessage.batch' },
    { name: 'podcasts', file: 'build/media_source/js/cwmadmin-podcasts-default-batch-footer.es6.js', task: 'cwmpodcast.batch' },
    { name: 'servers', file: 'build/media_source/js/cwmadmin-servers-default-batch-footer.es6.js', task: 'cwmserver.batch' },
    { name: 'templates', file: 'build/media_source/js/cwmadmin-templates-default-batch-footer.es6.js', task: 'cwmtemplate.batch' },
    { name: 'series-batch', file: 'build/media_source/js/cwmadmin-series-batch.es6.js', task: 'cwmseries.batch' },
    { name: 'teachers-batch', file: 'build/media_source/js/cwmadmin-teachers-batch.es6.js', task: 'cwmteachers.batch' }
];

describe('Admin Batch Files', () => {
    BATCH_FILES.forEach(({ file, task }) => {
        describe(`${file.split('/').pop()}`, () => {
            describe('Batch Functionality', () => {
                let mockSubmitForm;

                beforeEach(() => {
                    // Mock Joomla.submitform
                    mockSubmitForm = jest.fn();
                    global.Joomla = {
                        submitform: mockSubmitForm
                    };

                    // Set up DOM
                    document.body.innerHTML = `
                        <form id="adminForm">
                            <input type="hidden" name="task" value="" />
                            <input type="hidden" name="boxchecked" value="1" />
                        </form>
                        <button id="batch-submit-button-id" data-submit-task="${task}">
                            Process
                        </button>
                    `;

                    jest.resetModules();
                });

                afterEach(() => {
                    delete global.Joomla;
                    jest.resetModules();
                });

                test('should initialize without errors', () => {
                    expect(() => {
                        require(`../../${file}`);
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                    }).not.toThrow();
                });

                test('should bind click event to batch submit button', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));

                    const button = document.getElementById('batch-submit-button-id');
                    expect(button).toBeTruthy();

                    // Trigger click
                    button.click();

                    // Check if submitform was called with correct task
                    expect(mockSubmitForm).toHaveBeenCalledWith(
                        task,
                        expect.any(Object)
                    );
                });

                test('should not submit if form is missing', () => {
                    // Remove the form
                    document.body.innerHTML = `
                        <button id="batch-submit-button-id" data-submit-task="${task}">
                            Process
                        </button>
                    `;

                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));

                    const button = document.getElementById('batch-submit-button-id');
                    button.click();

                    // submitform should not be called
                    expect(mockSubmitForm).not.toHaveBeenCalled();
                });

                test('should handle missing button gracefully', () => {
                    document.body.innerHTML = '<form id="adminForm"></form>';

                    expect(() => {
                        require(`../../${file}`);
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                    }).not.toThrow();
                });
            });
        });
    });
});
