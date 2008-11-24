var $j = jQuery.noConflict();

$j(document).ready( function() {
	$j('.btnPlay').click( function() {
		var mediaId = $j(this).attr('alt');
		var url = $j(this).attr('href');
		$j('#media-' + mediaId).toggle();
		return false;
	});
});
