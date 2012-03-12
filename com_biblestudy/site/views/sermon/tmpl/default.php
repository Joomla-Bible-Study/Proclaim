<?php
//No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_details') > 0)
 {
	echo $this->loadTemplate('custom');
 }
 elseif ($this->params->get('sermontemplate') )
    {
    $length = strlen($this->params->get('sermontemplate'));
    $template = substr($this->params->get('sermontemplate'),8,$length - 8);    
    $template = substr($template,-0,4); 
    echo $this->loadTemplate($template);
    }
 else
 {
 	echo $this->loadTemplate('main');

 }
 echo $this->loadTemplate('scripture');
 echo '<br />';
 echo $this->loadTemplate('commentsform');
 echo $this->loadTemplate('footer');