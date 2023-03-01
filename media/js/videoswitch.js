(function (window, document, $) {
	$(function () {
		const videoID = 'yes101'

		$('.videolink').on('click',function (event) {
			const contentPanelId = $(this).attr('id')
			const videolink = $('#' + contentPanelId)
			const newmp4 = videolink.attr('data-src')
			const player = $('#' + videoID)
			player.get(0).pause()
			player.attr('src', newmp4)
			player.get(0).load()
			//$('#'+videoID).attr('poster', newposter); //Change video poster
			player.get(0).play()
		})
	})
}(window, document, jQuery))
