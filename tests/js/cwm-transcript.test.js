/**
 * Tests for cwm-transcript.es6.js
 * Interactive transcript panel — VTT parsing, rendering, seek, search
 *
 * @package  Proclaim.Tests
 * @since    10.3.0
 */

describe('cwm-transcript', () => {
    beforeEach(() => {
        jest.resetModules();
        document.body.innerHTML = '';

        // Ensure Joomla.Text._ is available (setup.js only defines JText)
        global.Joomla = {
            ...global.Joomla,
            Text: { _: jest.fn((key) => key) },
        };

        // jsdom doesn't implement scrollIntoView
        Element.prototype.scrollIntoView = jest.fn();
    });

    // Sample VTT content for testing
    const sampleVtt = `WEBVTT

1
00:00:00.000 --> 00:00:05.000
Welcome to today's message.

2
00:00:05.000 --> 00:00:12.000
We're going to be looking at Romans chapter 8.

3
00:00:12.000 --> 00:00:20.000
This is one of the most encouraging passages in all of Scripture.`;

    /**
     * Flush microtasks so fetch().then().then() chains resolve.
     * The transcript module uses fetch().then(r.text()).then(parse+render),
     * so we need to flush multiple microtask levels.
     */
    async function flush() {
        for (let i = 0; i < 20; i += 1) {
            await Promise.resolve();
        }
        await new Promise((r) => setTimeout(r, 100));
        for (let i = 0; i < 10; i += 1) {
            await Promise.resolve();
        }
    }

    function setupWithVtt(vttContent) {
        // Mock fetch to return the VTT content
        global.fetch = jest.fn(() =>
            Promise.resolve({
                ok: true,
                text: () => Promise.resolve(vttContent),
            }),
        );

        document.body.innerHTML = `
            <div class="media playhit" data-id="1">
                <audio src="test.mp3" controls></audio>
            </div>
            <div data-transcript-src="/media/captions/test.vtt"></div>
        `;

        require('../../build/media_source/js/cwm-transcript.es6.js');
    }

    describe('VTT parsing', () => {
        test('parses VTT cues and renders transcript panel', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const panel = document.querySelector('.cwm-transcript-panel');
            expect(panel).not.toBeNull();

            const cues = panel.querySelectorAll('.cwm-transcript-cue');
            expect(cues.length).toBe(3);
        });

        test('renders timestamps in MM:SS format', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const times = document.querySelectorAll('.cwm-transcript-time');
            expect(times[0].textContent).toBe('0:00');
            expect(times[1].textContent).toBe('0:05');
            expect(times[2].textContent).toBe('0:12');
        });

        test('renders cue text content', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const texts = document.querySelectorAll('.cwm-transcript-text');
            expect(texts[0].textContent).toContain('Welcome');
            expect(texts[1].textContent).toContain('Romans chapter 8');
        });

        test('does not render panel when VTT fetch fails', async () => {
            global.fetch = jest.fn(() => Promise.resolve({ ok: false, text: () => Promise.resolve('') }));

            document.body.innerHTML = `
                <div class="media playhit" data-id="1"><audio src="t.mp3"></audio></div>
                <div data-transcript-src="/bad.vtt"></div>
            `;

            require('../../build/media_source/js/cwm-transcript.es6.js');
            await flush();

            expect(document.querySelector('.cwm-transcript-panel')).toBeNull();
        });

        test('does not render panel for non-VTT content', async () => {
            global.fetch = jest.fn(() =>
                Promise.resolve({ ok: true, text: () => Promise.resolve('Not a VTT file') }),
            );

            document.body.innerHTML = `
                <div class="media playhit" data-id="1"><audio src="t.mp3"></audio></div>
                <div data-transcript-src="/bad.txt"></div>
            `;

            require('../../build/media_source/js/cwm-transcript.es6.js');
            await flush();

            expect(document.querySelector('.cwm-transcript-panel')).toBeNull();
        });

        test('handles VTT with hour timestamps', async () => {
            const vttWithHours = `WEBVTT

1
01:15:30.000 --> 01:16:00.000
Late in the sermon.`;

            setupWithVtt(vttWithHours);
            await flush();

            const time = document.querySelector('.cwm-transcript-time');
            expect(time.textContent).toBe('75:30');
        });

        test('strips VTT formatting tags from text', async () => {
            const vttWithTags = `WEBVTT

1
00:00:00.000 --> 00:00:05.000
This has <b>bold</b> and <i>italic</i> tags.`;

            setupWithVtt(vttWithTags);
            await flush();

            const text = document.querySelector('.cwm-transcript-text');
            expect(text.textContent).toBe('This has bold and italic tags.');
        });
    });

    describe('click to seek', () => {
        test('clicking a cue sets currentTime on audio element', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const cues = document.querySelectorAll('.cwm-transcript-cue');
            cues[1].click();

            const audio = document.querySelector('audio');
            expect(audio.currentTime).toBe(5);
            expect(window.HTMLMediaElement.prototype.play).toHaveBeenCalled();
        });
    });

    describe('active cue tracking', () => {
        test('highlights active cue during playback', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const audio = document.querySelector('audio');
            const cues = document.querySelectorAll('.cwm-transcript-cue');

            // Simulate playback at 7 seconds (within cue 2: 5-12s)
            Object.defineProperty(audio, 'currentTime', { value: 7, writable: true });
            audio.dispatchEvent(new Event('timeupdate'));

            expect(cues[0].classList.contains('active')).toBe(false);
            expect(cues[1].classList.contains('active')).toBe(true);
            expect(cues[2].classList.contains('active')).toBe(false);
        });
    });

    describe('search/filter', () => {
        test('filters cues by search text', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const searchInput = document.querySelector('.cwm-transcript-search input');
            searchInput.value = 'romans';
            searchInput.dispatchEvent(new Event('input'));

            // Wait for debounce
            await new Promise((r) => setTimeout(r, 300));

            const cues = document.querySelectorAll('.cwm-transcript-cue');
            // Cue 2 contains "Romans" — should be visible
            expect(cues[1].style.display).toBe('');
            // Others should be hidden
            expect(cues[0].style.display).toBe('none');
            expect(cues[2].style.display).toBe('none');
        });

        test('highlights matching text with <mark> tags', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const searchInput = document.querySelector('.cwm-transcript-search input');
            searchInput.value = 'romans';
            searchInput.dispatchEvent(new Event('input'));
            await new Promise((r) => setTimeout(r, 300));

            const text = document.querySelectorAll('.cwm-transcript-text')[1];
            expect(text.innerHTML).toContain('<mark>');
            expect(text.innerHTML).toContain('Romans');
        });

        test('clears filter when search is emptied', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const searchInput = document.querySelector('.cwm-transcript-search input');

            // First filter
            searchInput.value = 'romans';
            searchInput.dispatchEvent(new Event('input'));
            await new Promise((r) => setTimeout(r, 300));

            // Then clear
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            await new Promise((r) => setTimeout(r, 300));

            const cues = document.querySelectorAll('.cwm-transcript-cue');
            cues.forEach((cue) => {
                expect(cue.style.display).toBe('');
            });
        });
    });

    describe('no player found', () => {
        test('does not render when no sibling player exists', async () => {
            global.fetch = jest.fn(() =>
                Promise.resolve({ ok: true, text: () => Promise.resolve(sampleVtt) }),
            );

            document.body.innerHTML = '<div data-transcript-src="/test.vtt"></div>';

            require('../../build/media_source/js/cwm-transcript.es6.js');
            await flush();

            expect(document.querySelector('.cwm-transcript-panel')).toBeNull();
        });
    });

    describe('accessibility', () => {
        test('cue list has role="list" and items have role="listitem"', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            expect(document.querySelector('.cwm-transcript-cues').getAttribute('role')).toBe('list');
            document.querySelectorAll('.cwm-transcript-cue').forEach((cue) => {
                expect(cue.getAttribute('role')).toBe('listitem');
            });
        });

        test('search input has aria-label', async () => {
            setupWithVtt(sampleVtt);
            await flush();

            const input = document.querySelector('.cwm-transcript-search input');
            expect(input.getAttribute('aria-label')).toBeTruthy();
        });
    });
});
