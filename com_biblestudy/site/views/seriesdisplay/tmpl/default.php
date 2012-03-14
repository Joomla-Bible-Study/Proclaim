<?php

//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_seriesdetail')> 0)
    {
        echo $this->loadTemplate('custom');
    }
elseif ($this->params->get('seriesdisplaytemplate') )
    {
        echo $this->loadTemplate($this->params->get('seriesdisplaytemplate'));
    }
else
    {
        echo $this->loadTemplate('main');
    }