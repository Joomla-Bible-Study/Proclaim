<?php defined('_JEXEC') or die('Restricted access'); 

if ($this->params->get('useexpert_details') > 0)
 {
	echo $this->loadTemplate('custom');
 }
 else
 {
 	echo $this->loadTemplate('main');
	 
 }
 echo $this->loadTemplate('scripture');
 echo '<br />';
 echo $this->loadTemplate('commentsform');
 echo $this->loadTemplate('footer');