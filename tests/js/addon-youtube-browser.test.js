'use strict';

/**
 * Tests for addon-youtube-browser.es6.js
 * YouTube Video Browser functionality
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach } = require('node:test');
const { resetModules } = require('./helpers/mocks');
const { calledWith } = require('./helpers/assert');

describe('addon-youtube-browser.es6.js', () => {
    describe('YoutubeBrowser Module', () => {
        beforeEach(() => {
            // Set up DOM
            document.body.innerHTML = `
                <input name="jform[server_id]" value="123" />
                <!-- What Joomla 5 ModalSelectField actually renders: the readable
                     title on the field id, the value on the id suffixed _id. The
                     _name element that stood here is the Joomla 3/4 convention and
                     never appears on a real page - a fixture matching the code
                     rather than the CMS, which is how the empty-search bug
                     survived. See tests/js/modal-title-prefill.test.js -->
                <input class="js-input-title" id="jform_study_id" value="" />
                <input type="hidden" id="jform_study_id_id" value="" />
                <input name="jform[params][filename]" value="" />
            `;

            // Load the module
            require('../../build/media_source/js/addon-youtube-browser.es6.js');
        });

        afterEach(() => {
            // Clean up
            delete window.Proclaim;
            resetModules();
        });

        test('should create Proclaim.YoutubeBrowser object', () => {
            assert.notStrictEqual(window.Proclaim, undefined);
            assert.notStrictEqual(window.Proclaim.YoutubeBrowser, undefined);
        });

        test('should have required methods', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            assert.strictEqual(typeof browser.init, 'function');
            assert.strictEqual(typeof browser.createModal, 'function');
            assert.strictEqual(typeof browser.bindEvents, 'function');
            assert.strictEqual(typeof browser.open, 'function');
            assert.strictEqual(typeof browser.loadVideos, 'function');
            assert.strictEqual(typeof browser.selectVideo, 'function');
        });

        test('should escape HTML properly', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            assert.strictEqual(
                browser.escapeHtml('<script>alert("xss")</script>'),
                '&lt;script&gt;alert("xss")&lt;/script&gt;'
            );
        });

        test('should format dates', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            assert.strictEqual(browser.formatDate(''), '');
            assert.strictEqual(browser.formatDate(null), '');
            // Valid date should return formatted string
            const result = browser.formatDate('2024-01-15T10:30:00Z');
            assert.ok(result);
        });

        test('should alert if no server selected when opening', () => {
            document.querySelector('[name="jform[server_id]"]').value = '';
            const browser = window.Proclaim.YoutubeBrowser;
            browser.open();
            calledWith(window.cwmAlert, 'Please select a server first.');
        });
    });
});
