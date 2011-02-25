<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$done = JRequest::getInt('done','','get');
if ($done > 0) 
{
    echo $this->loadTemplate('messages');
} 
    echo $this->loadTemplate('main');

?>

