<?php

/**
 * @author JoomlaBibleStudy.org
 * @copyright 2010
 */

defined( '_JEXEC' ) or die('Restricted access');

require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
$mediaid = JRequest::getInt('mediaid',1,'post');
$media = new jbsMedia();
$medialink = $media->getMediaLink($mediaid);
$play = $media->hitPlay($mediaid);


print "<script>";
print " self.location='http://".$medialink."';";
print "</script>"; 

?>