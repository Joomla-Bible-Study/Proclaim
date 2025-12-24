(function (window, document, $) {
    'use strict';

    $(function () {
        const videoID = 'yes101';

        // Helper function to validate video src URLs
        function isSafeVideoSrc(url) {
            if (typeof url !== 'string') return false;
            // Disallow javascript:, data:, vbscript: protocols
            const unsafePattern = /^(javascript|data|vbscript):/i;
            if (unsafePattern.test(url.trim())) return false;
            // Only allow video file extensions (.mp4, .webm, .ogg)
            const allowedExtensions = /\.(mp4|webm|ogg)(\?.*)?$/i;
            if (!allowedExtensions.test(url.trim())) return false;
            return true;
        }

        $('.videolink').on('click', function (event) {
            event.preventDefault();

            const $this = $(this);
            const contentPanelId = $this.attr('id');
            const newmp4 = $this.attr('data-src');
            const $player = $('#' + videoID);
            const playerElement = $player.get(0);

            // Ensure player element exists
            if (!playerElement) {
                console.error('Video player element not found:', videoID);
                return;
            }

            // Validate the newmp4 URL before using it
            if (isSafeVideoSrc(newmp4)) {
                playerElement.pause();
                $player.attr('src', newmp4);
                playerElement.load();
                playerElement.play();
            } else {
                console.error('Blocked potentially unsafe video source:', newmp4);
            }
        });
    });
}(window, document, jQuery));
