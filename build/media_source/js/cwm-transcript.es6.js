/**
 * Interactive transcript panel — parses VTT files and renders a scrollable,
 * clickable, searchable transcript synced to media playback.
 *
 * Looks for elements with `data-transcript-src` (VTT URL) and a sibling
 * media player (video/audio or YouTube iframe) to bind to.
 *
 * @package  Proclaim
 * @since    10.3.0
 */
(() => {
    'use strict';

    /**
     * Parse a VTT timestamp (HH:MM:SS.mmm or MM:SS.mmm) into seconds.
     *
     * @param {string} timestamp  VTT timestamp string
     * @returns {number}  Time in seconds
     */
    function parseVttTime(timestamp) {
        const parts = timestamp.trim().split(':');
        let seconds = 0;

        if (parts.length === 3) {
            seconds += parseFloat(parts[0]) * 3600;
            seconds += parseFloat(parts[1]) * 60;
            seconds += parseFloat(parts[2]);
        } else if (parts.length === 2) {
            seconds += parseFloat(parts[0]) * 60;
            seconds += parseFloat(parts[1]);
        }

        return seconds;
    }

    /**
     * Format seconds as MM:SS for display.
     *
     * @param {number} seconds
     * @returns {string}
     */
    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);

        return m + ':' + String(s).padStart(2, '0');
    }

    /**
     * Parse a WebVTT file string into an array of cue objects.
     *
     * @param {string} vttText  Raw VTT file content
     * @returns {Array<{start: number, end: number, text: string}>}
     */
    function parseVtt(vttText) {
        const cues = [];
        // Normalize line endings and split into blocks
        const blocks = vttText.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n\n');

        for (const block of blocks) {
            const lines = block.trim().split('\n');

            // Find the timestamp line (contains ' --> ')
            let tsIndex = -1;

            for (let i = 0; i < lines.length; i += 1) {
                if (lines[i].includes(' --> ')) {
                    tsIndex = i;
                    break;
                }
            }

            if (tsIndex === -1) {
                continue;
            }

            const tsParts = lines[tsIndex].split(' --> ');

            if (tsParts.length < 2) {
                continue;
            }

            // Strip position/alignment settings from end timestamp
            const endRaw = tsParts[1].split(/\s/)[0];
            const start = parseVttTime(tsParts[0]);
            const end = parseVttTime(endRaw);

            // Text is everything after the timestamp line, strip VTT tags
            const text = lines.slice(tsIndex + 1).join(' ')
                .replace(/<[^>]+>/g, '')
                .trim();

            if (text) {
                cues.push({ start, end, text });
            }
        }

        return cues;
    }

    /**
     * Seek a media element or YouTube iframe to the given time.
     *
     * @param {HTMLElement} container  Player wrapper element
     * @param {number}      seconds   Target time
     */
    function seekTo(container, seconds) {
        const iframe = container.querySelector('iframe[src*="youtube.com"]');

        if (iframe) {
            iframe.contentWindow.postMessage(JSON.stringify({
                event: 'command',
                func: 'seekTo',
                args: [seconds, true],
            }), '*');

            return;
        }

        const media = container.querySelector('video, audio');

        if (media) {
            media.currentTime = seconds;
            media.play();
        }
    }

    /**
     * Build and attach the interactive transcript panel for a player.
     *
     * @param {HTMLElement} wrapper    Element with data-transcript-src
     * @param {Array}       cues       Parsed VTT cues
     * @param {HTMLElement} playerEl   The player container (for seek)
     */
    function buildTranscriptPanel(wrapper, cues, playerEl) {
        const panel = document.createElement('div');
        panel.className = 'cwm-transcript-panel';

        // Search bar
        const searchWrap = document.createElement('div');
        searchWrap.className = 'cwm-transcript-search';

        const searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'form-control form-control-sm';
        searchInput.placeholder = Joomla.Text._('JBS_MED_TRANSCRIPT_SEARCH') || 'Search transcript...';
        searchInput.setAttribute('aria-label', searchInput.placeholder);
        searchWrap.appendChild(searchInput);
        panel.appendChild(searchWrap);

        // Cue list
        const listEl = document.createElement('div');
        listEl.className = 'cwm-transcript-cues';
        listEl.setAttribute('role', 'list');

        cues.forEach((cue, idx) => {
            const cueEl = document.createElement('div');
            cueEl.className = 'cwm-transcript-cue';
            cueEl.setAttribute('role', 'listitem');
            cueEl.dataset.index = idx;
            cueEl.dataset.start = cue.start;
            cueEl.dataset.end = cue.end;
            cueEl.innerHTML = '<span class="cwm-transcript-time">' + formatTime(cue.start)
                + '</span> <span class="cwm-transcript-text">' + cue.text + '</span>';

            cueEl.addEventListener('click', () => {
                seekTo(playerEl, cue.start);
            });

            listEl.appendChild(cueEl);
        });

        panel.appendChild(listEl);
        wrapper.appendChild(panel);

        // Active cue tracking via HTML5 timeupdate
        const media = playerEl.querySelector('video, audio');

        if (media) {
            let lastActive = -1;

            media.addEventListener('timeupdate', () => {
                const t = media.currentTime;
                let activeIdx = -1;

                for (let i = cues.length - 1; i >= 0; i -= 1) {
                    if (t >= cues[i].start && t < cues[i].end) {
                        activeIdx = i;
                        break;
                    }
                }

                if (activeIdx === lastActive) {
                    return;
                }

                lastActive = activeIdx;
                const items = listEl.querySelectorAll('.cwm-transcript-cue');

                items.forEach((item, idx) => {
                    item.classList.toggle('active', idx === activeIdx);
                });

                // Auto-scroll to keep active cue visible
                if (activeIdx >= 0 && items[activeIdx]) {
                    items[activeIdx].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                }
            });
        }

        // Search/filter
        let debounceTimer;

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                const query = searchInput.value.toLowerCase().trim();
                const items = listEl.querySelectorAll('.cwm-transcript-cue');

                items.forEach((item) => {
                    const textEl = item.querySelector('.cwm-transcript-text');
                    const original = cues[parseInt(item.dataset.index, 10)].text;

                    if (!query) {
                        item.style.display = '';
                        textEl.innerHTML = original;

                        return;
                    }

                    const lower = original.toLowerCase();

                    if (lower.includes(query)) {
                        item.style.display = '';
                        // Highlight matches
                        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                        textEl.innerHTML = original.replace(regex, '<mark>$1</mark>');
                    } else {
                        item.style.display = 'none';
                    }
                });
            }, 200);
        });
    }

    // Initialize all transcript panels on the page
    document.querySelectorAll('[data-transcript-src]').forEach((wrapper) => {
        const vttUrl = wrapper.dataset.transcriptSrc;

        if (!vttUrl) {
            return;
        }

        // Find the player container — look for sibling or parent with media/iframe
        const playerEl = wrapper.closest('.media.playhit')
            || wrapper.previousElementSibling
            || wrapper.parentElement.querySelector('.media.playhit, [data-chapters]');

        if (!playerEl) {
            return;
        }

        fetch(vttUrl)
            .then((r) => {
                if (!r.ok) {
                    return '';
                }

                return r.text();
            })
            .then((text) => {
                if (!text || !text.includes('WEBVTT')) {
                    return;
                }

                const cues = parseVtt(text);

                if (cues.length > 0) {
                    buildTranscriptPanel(wrapper, cues, playerEl);
                }
            })
            .catch(() => { /* VTT fetch failed — transcript not shown */ });
    });
})();
