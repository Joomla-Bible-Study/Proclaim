<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_serieslist')> 0)
     {
     	echo $this->loadTemplate('custom');        
     }
elseif ($this->params->get('seriesdisplaystemplate') )
    {
        echo $this->loadTemplate($this->params->get('seriesdisplaystemplate'));
    }
else
    {
        echo $this->loadTemplate('main');
    }