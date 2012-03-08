<?php

//No Direct Access
defined('_JEXEC') or die;
echo $this->loadTemplate('formheader');
if ($this->params->get('useexpert_list') > 0) {
    echo $this->loadTemplate('custom');
} else {
    echo $this->loadTemplate('main');
    
}
echo $this->loadTemplate('formfooter');