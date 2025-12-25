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

            Fancybox.show([
                {
                    src: src,
                    width: width,
                    height: height,
                    thumb: image,
                    preload: false,
                    autoStart: autoplay
                }
            ]);
        });
    });
});
