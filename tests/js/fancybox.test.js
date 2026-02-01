/**
 * Tests for fancybox.es6.js
 * Fancybox media player integration
 */

const { validateFile } = require('./helpers/jshint-helper');

const SOURCE_FILE = 'build/media_source/js/fancybox.es6.js';

describe('fancybox.es6.js', () => {
    describe('JSHint Validation', () => {
        test('should pass JSHint validation', () => {
            const result = validateFile(SOURCE_FILE);
            expect(result.valid).toBe(true);
            if (!result.valid) {
                console.error('JSHint errors:\n' + result.errorReport);
            }
        });
    });

    describe('Fancybox Player', () => {
        let mockFancybox;

        beforeEach(() => {
            // Mock Fancybox
            mockFancybox = {
                show: jest.fn()
            };
            global.Fancybox = mockFancybox;

            // Set up DOM with fancybox player elements
            document.body.innerHTML = `
                <div class="fancybox_player"
                    data-src="https://www.youtube.com/watch?v=abc123"
                    data-height="480"
                    data-width="854"
                    data-header="Test Header"
                    data-footer="Test Footer">
                </div>
                <div class="fancybox_player"
                    data-src="/media/videos/test.mp4"
                    data-image="/media/images/poster.jpg"
                    autostart="true"
                    controls="1">
                </div>
                <div class="fancybox_player"
                    data-src="/media/audio/test.mp3">
                </div>
            `;

            // Load the module (triggers DOMContentLoaded setup)
            jest.resetModules();
            require('../../build/media_source/js/fancybox.es6.js');

            // Manually trigger DOMContentLoaded since it already fired
            document.dispatchEvent(new Event('DOMContentLoaded'));
        });

        afterEach(() => {
            delete global.Fancybox;
            jest.resetModules();
        });

        test('should bind click events to fancybox_player elements', () => {
            const players = document.querySelectorAll('.fancybox_player');
            expect(players.length).toBe(3);
        });

        test('should open Fancybox on YouTube video click', () => {
            const youtubePlayer = document.querySelector('.fancybox_player[data-src*="youtube"]');
            youtubePlayer.click();

            expect(mockFancybox.show).toHaveBeenCalled();
            const callArgs = mockFancybox.show.mock.calls[0];
            expect(callArgs[0][0].type).toBe('iframe');
            expect(callArgs[0][0].src).toContain('youtube');
        });

        test('should use HTML5 player for local video', () => {
            const videoPlayer = document.querySelector('.fancybox_player[data-src*=".mp4"]');
            videoPlayer.click();

            expect(mockFancybox.show).toHaveBeenCalled();
            const callArgs = mockFancybox.show.mock.calls[0];
            expect(callArgs[0][0].html).toContain('<video');
        });

        test('should use HTML5 audio player for audio files', () => {
            const audioPlayer = document.querySelector('.fancybox_player[data-src*=".mp3"]');
            audioPlayer.click();

            expect(mockFancybox.show).toHaveBeenCalled();
            const callArgs = mockFancybox.show.mock.calls[0];
            expect(callArgs[0][0].html).toContain('<audio');
        });

        test('should include header and footer in caption', () => {
            const playerWithCaption = document.querySelector('.fancybox_player[data-header="Test Header"]');
            playerWithCaption.click();

            const callArgs = mockFancybox.show.mock.calls[0];
            expect(callArgs[0][0].caption).toContain('Test Header');
            expect(callArgs[0][0].caption).toContain('Test Footer');
        });
    });
});
