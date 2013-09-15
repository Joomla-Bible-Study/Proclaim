/**
 * Created with JetBrains PhpStorm.
 * User: bcordis
 * Date: 9/14/13
 * Time: 11:35 AM
 * To change this template use File | Settings | File Templates.
 */
;
(function (window, document, $) {

    $(document).ready(function () {
        $(".fancybox").fancybox();
    });
    $(".fancybox")
        .attr('rel', 'gallery')
        .fancybox({
            openEffect: 'none',
            closeEffect: 'none',
            nextEffect: 'none',
            prevEffect: 'none',
            padding: 0,
            margin: [20, 60, 20, 60] // Increase left/right margin
        });
    $(document).ready(function () {
        $('.fancybox-media').fancybox({
            openEffect: 'none',
            closeEffect: 'none',
            helpers: {
                media: {}
            }
        });
    });

    $(document).ready(function () {
        $(".fancybox_jwplayer").on("click", function () {
            var tarGet;
            var myVideo = this.href;
            var width = 640; //parseInt(this.rel.match(/width=[0-9]+/i)[0].replace('width=', ''));
            var height = 381; //parseInt(this.rel.match(/height=[0-9]+/i)[0].replace('height=', ''));
            $.fancybox({
                content: '<div id="video_container" style="width:640px;height:381px;">Loading the player ... </div> ',
                afterShow: function () {
                    jwplayer("video_container").setup({
                        flashplayer: "media/com_biblestudy/player/jwplayer.flash.swf",
                        file: myVideo,
                        width: width,
                        height: height
                    }); // jwplayer setup
                } // afterShow
            }); // fancybox
            return false; // prevents default
        }); // on
    }); // ready
}(window, document, jQuery));
