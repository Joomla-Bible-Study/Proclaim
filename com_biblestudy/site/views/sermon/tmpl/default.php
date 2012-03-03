<?php
//No Direct Access
defined('_JEXEC') or die;

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