<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'administrator' .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
if (JOOMLA_VERSION == '5')
     {
     	echo $this->loadTemplate('15');
	 }
else
	{
		echo $this->loadTemplate('16');
	}
?>
