<?php
/**
 * Default Header
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;
/*
 * @desc this is the main div for the page. default_footer has the closing div
 *
 */
?>
<script language="javascript">
function ReverseDisplay() {
	var ele = document.getElementById("scripture");
	var text = document.getElementById("heading");
	if(ele.style.display == "block") {
    		ele.style.display = "none";
		text.innerHTML = "show";
  	}
	else {
		ele.style.display = "block";
		text.innerHTML = "hide";
	}
}
</script>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->