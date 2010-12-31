<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'administrator' .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
if (JOOMLA_VERSION == '6')
     
     	echo $this->loadTemplate('j16');
else
		echo $this->loadTemplate('j15');
	
?>
