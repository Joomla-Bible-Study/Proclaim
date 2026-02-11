/**
 * @package     Proclaim.Fancybox
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005-2026 CWM Team All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/* jshint esversion: 11, browser: true */
/* globals Fancybox */

"use strict";

document.addEventListener("DOMContentLoaded", function () {
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
