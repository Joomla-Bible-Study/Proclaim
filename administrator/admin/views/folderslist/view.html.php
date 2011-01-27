<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');

//Branch the JView based on the joomla version
if(JOOMLA_VERSION == 5)
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'views'.DS.'folderslist'.DS.'viewj16.html.php');
else
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'views'.DS.'folderslist'.DS.'viewj15.html.php');

?>
