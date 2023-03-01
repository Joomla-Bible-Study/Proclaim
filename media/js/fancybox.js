/**
 * @package     Proclaim.Fancybox
 * @subpackage  com_proclaim
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights
 *   reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (window, document, $) {
	$(function () {
		$('.fancybox_jwplayer').on('click', function () {
			let tarGet
			const contentPanelId = $(this).attr('id')
			const id = $('#' + contentPanelId)
			const myVideo = id.attr('data-src')
			const title = id.attr('title')
			const height = id.attr('pheight')
			const width = id.attr('pwidth')
			const ptype = id.attr('ptype')
			const potext = id.attr('potext')
			const autostart = id.attr('autostart')
			const controls = id.attr('data-controls') || true
			const logo = id.attr('data-logo')
			const logolink = id.attr('data-logolink') || '#'
			const image = id.attr('data-image')
			const mute = id.attr('data-mute') || false
			$.fancybox.open({
				content: '<div id="video_container"></div>',
				type: 'html',
				width: width,
				height: height,
				opts: {
					smallBtn: false,
					afterLoad: function () {
						const playerInstance = jwplayer('video_container')
						playerInstance.setup({
							title: title,
							logo: {
								file: logo,
								link: logolink,
							},
							image: image,
							abouttext: 'Direct Link',
							aboutlink: myVideo,
							mediaid: contentPanelId,
							file: myVideo,
							width: width,
							height: height,
							mute: false,
							autostart: autostart,
							controls: controls,
						})
					},
					caption: '<button data-fancybox-close onclick="window.open(\'index.php?option=com_proclaim&amp;player=' +
						ptype + '&amp;view=popup&amp;mediaid=' + contentPanelId +
						'&amp;tmpl=component\',\'_blank\',\'resizable=yes\')">' + potext +
						'</button>',
				},
			})
		}) // on
	}) // ready
}(window, document, jQuery))
