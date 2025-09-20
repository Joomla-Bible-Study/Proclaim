(function (window, document, $) {
    $(function () {
        const videoID = 'yes101'

        // Helper function to validate video src URLs
        function isSafeVideoSrc(url) {
            if (typeof url !== 'string') return false;
            // Disallow javascript:, data:, vbscript: protocols
            const unsafePattern = /^(javascript|data|vbscript):/i;
            if (unsafePattern.test(url.trim())) return false;
            // Optionally, allow only certain file extensions (e.g., .mp4, .webm, .ogg)
            const allowedExtensions = /\.(mp4|webm|ogg)(\?.*)?$/i;
            if (!allowedExtensions.test(url.trim())) return false;
            return true;
        }

        $('.videolink').on('click',function (event) {
            const contentPanelId = $(this).attr('id')
            const videolink = $('#' + contentPanelId)
            const newmp4 = videolink.attr('data-src')
            const player = $('#' + videoID)
            // Validate the newmp4 URL before using it
            if (isSafeVideoSrc(newmp4)) {
                player.get(0).pause()
                player.attr('src', newmp4)
                player.get(0).load()
            } else {
                console.error('Blocked potentially unsafe video source:', newmp4)
            }
            //$('#'+videoID).attr('poster', newposter); //Change video poster
            player.get(0).play()
        })
    })
}(window, document, jQuery))
