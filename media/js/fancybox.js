/**
 * @package     Proclaim.Fancybox
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005-2023 Open Source Matters, Inc. All rights
 *   reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".fancybox_player").forEach(function (element) {
        element.addEventListener("click", function () {
            const src = this.getAttribute('data-src');
            const height = this.getAttribute('data-height') || this.getAttribute('pheight') || '360';
            const width = this.getAttribute('data-width') || this.getAttribute('pwidth') || '640';
            const posterImage = this.getAttribute('data-image') || '';
            const autoplay = this.getAttribute('autostart') === 'true';
            const muted = this.getAttribute('data-mute') === 'true';
            const logo = this.getAttribute('data-logo') || '';
            const logoLink = this.getAttribute('data-logolink') || '';
            const headerText = this.getAttribute('data-header') || '';
            const footerText = this.getAttribute('data-footer') || '';
            const controls = this.getAttribute('controls') !== '0';

            // Determine media type from source
            const isVideo = /\.(mp4|m4v|webm|ogv|mov)(\?|$)/i.test(src) ||
                           /youtube\.com|youtu\.be|vimeo\.com/i.test(src);
            const isAudio = /\.(mp3|m4a|ogg|wav)(\?|$)/i.test(src);

            // Build caption with header and footer
            let caption = '';
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

            // Build logo overlay HTML
            let logoHtml = '';
            if (logo) {
                logoHtml = '<div class="proclaim-fancybox-logo">';
                if (logoLink) {
                    logoHtml += '<a href="' + logoLink + '" target="_blank">';
                }
                logoHtml += '<img src="' + logo + '" alt="Logo" />';
                if (logoLink) {
                    logoHtml += '</a>';
                }
                logoHtml += '</div>';
            }

            // For YouTube/Vimeo, use iframe; for local media, use HTML5 player
            let contentType = 'iframe';
            let htmlContent = '';

            if (isAudio) {
                // Build HTML5 audio player
                contentType = 'html';
                htmlContent = '<div class="proclaim-player-wrapper" style="width:' + width + 'px;">';
                htmlContent += '<audio ' + (controls ? 'controls ' : '') +
                              (autoplay ? 'autoplay ' : '') +
                              (muted ? 'muted ' : '') +
                              'style="width:100%;">';
                htmlContent += '<source src="' + src + '" type="audio/mpeg">';
                htmlContent += '</audio>';
                htmlContent += logoHtml;
                htmlContent += '</div>';
            } else if (isVideo && !/youtube\.com|youtu\.be|vimeo\.com/i.test(src)) {
                // Build HTML5 video player for local videos
                contentType = 'html';
                htmlContent = '<div class="proclaim-player-wrapper" style="position:relative;width:' + width + 'px;height:' + height + 'px;">';
                htmlContent += '<video ' + (controls ? 'controls ' : '') +
                              (autoplay ? 'autoplay ' : '') +
                              (muted ? 'muted ' : '') +
                              (posterImage ? 'poster="' + posterImage + '" ' : '') +
                              'style="width:100%;height:100%;">';
                htmlContent += '<source src="' + src + '" type="video/mp4">';
                htmlContent += '</video>';
                htmlContent += logoHtml;
                htmlContent += '</div>';
            }

            // Show Fancybox
            if (contentType === 'html' && htmlContent) {
                Fancybox.show([{
                    src: htmlContent,
                    type: 'html',
                    caption: caption
                }], {
                    Carousel: { infinite: false },
                    Toolbar: {
                        display: {
                            left: [],
                            middle: [],
                            right: ["close"]
                        }
                    }
                });
            } else {
                // Use iframe for YouTube/Vimeo
                Fancybox.show([{
                    src: src,
                    type: 'iframe',
                    width: width,
                    height: height,
                    thumb: posterImage,
                    preload: false,
                    caption: caption
                }], {
                    Carousel: { infinite: false },
                    Toolbar: {
                        display: {
                            left: [],
                            middle: [],
                            right: ["close"]
                        }
                    }
                });
            }
        });
    });
});
