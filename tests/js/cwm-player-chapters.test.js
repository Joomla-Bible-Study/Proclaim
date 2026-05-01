/**
 * Tests for cwm-player-chapters.es6.js
 * Player chapters and timestamp seek handler
 *
 * @package  Proclaim.Tests
 * @since    10.2.0
 */

describe('cwm-player-chapters', () => {
    beforeEach(() => {
        jest.resetModules();
        document.body.innerHTML = '';
    });

    describe('chapter list rendering', () => {
        test('renders chapter buttons from data-chapters attribute', () => {
            const chapters = [
                { time: '0:00', label: 'Introduction', seconds: 0 },
                { time: '3:45', label: 'Main Point', seconds: 225 },
                { time: '10:30', label: 'Conclusion', seconds: 630 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <video src="test.mp4"></video>
                </div>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const list = document.querySelector('.cwm-chapter-list');
            expect(list).not.toBeNull();

            const items = list.querySelectorAll('.cwm-chapter-item');
            expect(items.length).toBe(3);
            expect(items[0].querySelector('.cwm-chapter-time').textContent).toBe('0:00');
            expect(items[0].querySelector('.cwm-chapter-label').textContent).toBe('Introduction');
            expect(items[1].querySelector('.cwm-chapter-time').textContent).toBe('3:45');
            expect(items[2].dataset.seconds).toBe('630');
        });

        test('skips rendering when data-chapters is invalid JSON', () => {
            document.body.innerHTML = '<div data-chapters="not valid json"></div>';

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            expect(document.querySelector('.cwm-chapter-list')).toBeNull();
        });

        test('skips rendering when chapters array is empty', () => {
            document.body.innerHTML = '<div data-chapters="[]"></div>';

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            expect(document.querySelector('.cwm-chapter-list')).toBeNull();
        });

        test('does nothing when no data-chapters elements exist', () => {
            document.body.innerHTML = '<div>No chapters here</div>';

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            expect(document.querySelector('.cwm-chapter-list')).toBeNull();
        });
    });

    describe('HTML5 media seek', () => {
        test('clicking a chapter button sets currentTime on video element', () => {
            const chapters = [
                { time: '0:00', label: 'Intro', seconds: 0 },
                { time: '5:00', label: 'Middle', seconds: 300 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <video src="test.mp4"></video>
                </div>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const video = document.querySelector('video');
            const items = document.querySelectorAll('.cwm-chapter-item');
            items[1].click();

            expect(video.currentTime).toBe(300);
            expect(window.HTMLMediaElement.prototype.play).toHaveBeenCalled();
        });

        test('clicking a chapter button sets currentTime on audio element', () => {
            const chapters = [
                { time: '0:00', label: 'Start', seconds: 0 },
                { time: '2:30', label: 'Song', seconds: 150 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <audio src="test.mp3"></audio>
                </div>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const audio = document.querySelector('audio');
            const items = document.querySelectorAll('.cwm-chapter-item');
            items[1].click();

            expect(audio.currentTime).toBe(150);
        });
    });

    describe('YouTube iframe seek', () => {
        test('clicking a chapter button posts seekTo message to YouTube iframe', () => {
            const chapters = [
                { time: '0:00', label: 'Start', seconds: 0 },
                { time: '1:00', label: 'Next', seconds: 60 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <iframe src="https://www.youtube.com/embed/abc123"></iframe>
                </div>
            `;

            // Mock iframe contentWindow.postMessage
            const mockPostMessage = jest.fn();
            const iframe = document.querySelector('iframe');
            Object.defineProperty(iframe, 'contentWindow', {
                value: { postMessage: mockPostMessage },
            });

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const items = document.querySelectorAll('.cwm-chapter-item');
            items[1].click();

            expect(mockPostMessage).toHaveBeenCalledWith(
                JSON.stringify({
                    event: 'command',
                    func: 'seekTo',
                    args: [60, true],
                }),
                '*',
            );
        });
    });

    describe('active chapter tracking', () => {
        test('updates active class during HTML5 media timeupdate', () => {
            const chapters = [
                { time: '0:00', label: 'Part 1', seconds: 0 },
                { time: '5:00', label: 'Part 2', seconds: 300 },
                { time: '10:00', label: 'Part 3', seconds: 600 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <video src="test.mp4"></video>
                </div>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const video = document.querySelector('video');
            const items = document.querySelectorAll('.cwm-chapter-item');

            // Simulate playback at 4 minutes (should highlight Part 1)
            Object.defineProperty(video, 'currentTime', { value: 240, writable: true });
            video.dispatchEvent(new Event('timeupdate'));

            expect(items[0].classList.contains('active')).toBe(true);
            expect(items[1].classList.contains('active')).toBe(false);

            // Simulate playback at 7 minutes (should highlight Part 2)
            Object.defineProperty(video, 'currentTime', { value: 420, writable: true });
            video.dispatchEvent(new Event('timeupdate'));

            expect(items[0].classList.contains('active')).toBe(false);
            expect(items[1].classList.contains('active')).toBe(true);
            expect(items[2].classList.contains('active')).toBe(false);
        });
    });

    describe('delegated .cwm-timestamp click handler', () => {
        test('seeks player when a .cwm-timestamp link is clicked', () => {
            const chapters = [
                { time: '0:00', label: 'Start', seconds: 0 },
            ];

            document.body.innerHTML = `
                <div data-chapters='${JSON.stringify(chapters)}'>
                    <audio src="test.mp3"></audio>
                </div>
                <p>Jump to <a class="cwm-timestamp" data-seconds="90" href="#">1:30</a></p>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const link = document.querySelector('.cwm-timestamp');
            link.click();

            const audio = document.querySelector('audio');
            expect(audio.currentTime).toBe(90);
        });

        test('ignores .cwm-timestamp with non-numeric seconds', () => {
            document.body.innerHTML = `
                <div class="media playhit">
                    <audio src="test.mp3"></audio>
                </div>
                <a class="cwm-timestamp" data-seconds="abc" href="#">bad</a>
            `;

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            const audio = document.querySelector('audio');
            const link = document.querySelector('.cwm-timestamp');
            link.click();

            // Should not have changed currentTime
            expect(audio.currentTime).toBe(0);
        });

        test('falls back to direct YouTube iframe when no player container', () => {
            document.body.innerHTML = `
                <iframe src="https://www.youtube.com/embed/xyz"></iframe>
                <a class="cwm-timestamp" data-seconds="120" href="#">2:00</a>
            `;

            const mockPostMessage = jest.fn();
            const iframe = document.querySelector('iframe');
            Object.defineProperty(iframe, 'contentWindow', {
                value: { postMessage: mockPostMessage },
            });

            require('../../build/media_source/js/cwm-player-chapters.es6.js');

            document.querySelector('.cwm-timestamp').click();

            expect(mockPostMessage).toHaveBeenCalledWith(
                expect.stringContaining('"func":"seekTo"'),
                '*',
            );
        });
    });
});