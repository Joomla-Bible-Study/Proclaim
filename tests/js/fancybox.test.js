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
                show: jest.fn(),
                bind: jest.fn()
            };
            global.Fancybox = mockFancybox;

            // Set up DOM with fancybox player elements
            document.body.innerHTML = `
                <div class="fancybox_player"
                    data-src="https://www.youtube.com/embed/abc123"
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

        test('should call Fancybox.bind for fancybox_player elements', () => {
            expect(mockFancybox.bind).toHaveBeenCalledWith(
                '.fancybox_player',
                expect.objectContaining({
                    backdropClick: 'close'
                })
            );
        });

        test('should bind with Carousel and Toolbar options', () => {
            const bindOptions = mockFancybox.bind.mock.calls[0][1];
            expect(bindOptions.Carousel).toEqual({ infinite: false });
            expect(bindOptions.Toolbar.display.right).toContain('close');
        });

        test('should open audio overlay on audio element click', () => {
            const audioPlayer = document.querySelector('.fancybox_player[data-src*=".mp3"]');
            audioPlayer.click();

            // Audio handler creates a custom overlay, not Fancybox
            const overlay = document.querySelector('.proclaim-audio-overlay');
            expect(overlay).not.toBeNull();
            expect(overlay.querySelector('audio')).not.toBeNull();
        });

        test('should close audio overlay on Escape key', () => {
            const audioPlayer = document.querySelector('.fancybox_player[data-src*=".mp3"]');
            audioPlayer.click();

            const overlay = document.querySelector('.proclaim-audio-overlay');
            expect(overlay).not.toBeNull();

            // Simulate Escape key
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

            expect(document.querySelector('.proclaim-audio-overlay')).toBeNull();
        });

        test('should not open audio overlay for non-audio elements', () => {
            const videoPlayer = document.querySelector('.fancybox_player[data-src*=".mp4"]');
            videoPlayer.click();

            // No custom audio overlay should exist
            expect(document.querySelector('.proclaim-audio-overlay')).toBeNull();
        });
    });
});
