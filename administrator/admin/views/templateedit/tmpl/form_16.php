<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
$params = $this->form->getFieldsets('params');
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">

<?php echo JHtml::_('tabs.start'); ?>
<?php echo JHtml::_('tabs.panel', JText::_('JBS_TPL_PARAMS'), 'general'); ?>
 <div class="width-100">
    <div class="width-60 fltlft">
        <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_COMPONENT_SETTINGS'); ?></legend>
        </fieldset>
    </div>
 </div>
</form>
