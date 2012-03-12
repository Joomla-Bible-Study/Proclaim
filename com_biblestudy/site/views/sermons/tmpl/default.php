<?php

//No Direct Access
defined('_JEXEC') or die; 
echo $this->loadTemplate('formheader');
if ($this->params->get('useexpert_list') > 0) {
    echo $this->loadTemplate('custom');
} 
elseif ($this->params->get('sermonstemplate') )
    {
    $length = strlen($this->params->get('sermonstemplate'));
    $template = substr($this->params->get('sermonstemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
else {
    echo $this->loadTemplate('main');
    
}
echo $this->loadTemplate('formfooter');