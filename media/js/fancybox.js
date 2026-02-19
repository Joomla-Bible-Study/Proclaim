(function () {
    'use strict';

    /**
     * @package     Proclaim.Fancybox
     * @subpackage  com_proclaim
     *
     * @copyright   Copyright (C) 2005-2026 CWM Team All rights reserved.
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     */
    /* jshint esversion: 11, browser: true, globalstrict: true */
    /* globals Fancybox, YT, Vimeo */


    /**
     * Send an AJAX play-hit request for the given media file ID.
     * Tracks each ID only once per page load to avoid duplicate counts.
     *
     * @param {number|string} mediaId  The media file ID to record
     */
    var proclaimTrackedIds = {};

    // Resolve the site root from Joomla's <base href> so the AJAX URL is always
    // root-relative, even on multilingual sites where pages live under /en/, /es/, etc.
    // Falls back to '/' if no <base> tag is present (non-multilingual installs).
    var proclaimBaseUrl = (function () {
        var base = document.querySelector('base');
        if (!base) { return '/'; }
        var href = base.href; // absolute URL, e.g. "https://example.com/"
        return href.charAt(href.length - 1) === '/' ? href : href + '/';
    }());

    function proclaimTrackPlay(mediaId) {
        if (!mediaId || proclaimTrackedIds[mediaId]) {
            return;
        }
        proclaimTrackedIds[mediaId] = true;

        var url = proclaimBaseUrl + 'index.php?option=com_proclaim&task=Cwmsermons.playHitAjax&id=' + encodeURIComponent(mediaId) + '&tmpl=component';
        if (typeof fetch !== 'undefined') {
            fetch(url, { method: 'GET', credentials: 'same-origin' }).catch(function () {});
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.send();
        }
    }

    /**
     * Attach a YT.Player wrapper to an iframe and track PLAYING events.
     * @param {HTMLIFrameElement} iframe
     * @param {string} mediaId
     */
    function proclaimInitYTPlayer(iframe, mediaId) {
        new YT.Player(iframe, {
            events: {
                onStateChange: function (event) {
                    if (event.data === YT.PlayerState.PLAYING) {
                        proclaimTrackPlay(mediaId);
                    }
                }
            }
        });
    }

    /**
     * YouTube IFrame API callback — fires once the API script has loaded.
     * Creates YT.Player wrappers on each inline YouTube iframe so we can
     * detect the PLAYING state and log the analytics event.
     * (Cross-origin security prevents detecting clicks inside the iframe itself.)
     *
     * Handles both new renders (src already has enablejsapi=1) and cached
     * renders (src missing enablejsapi=1 — iframe is reloaded with it added).
     */
    // Capture audio/video play events from .playhit/.hitplay containers.
    // Uses capture phase because 'play' does not bubble, and survives DOM
    // replacement by sermon-filters AJAX (innerHTML swaps destroy per-element listeners).
    document.addEventListener('play', function (e) {
        var el = e.target;
        if (el.tagName !== 'AUDIO' && el.tagName !== 'VIDEO') { return; }
        var container = el.closest('.playhit[data-id], .hitplay[data-id]');
        if (container) {
            proclaimTrackPlay(container.getAttribute('data-id'));
        }
    }, true);

    /**
     * Attach Vimeo Player SDK wrappers to all uninitialised Vimeo iframes.
     * Called after the SDK script loads and after any listing DOM replacement.
     */
    function proclaimInitVimeoPlayers() {
        document.querySelectorAll('iframe.playhit[src*="vimeo.com"], iframe.hitplay[src*="vimeo.com"]').forEach(function (iframe) {
            var mediaId = iframe.getAttribute('data-id');
            if (!mediaId || iframe.dataset.vimeoInited) {
                return;
            }
            iframe.dataset.vimeoInited = '1';
            var player = new Vimeo.Player(iframe);
            player.on('play', function () {
                proclaimTrackPlay(mediaId);
            });
        });
    }

    /**
     * Push _wq handlers for all uninitialised Wistia iframes.
     * Must be called before (or after) E-v1.js loads — _wq is processed either way.
     */
    function proclaimSetupWistiaTracking() {
        window._wq = window._wq || [];
        document.querySelectorAll('iframe.playhit[src*="wistia"], iframe.hitplay[src*="wistia"]').forEach(function (iframe) {
            if (iframe.dataset.wistiaInited) {
                return;
            }
            var src = iframe.getAttribute('src') || '';
            var match = src.match(/\/embed\/iframe\/([a-zA-Z0-9]+)/);
            if (!match) {
                return;
            }
            iframe.dataset.wistiaInited = '1';
            var wistiaHash = match[1];
            var mediaId = iframe.getAttribute('data-id');
            window._wq.push({
                id: wistiaHash,
                onReady: function (video) {
                    video.bind('play', function () {
                        proclaimTrackPlay(mediaId);
                    });
                }
            });
        });
    }

    /**
     * Resi has no public player SDK. Instead, a transparent overlay div is rendered
     * over the iframe (see Cwmmedia.php). On first click we:
     *   1. Track the play
     *   2. Swap autoplay=false → autoplay=true in the iframe src so the video starts
     *   3. Remove the overlay so subsequent clicks reach the player controls directly
     *
     * Called on page load and after each listing AJAX update.
     */
    function proclaimSetupResiTracking() {
        document.querySelectorAll('.proclaim-resi-overlay').forEach(function (overlay) {
            if (overlay.dataset.resiInited) {
                return;
            }
            overlay.dataset.resiInited = '1';

            overlay.addEventListener('click', function () {
                var mediaId = overlay.getAttribute('data-media-id');
                if (mediaId) {
                    proclaimTrackPlay(mediaId);
                }

                // Swap autoplay param then reload the iframe so the video starts
                var iframe = overlay.previousElementSibling;
                if (iframe && iframe.tagName === 'IFRAME') {
                    var src = iframe.getAttribute('src') || iframe.src;
                    src = src.replace(/autoplay=false/i, 'autoplay=true')
                             .replace(/autoplay=0\b/, 'autoplay=1');
                    iframe.src = src;
                }

                overlay.remove();
            }, { once: true });
        });
    }

    // Re-init third-party players after sermon-filters.es6.js replaces or appends listing content.
    // onYouTubeIframeAPIReady / Vimeo SDK onload each fire only once; new iframes need wrapping manually.
    document.addEventListener('proclaim:listing-updated', function () {
        // YouTube
        if (typeof YT !== 'undefined' && typeof YT.Player !== 'undefined') {
            document.querySelectorAll('iframe.playhit[src*="youtube.com"], iframe.hitplay[src*="youtube.com"]').forEach(function (iframe) {
                var mediaId = iframe.getAttribute('data-id');
                if (!mediaId || iframe.dataset.ytInited) {
                    return;
                }
                iframe.dataset.ytInited = '1';
                proclaimInitYTPlayer(iframe, mediaId);
            });
        }
        // Vimeo
        if (typeof Vimeo !== 'undefined' && typeof Vimeo.Player !== 'undefined') {
            proclaimInitVimeoPlayers();
        }
        // Wistia — _wq handles timing; just push handlers for new iframes
        proclaimSetupWistiaTracking();
        // Resi — re-init overlays for any newly inserted Resi players
        proclaimSetupResiTracking();
    });

    window.onYouTubeIframeAPIReady = function () {
        document.querySelectorAll('iframe.playhit, iframe.hitplay').forEach(function (iframe) {
            if (!/(youtube\.com\/embed)/.test(iframe.src)) {
                return;
            }

            var mediaId = iframe.getAttribute('data-id');
            if (!mediaId) {
                return;
            }

            iframe.dataset.ytInited = '1';

            // If the iframe was served from cache without enablejsapi=1, add it now.
            // This reloads the iframe, so we wait for the load event before wrapping.
            if (iframe.src.indexOf('enablejsapi=1') === -1) {
                var sep = iframe.src.indexOf('?') >= 0 ? '&' : '?';
                iframe.addEventListener('load', function () {
                    proclaimInitYTPlayer(iframe, mediaId);
                }, { once: true });
                iframe.src = iframe.src + sep + 'enablejsapi=1';
                return;
            }

            proclaimInitYTPlayer(iframe, mediaId);
        });
    };

    document.addEventListener("DOMContentLoaded", function () {
        // Load the YouTube IFrame API if any inline YouTube iframes are present.
        // This is deferred so it doesn't block page render.
        if (document.querySelector('iframe.playhit[src*="youtube.com"], iframe.hitplay[src*="youtube.com"]')) {
            var ytScript = document.createElement('script');
            ytScript.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(ytScript);
        }

        // Load the Vimeo Player SDK and init players once it's ready.
        if (document.querySelector('iframe.playhit[src*="vimeo.com"], iframe.hitplay[src*="vimeo.com"]')) {
            var vimeoScript = document.createElement('script');
            vimeoScript.src = 'https://player.vimeo.com/api/player.js';
            vimeoScript.onload = function () { proclaimInitVimeoPlayers(); };
            document.head.appendChild(vimeoScript);
        }

        // Set up Wistia _wq listeners then load E-v1.js asynchronously.
        if (document.querySelector('iframe.playhit[src*="wistia"], iframe.hitplay[src*="wistia"]')) {
            proclaimSetupWistiaTracking();
            var wistiaScript = document.createElement('script');
            wistiaScript.src = 'https://fast.wistia.com/assets/external/E-v1.js';
            wistiaScript.async = true;
            document.head.appendChild(wistiaScript);
        }

        // Set up Resi click-intercept overlays.
        if (document.querySelector('.proclaim-resi-overlay')) {
            proclaimSetupResiTracking();
        }

        // Track clicks/plays on any element with playhit or hitplay class.
        document.querySelectorAll('.playhit, .hitplay').forEach(function (element) {
            // Skip fancybox_player elements — they are tracked separately below
            if (element.classList.contains('fancybox_player')) {
                return;
            }

            var mediaId = element.getAttribute('data-id');
            if (!mediaId) {
                return;
            }

            // Audio/video in .playhit/.hitplay containers are tracked by the document
            // capture listener above — skip adding a click listener to the container.
            if (element.querySelector('audio, video')) {
                return;
            }

            // Third-party iframes are tracked via their own SDKs/postMessage listeners above.
            // Clicks on cross-origin iframes never reach the parent document, so adding a
            // click listener here would be silently ineffective.
            if (element.tagName === 'IFRAME') {
                return;
            }

            element.addEventListener('click', function () {
                proclaimTrackPlay(mediaId);
            });
        });

        // Track clicks on fancybox_player elements before Fancybox processes them.
        // This captures the data-id reliably regardless of Fancybox's internal API.
        document.querySelectorAll('.fancybox_player').forEach(function (element) {
            element.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                if (id) {
                    proclaimTrackPlay(id);
                }
            });
        });

        if (typeof Fancybox === 'undefined') {
            return;
        }

        // Use Fancybox.bind() for native element-based initialization.
        // This triggers fromTriggerEl/fromNodes which properly creates slides
        // from element data attributes, unlike Fancybox.show() which needs
        // manual plugin registration in v6 UMD builds.
        Fancybox.bind('.fancybox_player', {
            Carousel: { infinite: false },
            Toolbar: {
                display: {
                    left: [],
                    middle: [],
                    right: ["close"]
                }
            },
            backdropClick: "close",
            on: {
                reveal: function (fancybox) {
                    // Add error handlers for media elements
                    var container = fancybox.container;
                    if (!container) {
                        return;
                    }
                    var mediaElement = container.querySelector('audio, video');
                    if (mediaElement) {
                        mediaElement.addEventListener('error', function () {
                            var slide = this.closest('.fancybox__slide');
                            if (slide) {
                                var errorDiv = document.createElement('div');
                                errorDiv.className = 'proclaim-media-error';
                                errorDiv.style.cssText = 'padding:30px 20px;text-align:center;color:#fff;';
                                errorDiv.innerHTML = '<p style="margin:0 0 10px;font-size:1.1em;font-weight:500;">Unable to load media file</p>' +
                                    '<p style="margin:0;font-size:0.9em;color:#888;">The requested file could not be found or is unavailable.</p>';
                                this.style.display = 'none';
                                this.parentNode.insertBefore(errorDiv, this.nextSibling);
                            }
                        });
                    }
                }
            }
        });

        // Handle audio files separately since Fancybox v6 has no native audio support.
        // Intercept clicks on audio elements before Fancybox processes them.
        document.querySelectorAll('.fancybox_player').forEach(function (element) {
            var src = element.getAttribute('data-src') || '';
            if (!/\.(mp3|m4a|ogg|wav)(\?|$)/i.test(src)) {
                return;
            }

            element.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Track the play
                var mediaId = this.getAttribute('data-id');
                if (mediaId) {
                    proclaimTrackPlay(mediaId);
                }

                var audioSrc = this.getAttribute('data-src');
                var width = this.getAttribute('data-width') || this.getAttribute('pwidth') || '400';
                var headerText = this.getAttribute('data-header') || '';
                var footerText = this.getAttribute('data-footer') || '';
                var controls = this.getAttribute('controls') !== '0';
                var autoplay = this.getAttribute('autostart') === 'true';

                // Build caption
                var caption = '';
                if (headerText || footerText) {
                    caption = '<div class="proclaim-fancybox-caption">';
                    if (headerText) {
                        caption += '<div class="proclaim-fancybox-header">' + headerText + '</div>';
                    }
                    if (footerText) {
                        caption += '<div class="proclaim-fancybox-footer">' + footerText + '</div>';
                    }
                    caption += '</div>';
                }

                // Create audio overlay
                var overlay = document.createElement('div');
                overlay.className = 'proclaim-audio-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;' +
                    'background:rgba(0,0,0,0.85);z-index:9999;display:flex;' +
                    'align-items:center;justify-content:center;cursor:pointer;';

                var wrapper = document.createElement('div');
                wrapper.style.cssText = 'background:#1e1e2e;border-radius:12px;padding:24px;' +
                    'width:' + width + 'px;max-width:90vw;cursor:default;';

                if (caption) {
                    var captionDiv = document.createElement('div');
                    captionDiv.textContent = caption;
                    captionDiv.style.cssText = 'color:#fff;margin-bottom:16px;text-align:center;';
                    wrapper.appendChild(captionDiv);
                }

                var audio = document.createElement('audio');
                audio.style.cssText = 'width:100%;';
                if (controls) {
                    audio.controls = true;
                }
                if (autoplay) {
                    audio.autoplay = true;
                }
                var audioSource = document.createElement('source');
                audioSource.src = audioSrc;
                audioSource.type = 'audio/mpeg';
                audio.appendChild(audioSource);
                wrapper.appendChild(audio);

                // Track when the audio actually starts playing (belt-and-suspenders alongside click tracking above).
                // Deduplication in proclaimTrackPlay ensures only one event per page load per media ID.
                audio.addEventListener('play', function () {
                    proclaimTrackPlay(mediaId);
                });

                // Error handler
                audio.addEventListener('error', function () {
                    audio.style.display = 'none';
                    var errorDiv = document.createElement('div');
                    errorDiv.style.cssText = 'padding:20px;text-align:center;color:#fff;';
                    errorDiv.innerHTML = '<p style="margin:0 0 10px;font-size:1.1em;">Unable to load audio file</p>' +
                        '<p style="margin:0;font-size:0.9em;color:#888;">The requested file could not be found.</p>';
                    wrapper.appendChild(errorDiv);
                });

                overlay.appendChild(wrapper);
                document.body.appendChild(overlay);

                // Close on backdrop click or Escape
                overlay.addEventListener('click', function (ev) {
                    if (ev.target === overlay) {
                        audio.pause();
                        overlay.remove();
                    }
                });
                document.addEventListener('keydown', function handler(ev) {
                    if (ev.key === 'Escape') {
                        audio.pause();
                        overlay.remove();
                        document.removeEventListener('keydown', handler);
                    }
                });
            });
        });
    });

})();
