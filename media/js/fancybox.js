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
            const height = this.getAttribute('data-height') || this.getAttribute('pheight');
            const width = this.getAttribute('data-width') || this.getAttribute('pwidth');
            const image = this.getAttribute('data-image');
            const autoplay = this.getAttribute('autostart') === 'true';
            const headerText = this.getAttribute('data-header') || '';
            const footerText = this.getAttribute('data-footer') || '';

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

            Fancybox.show([
                {
                    src: src,
                    width: width,
                    height: height,
                    thumb: image,
                    preload: false,
                    autoStart: autoplay,
                    caption: caption
                }
            ], {
                // Fancybox options
                Carousel: {
                    infinite: false
                },
                Toolbar: {
                    display: {
                        left: [],
                        middle: [],
                        right: ["close"]
                    }
                }
            });
        });
    });
});
