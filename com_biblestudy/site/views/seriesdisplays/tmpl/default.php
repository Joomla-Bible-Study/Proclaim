<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_serieslist')> 0)
     {
     	echo $this->loadTemplate('custom');
	 }
elseif ($this->params->get('seriesdisplaystemplate') )
    {
    $length = strlen($this->params->get('seriesdisplaystemplate'));
    $template = substr($this->params->get('seriesdisplaystemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
else
	{
		echo $this->loadTemplate('main');
	}