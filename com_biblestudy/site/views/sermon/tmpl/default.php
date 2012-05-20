<?php
//No Direct Access
defined('_JEXEC') or die;
echo $this->loadTemplate('header');
if ($this->params->get('useexpert_details') > 0)
 {
	echo $this->loadTemplate('custom');
 }
 elseif ($this->params->get('sermontemplate') )
    {
        echo $this->loadTemplate($this->params->get('sermontemplate'));
    }
 else
 {
 	echo $this->loadTemplate('main');
 }
 echo $this->loadTemplate('scripture');
 //echo '<br />';
 echo $this->loadTemplate('commentsform');
 echo $this->loadTemplate('footerlink');
 echo $this->loadTemplate('footer');