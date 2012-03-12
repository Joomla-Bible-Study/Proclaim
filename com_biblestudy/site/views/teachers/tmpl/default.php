<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_teacherlist')> 0)
     {
     	echo $this->loadTemplate('custom');
	 }
elseif ($this->params->get('teacherstemplate') )
    {
    $length = strlen($this->params->get('teacherstemplate'));
    $template = substr($this->params->get('teacherstemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
else
	{
		echo $this->loadTemplate('main');
	}