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
		$(".fancybox_jwplayer").on("click", function () {
			var tarGet;
			var contentPanelId = $(this).attr("id");
			var id = $("#" + contentPanelId);
			var myVideo = id.attr('data-src');
			var title = id.attr('title');
			var height = id.attr('pheight');
			var width = id.attr('pwidth');
			var ptype = id.attr('ptype');
			var potext = id.attr('potext');
			var autostart = id.attr('autostart');
			var controls = id.attr('data-controls') || true;
			var logo = id.attr('data-logo');
			var logolink = id.attr('data-logolink') || '#';
			var image = id.attr('data-image');
			var mute = id.attr('data-mute') || false;
			$.fancybox.open({
				content: '<div id="video_container"></div>',
				type: 'html',
				width: width,
				height: height,
				opts: {
					smallBtn: false,
					afterLoad: function () {
						var playerInstance = jwplayer("video_container");
						playerInstance.setup({
							title: title,
							logo: {
								file: logo,
								link: logolink
							},
							image: image,
							abouttext: "Direct Link",
							aboutlink: myVideo,
							mediaid: contentPanelId,
							file: myVideo,
							width: width,
							height: height,
							mute: mute,
							autostart: autostart,
							controls: controls
						});
					},
					caption: '<button data-fancybox-close onclick="window.open(\'index.php?option=com_biblestudy&amp;player=' + ptype + '&amp;view=popup&amp;mediaid=' + contentPanelId +
					'&amp;tmpl=component\',\'_blank\',\'resizable=yes\')">' + potext + '</button>'
				}
			});
		}); // on
	}); // ready
}(window, document, jQuery));
