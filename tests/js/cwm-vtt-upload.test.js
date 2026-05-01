/**
 * Tests for cwm-vtt-upload.es6.js
 * VTT/SRT caption file upload handler
 *
 * @package  Proclaim.Tests
 * @since    10.2.0
 */

// Override Joomla.getOptions for this module
global.Joomla = {
    ...global.Joomla,
    getOptions: jest.fn((key) => {
        if (key === 'com_proclaim.vttUpload') {
            return {
                uploadUrl: '/index.php?task=cwmmediafile.uploadVtt',
            };
        }
        return undefined;
    }),
    Text: { _: jest.fn((key) => key) },
    renderMessages: jest.fn(),
};

describe('cwm-vtt-upload', () => {
    beforeEach(() => {
        jest.resetModules();
        global.Joomla.renderMessages.mockClear();

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
        const clickSpy = jest.spyOn(fileInput, 'click');

        document.querySelector('.cwm-vtt-upload-btn').click();

        expect(clickSpy).toHaveBeenCalled();
    });

    test('uploading a file sends FormData to uploadUrl', async () => {
        const mockUrl = '/media/vtt/test-en.vtt';
        global.fetch = jest.fn(() =>
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

        expect(global.fetch).toHaveBeenCalledWith(
            '/index.php?task=cwmmediafile.uploadVtt',
            expect.objectContaining({ method: 'POST' }),
        );

        // URL input should be updated with the returned URL
        expect(urlInput.value).toBe(mockUrl);
    });

    test('shows error message on upload failure', async () => {
        global.fetch = jest.fn(() =>
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

        expect(global.Joomla.renderMessages).toHaveBeenCalledWith({
            error: ['Invalid file type'],
        });
    });

    test('shows error on network failure', async () => {
        global.fetch = jest.fn(() => Promise.reject(new Error('Network error')));

        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        const file = new File(['WEBVTT'], 'test.vtt', { type: 'text/vtt' });
        Object.defineProperty(fileInput, 'files', { value: [file] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        await new Promise((r) => setTimeout(r, 0));

        expect(global.Joomla.renderMessages).toHaveBeenCalledWith({
            error: [expect.any(String)],
        });
    });

    test('disables button during upload and re-enables after', async () => {
        global.fetch = jest.fn(
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
        expect(btn.disabled).toBe(true);

        // Wait for upload to complete
        await new Promise((r) => setTimeout(r, 100));

        expect(btn.disabled).toBe(false);
    });

    test('does nothing when no file is selected', () => {
        require('../../build/media_source/js/cwm-vtt-upload.es6.js');

        const fileInput = document.querySelector('.cwm-vtt-file-input');
        Object.defineProperty(fileInput, 'files', { value: [] });

        fileInput.dispatchEvent(new Event('change', { bubbles: true }));

        expect(global.fetch).not.toHaveBeenCalled();
    });

    test('resets file input value after upload', async () => {
        global.fetch = jest.fn(() =>
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

        expect(fileInput.value).toBe('');
    });
});