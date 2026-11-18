'use strict';

/**
 * Tests for cwmadmin-*-batch-footer.es6.js and cwmadmin-*-batch.es6.js files
 * Batch processing functionality for admin views
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach } = require('node:test');
const { fn, resetModules } = require('./helpers/mocks');
const { calledWith, match } = require('./helpers/assert');

// Entity-specific batch files (legacy, kept for backward compat)
const LEGACY_BATCH_FILES = [
    { name: 'locations', file: 'build/media_source/js/cwmadmin-locations-default-batch-footer.es6.js', task: 'cwmlocation.batch' },
    { name: 'mediafiles', file: 'build/media_source/js/cwmadmin-mediafiles-default-batch-footer.es6.js', task: 'cwmmediafile.batch' },
    { name: 'messages', file: 'build/media_source/js/cwmadmin-messages-default-batch-footer.es6.js', task: 'cwmmessage.batch' },
    { name: 'podcasts', file: 'build/media_source/js/cwmadmin-podcasts-default-batch-footer.es6.js', task: 'cwmpodcast.batch' },
    { name: 'servers', file: 'build/media_source/js/cwmadmin-servers-default-batch-footer.es6.js', task: 'cwmserver.batch' },
    { name: 'templates', file: 'build/media_source/js/cwmadmin-templates-default-batch-footer.es6.js', task: 'cwmtemplate.batch' },
    { name: 'series-batch', file: 'build/media_source/js/cwmadmin-series-batch.es6.js', task: 'cwmserie.batch' },
    { name: 'teachers-batch', file: 'build/media_source/js/cwmadmin-teachers-batch.es6.js', task: 'cwmteacher.batch' }
];

describe('Legacy Entity-Specific Batch Files', () => {
    LEGACY_BATCH_FILES.forEach(({ file, task }) => {
        describe(`${file.split('/').pop()}`, () => {
            describe('Batch Functionality', () => {
                let mockSubmitForm;

                beforeEach(() => {
                    // Mock Joomla.submitform
                    mockSubmitForm = fn();
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

                    resetModules();
                });

                afterEach(() => {
                    delete global.Joomla;
                    resetModules();
                });

                test('should initialize without errors', () => {
                    assert.doesNotThrow(() => {
                        require(`../../${file}`);
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                    });
                });

                test('should bind click event to batch submit button', () => {
                    require(`../../${file}`);
                    document.dispatchEvent(new Event('DOMContentLoaded'));

                    const button = document.getElementById('batch-submit-button-id');
                    assert.ok(button);

                    // Trigger click
                    button.click();

                    // Check if submitform was called with correct task
                    calledWith(
                        mockSubmitForm,
                        task,
                        match.any(Object),
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
                    assert.strictEqual(mockSubmitForm.mock.callCount(), 0);
                });

                test('should handle missing button gracefully', () => {
                    document.body.innerHTML = '<form id="adminForm"></form>';

                    assert.doesNotThrow(() => {
                        require(`../../${file}`);
                        document.dispatchEvent(new Event('DOMContentLoaded'));
                    });
                });
            });
        });
    });
});

// Generic batch footer handler (used by all views)
describe('Generic Batch Footer (cwmadmin-batch-footer.es6.js)', () => {
    const GENERIC_FILE = 'build/media_source/js/cwmadmin-batch-footer.es6.js';

    // All entity tasks that should work with the generic handler
    const BATCH_TASKS = [
        'cwmmessage.batch',
        'cwmmediafile.batch',
        'cwmserver.batch',
        'cwmpodcast.batch',
        'cwmtemplate.batch',
        'cwmlocation.batch',
        'cwmserie.batch',
        'cwmteacher.batch',
        'cwmcomment.batch',
        'cwmmessagetype.batch',
    ];

    let mockSubmitForm;

    beforeEach(() => {
        mockSubmitForm = fn();
        global.Joomla = {
            submitform: mockSubmitForm
        };
        resetModules();
    });

    afterEach(() => {
        delete global.Joomla;
        resetModules();
    });

    test('should initialize without errors', () => {
        document.body.innerHTML = '<form id="adminForm"></form>';

        assert.doesNotThrow(() => {
            require(`../../${GENERIC_FILE}`);
            document.dispatchEvent(new Event('DOMContentLoaded'));
        });
    });

    BATCH_TASKS.forEach((task) => {
        test(`should submit ${task} correctly`, () => {
            document.body.innerHTML = `
                <form id="adminForm">
                    <input type="hidden" name="task" value="" />
                </form>
                <button id="batch-submit-button-id" data-submit-task="${task}">
                    Process
                </button>
            `;

            require(`../../${GENERIC_FILE}`);
            document.dispatchEvent(new Event('DOMContentLoaded'));

            document.getElementById('batch-submit-button-id').click();

            calledWith(mockSubmitForm, task, match.any(Object));
        });
    });

    test('should reject tasks that do not end with .batch', () => {
        document.body.innerHTML = `
            <form id="adminForm">
                <input type="hidden" name="task" value="" />
            </form>
            <button id="batch-submit-button-id" data-submit-task="cwmmessage.delete">
                Process
            </button>
        `;

        require(`../../${GENERIC_FILE}`);
        document.dispatchEvent(new Event('DOMContentLoaded'));

        document.getElementById('batch-submit-button-id').click();

        assert.strictEqual(mockSubmitForm.mock.callCount(), 0);
    });

    test('should not submit if form is missing', () => {
        document.body.innerHTML = `
            <button id="batch-submit-button-id" data-submit-task="cwmserie.batch">
                Process
            </button>
        `;

        require(`../../${GENERIC_FILE}`);
        document.dispatchEvent(new Event('DOMContentLoaded'));

        document.getElementById('batch-submit-button-id').click();

        assert.strictEqual(mockSubmitForm.mock.callCount(), 0);
    });

    test('should handle missing button gracefully', () => {
        document.body.innerHTML = '<form id="adminForm"></form>';

        assert.doesNotThrow(() => {
            require(`../../${GENERIC_FILE}`);
            document.dispatchEvent(new Event('DOMContentLoaded'));
        });
    });
});
