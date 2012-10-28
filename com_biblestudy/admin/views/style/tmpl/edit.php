<?php
/**
 * Form
 * @package BibleStudy.Admin
 * @since 7.1.0
 * @desc Form for style
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'style.cancel' || document.formvalidator.isValid(document.id('style-form'))) {
            Joomla.submitform(task, document.getElementById('style-form'));
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="style-form" class="form-validate form-horizontal" enctype="multipart/form-data">
    <fieldset class="adminform">
        <h4><?php echo JText::_('JBS_CMN_DETAILS'); ?></h4>
        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('published'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('published'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('filename'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('filename'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('stylecode'); ?>
            </div>
        </div>
        <div class="clr"></div>
        <hr />
        <div class="editor-border">
            <?php echo $this->form->getInput('stylecode', null, empty($this->item->stylecode) ? $this->defaultstyle : $this->item->stylecode); ?>
        </div>
        <div class="clr"></div>
        <?php if ($this->canDo->get('core.admin')): ?>
            <?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

            <?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>

            <fieldset class="panelform">
                <?php echo $this->form->getLabel('rules'); ?>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>

            <?php echo JHtml::_('sliders.end'); ?>
        <?php endif; ?>

    </fieldset>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>