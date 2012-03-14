<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_teacherlist')> 0)
     {
     	echo $this->loadTemplate('custom');
	 }
elseif ($this->params->get('teacherstemplate') )
    {
        echo $this->loadTemplate($this->params->get('teacherstemplate'));
    }
else
    {
        echo $this->loadTemplate('main');
    }