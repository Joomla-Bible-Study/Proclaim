<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
if ($this->params->get('useexpert_teacherdetail')> 0)
     {
     	echo $this->loadTemplate('custom');
	 }
else
	{
		echo $this->loadTemplate('main');
	}