(function () {
    'use strict';

    /**
     * @package     Proclaim.Fancybox
     * @subpackage  com_proclaim
     *
     * @copyright   Copyright (C) 2005-2026 CWM Team All rights reserved.
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     */
    /* jshint esversion: 11, browser: true */
    /* globals Fancybox, YT */


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

    window.onYouTubeIframeAPIReady = function () {
        document.querySelectorAll('iframe.playhit, iframe.hitplay').forEach(function (iframe) {
            if (!/(youtube\.com\/embed)/.test(iframe.src)) {
                return;
            }

            var mediaId = iframe.getAttribute('data-id');
            if (!mediaId) {
                return;
            }

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

            // YouTube iframes are tracked via IFrame API (onYouTubeIframeAPIReady above).
            // Vimeo/Resi/Wistia inline iframes: track on border click (best we can without their SDKs).
            if (element.tagName === 'IFRAME' && /(youtube\.com\/embed)/.test(element.src)) {
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
                    captionDiv.innerHTML = caption;
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
                audio.innerHTML = '<source src="' + audioSrc + '" type="audio/mpeg">';
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
