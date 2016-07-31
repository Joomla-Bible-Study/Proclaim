<?php
/**
 * ???
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

	<title>Plupload - Queue widget example</title>

	<link rel="stylesheet"
	      href="../../../../../../media/com_biblestudy/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css"
	      type="text/css" media="screen"/>

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>

	<!-- production -->
	<script type="text/javascript" src="../../../../../../media/com_biblestudy/plupload/js/plupload.full.js"></script>
	<script type="text/javascript"
	        src="../../../../../../media/com_biblestudy/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

	<!-- debug
	<script type="text/javascript" src="/media/com_biblestudy/plupload/js/moxie.js"></script>
	<script type="text/javascript" src="/media/com_biblestudy/plupload/js/plupload.dev.js"></script>
	<script type="text/javascript" src="/media/com_biblestudy/plupload/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>
	-->


</head>
<body style="font: 13px Verdana; background: #eee; color: #333;">

<form method="post" action="dump.php">
	<div id="uploader">
		<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>
	<input type="submit" value="Send"/>
</form>

<script type="text/javascript">
	$(function () {

		// Setup html5 version
		$("#uploader").pluploadQueue({
			// General settings
			runtimes: 'html5,flash,silverlight,html4',
			url: '../../../../../../media/com_biblestudy/plupload/upload.php',
			chunk_size: '1mb',
			rename: true,
			dragdrop: true,

			filters: {
				// Maximum file size
				max_file_size: '900mb',
				// Specify what files to browse for
				mime_types: [
					{title: "Image files", extensions: "*,gif,png"},
					{title: "Zip files", extensions: "zip"}
				]
			},

			// Resize images on clientside if we can
			resize: {width: 320, height: 240, quality: 90},

			flash_swf_url: '../../../../../../media/com_biblestudy/plupload/js/Moxie.swf',
			silverlight_xap_url: '../../../../../../media/com_biblestudy/plupload/js/Moxie.xap'
		});

	});
</script>

</body>
</html>
