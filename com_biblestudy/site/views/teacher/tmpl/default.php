<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_teacherdetail')> 0)
     {
     	echo $this->loadTemplate('custom');
	 }
elseif ($this->params->get('teachertemplate') )
    {
    $length = strlen($this->params->get('teachertemplate'));
    $template = substr($this->params->get('teachertemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
         else
	{
		echo $this->loadTemplate('main');
	}