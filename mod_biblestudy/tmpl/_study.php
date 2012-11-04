<?php defined('_JEXEC') or die('Restricted access'); 
$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
$row = $study;
?>
        <?php 
include_once($path1.'listing.php');
	$listing = getListing($row, $params, $oddeven);
 	echo $listing;
 
 ?>
    






