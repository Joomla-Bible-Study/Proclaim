<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_teacherdetail')> 0)
    {
     	echo $this->loadTemplate('custom');
    }
elseif ($this->params->get('teachertemplate') )
    {
        echo $this->loadTemplate($this->params->get('teachertemplate'));
    }     
else
    {
        echo $this->loadTemplate('main');
    }