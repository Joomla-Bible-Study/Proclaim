<?php
/**
 * @version     $Id: view.html.php 1328 2011-01-06 04:14:05Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');

//Branch the JView based on the joomla version
if(JOOMLA_VERSION == 6)
	require_once(JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'views'.DS.'studieslist'.DS.'viewj16.html.php');
else
	require_once(JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'views'.DS.'studiesedit'.DS.'viewj15.html.php');

?>