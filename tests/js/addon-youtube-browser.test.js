/**
 * Tests for addon-youtube-browser.es6.js
 * YouTube Video Browser functionality
 */

describe('addon-youtube-browser.es6.js', () => {
    describe('YoutubeBrowser Module', () => {
        beforeEach(() => {
            // Set up DOM
            document.body.innerHTML = `
                <input name="jform[server_id]" value="123" />
                <input id="jform_study_id_name" value="" />
                <input name="jform[params][filename]" value="" />
            `;

            // Load the module
            require('../../build/media_source/js/addon-youtube-browser.es6.js');
        });

        afterEach(() => {
            // Clean up
            delete window.Proclaim;
            jest.resetModules();
        });

        test('should create Proclaim.YoutubeBrowser object', () => {
            expect(window.Proclaim).toBeDefined();
            expect(window.Proclaim.YoutubeBrowser).toBeDefined();
        });

        test('should have required methods', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            expect(typeof browser.init).toBe('function');
            expect(typeof browser.createModal).toBe('function');
            expect(typeof browser.bindEvents).toBe('function');
            expect(typeof browser.open).toBe('function');
            expect(typeof browser.loadVideos).toBe('function');
            expect(typeof browser.selectVideo).toBe('function');
        });

        test('should escape HTML properly', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            expect(browser.escapeHtml('<script>alert("xss")</script>')).toBe(
                '&lt;script&gt;alert("xss")&lt;/script&gt;'
            );
        });

        test('should format dates', () => {
            const browser = window.Proclaim.YoutubeBrowser;
            expect(browser.formatDate('')).toBe('');
            expect(browser.formatDate(null)).toBe('');
            // Valid date should return formatted string
            const result = browser.formatDate('2024-01-15T10:30:00Z');
            expect(result).toBeTruthy();
        });

        test('should alert if no server selected when opening', () => {
            document.querySelector('[name="jform[server_id]"]').value = '';
            const browser = window.Proclaim.YoutubeBrowser;
            browser.open();
            expect(global.alert).toHaveBeenCalledWith('Please select a server first.');
        });
    });
});
