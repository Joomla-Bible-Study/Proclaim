<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_seriesdetail')> 0)
    {
    echo $this->loadTemplate('custom');
    }
elseif ($this->params->get('seriesdisplaytemplate') )
    {
    $length = strlen($this->params->get('seriesdisplaytemplate'));
    $template = substr($this->params->get('seriesdisplaytemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
else
    {
        echo $this->loadTemplate('main');
    }