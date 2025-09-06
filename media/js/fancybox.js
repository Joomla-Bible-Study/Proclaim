/**
 * @package     Proclaim.Fancybox
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005-2023 Open Source Matters, Inc. All rights
 *   reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".fancybox_player").forEach(function(element) {
        element.addEventListener("click", function() {
            const myVideo = this.getAttribute('data-src')
            const title = this.getAttribute('title')
            const height = this.getAttribute('pheight')
            const width = this.getAttribute('pwidth')
            const ptype = this.getAttribute('ptype')
            const potext = this.getAttribute('potext')
            const autostart = this.getAttribute('autostart')
            const controls = this.getAttribute('data-controls') || true
            const logo = this.getAttribute('data-logo')
            const logolink = this.getAttribute('data-logolink') || '#'
            const image = this.getAttribute('data-image')
            const mute = this.getAttribute('data-mute') || false
            console.log("myVideo value:", myVideo);
            Fancybox.show([
                {
                    src: myVideo,
                    width: width,
                    height: height,
                    preload: false,
                    img: image,
                    controls: 0,
                    rel: 0,
                    fs: 0
                },
            ])
        });
    });
}); 
