<?php defined('_JEXEC') or die('Restricted access'); 

if ($this->params->get('useexpert_details') > 0)
 {
 	//$details = getStudyExp($row, $params, $this->admin_params, $this->template);
	//	echo $details;
	echo $this->loadTemplate('custom');
 }
 else
 {
 	echo $this->loadTemplate('main');
	 
 }?>
