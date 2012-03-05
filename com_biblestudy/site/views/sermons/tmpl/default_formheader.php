<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . JRequest::getInt('t', '1')); ?>" method="post">