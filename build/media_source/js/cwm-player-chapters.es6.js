/**
 * Player chapters and timestamp seek handler
 *
 * Renders clickable chapter lists from `data-chapters` attributes and handles
 * seek for both YouTube iframe and HTML5 video/audio players. Also handles
 * delegated clicks on `.cwm-timestamp` links in study text.
 *
 * @package  Proclaim
 * @since    10.2.0
 */
(() => {
    'use strict';

    /**
     * Seek a player to the given time in seconds.
     *
     * @param {HTMLElement} container  The wrapper element containing the player
     * @param {number}      seconds   Time to seek to
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
     * Update active chapter highlight based on current playback time.
     *
     * @param {HTMLElement}  chapterList  The chapter list container
     * @param {Array}        chapters     Parsed chapter data
     * @param {number}       currentTime  Current playback time in seconds
     */
    function updateActiveChapter(chapterList, chapters, currentTime) {
        const items = chapterList.querySelectorAll('.cwm-chapter-item');
        let activeIndex = -1;

        for (let i = chapters.length - 1; i >= 0; i -= 1) {
            if (currentTime >= chapters[i].seconds) {
                activeIndex = i;
                break;
            }
        }

        items.forEach((item, idx) => {
            item.classList.toggle('active', idx === activeIndex);
        });
    }

    // Auto-render chapter lists from data-chapters attributes
    document.querySelectorAll('[data-chapters]').forEach((el) => {
        let chapters;

        try {
            chapters = JSON.parse(el.dataset.chapters);
        } catch {
            return;
        }

        if (!chapters || !chapters.length) {
            return;
        }

        const list = document.createElement('div');
        list.className = 'cwm-chapter-list';

        chapters.forEach((ch) => {
            const btn = document.createElement('button');
            btn.className = 'cwm-chapter-item';
            btn.type = 'button';
            btn.dataset.seconds = ch.seconds;
            btn.innerHTML = '<span class="cwm-chapter-time">'
                + ch.time + '</span> <span class="cwm-chapter-label">'
                + ch.label + '</span>';
            btn.addEventListener('click', () => seekTo(el, ch.seconds));
            list.appendChild(btn);
        });

        el.after(list);

        // Track active chapter during HTML5 media playback
        const media = el.querySelector('video, audio');

        if (media) {
            media.addEventListener('timeupdate', () => {
                updateActiveChapter(list, chapters, media.currentTime);
            });
        }
    });

    // Delegated click handler for .cwm-timestamp links in study text
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.cwm-timestamp');

        if (!link) {
            return;
        }

        e.preventDefault();
        const seconds = parseInt(link.dataset.seconds, 10);

        if (Number.isNaN(seconds)) {
            return;
        }

        // Find the nearest player container on the page
        const playerContainer = document.querySelector('[data-chapters], .media.playhit');

        if (playerContainer) {
            seekTo(playerContainer, seconds);
        } else {
            // Fallback: try YouTube iframe directly
            const iframe = document.querySelector('iframe[src*="youtube.com"]');

            if (iframe) {
                iframe.contentWindow.postMessage(JSON.stringify({
                    event: 'command',
                    func: 'seekTo',
                    args: [seconds, true],
                }), '*');
            }
        }
    });
})();
