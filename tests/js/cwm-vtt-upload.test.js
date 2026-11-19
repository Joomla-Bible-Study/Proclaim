'use strict';

/**
 * Tests for cwm-vtt-upload.es6.js
 * VTT/SRT caption file upload handler
 *
 * @package  Proclaim.Tests
 * @since    10.2.0
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach } = require('node:test');
const { fn, spyOn, resetModules } = require('./helpers/mocks');
const { calledWith, match } = require('./helpers/assert');

// Override Joomla.getOptions for this module
global.Joomla = {
    ...global.Joomla,
    getOptions: fn((key) => {
        if (key === 'com_proclaim.vttUpload') {
            return {
                uploadUrl: '/index.php?task=cwmmediafile.uploadVtt',
            };
        }
        return undefined;
    }),
    Text: { _: fn((key) => key) },
    renderMessages: fn(),
};

describe('cwm-vtt-upload', () => {
    beforeEach(() => {
        resetModules();
        global.Joomla.renderMessages.mock.resetCalls();

        document.body.innerHTML = `
            <div class="cwm-vtt-field">
                <div class="input-group">
                    <input type="url" class="cwm-vtt-url" value="">
                    <button type="button" class="cwm-vtt-upload-btn">Browse</button>
                </div>
                <input type="file" class="cwm-vtt-file-input" style="display:none">
            </div>
        `;
    });

    test('clicking Browse triggers the hidden file input', () => {
        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const clickSpy = spyOn(fileInput, 'click');

        document.querySelector('.cwm-vtt-upload-btn').click();

        assert.ok(clickSpy.mock.callCount() > 0);
    });

    test('uploading a file sends FormData to uploadUrl', async () => {
        const mockUrl = '/media/vtt/test-en.vtt';
        global.fetch = fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: true, url: mockUrl }),
            }),
        );

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const urlInput = document.querySelector('.cwm-vtt-url');

        // Create a mock file and set it on the file input
        const file = new File(['WEBVTT\n\n00:00.000 --> 00:01.000\nTest'], 'test.vtt', {
            type: 'text/vtt',
        });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        // Trigger change event
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));

        // Wait for async fetch
        await new Promise((r) => setTimeout(r, 0));

        calledWith(
            global.fetch,
            '/index.php?task=cwmmediafile.uploadVtt',
            match.objectContaining({ method: 'POST' }),
        );

        // URL input should be updated with the returned URL
        assert.strictEqual(urlInput.value, mockUrl);
    });

    test('shows error message on upload failure', async () => {
        global.fetch = fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: false, error: 'Invalid file type' }),
            }),
        );

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const file = new File(['bad'], 'test.txt', { type: 'text/plain' });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        await new Promise((r) => setTimeout(r, 0));

        calledWith(global.Joomla.renderMessages, {
            error: ['Invalid file type'],
        });
    });

    test('shows error on network failure', async () => {
        global.fetch = fn(() => Promise.reject(new Error('Network error')));

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const file = new File(['WEBVTT'], 'test.vtt', { type: 'text/vtt' });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        await new Promise((r) => setTimeout(r, 0));

        calledWith(global.Joomla.renderMessages, {
            error: [match.any(String)],
        });
    });

    test('disables button during upload and re-enables after', async () => {
        global.fetch = fn(
            () => new Promise((resolve) => setTimeout(
                () => resolve({
                    ok: true,
                    json: () => Promise.resolve({ success: true, url: '/test.vtt' }),
                }),
                50,
            )),
        );

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const btn = document.querySelector('.cwm-vtt-upload-btn');
        const file = new File(['WEBVTT'], 'test.vtt', { type: 'text/vtt' });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));

        // Button should be disabled during upload
        assert.strictEqual(btn.disabled, true);

        // Wait for upload to complete
        await new Promise((r) => setTimeout(r, 100));

        assert.strictEqual(btn.disabled, false);
    });

    test('does nothing when no file is selected', () => {
        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        Object.defineProperty(fileInput, 'files', { value: [] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));

        assert.strictEqual(global.fetch.mock.callCount(), 0);
    });

    test('resets file input value after upload', async () => {
        global.fetch = fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: true, url: '/test.vtt' }),
            }),
        );

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const file = new File(['WEBVTT'], 'test.vtt', { type: 'text/vtt' });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        await new Promise((r) => setTimeout(r, 0));

        assert.strictEqual(fileInput.value, '');
    });
});