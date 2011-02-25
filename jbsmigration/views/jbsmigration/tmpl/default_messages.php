<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */

defined('_JEXEC') or die();

//$messages = JRequest::getString('jbsmessages','','get');

?>
<div>
 <fieldset class="panelform">
 <?php echo JHtml::_('sliders.panel', JText::_('RE_INSTALLING_VERSION_700') , 'publishing-details'); ?>
 <?php $jbs611 = JRequest::getString('jbs611','','get'); echo $jbs611; ?>
 </fieldset>
</div>