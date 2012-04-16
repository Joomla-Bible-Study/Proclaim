<?php
/**
 * @version $Id: form.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @since 7.1.0
 * @desc Form for style
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

?>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="width-100">
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>

                    <?php echo $this->form->getInput('published'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('filename'); ?>

                    <?php echo $this->form->getInput('filename'); ?></li>
                <li>
                <li>
                    <?php echo $this->form->getLabel('type'); ?>

                    <?php echo $this->form->getInput('type'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('templatecode'); ?>
                </li>
            </ul>
            <div class="clr"></div>
            <div>
                <?php echo $this->form->getInput('templatecode', null, empty($this->item->templatecode) ? $this->defaultcode : $this->item->templatecode); ?>
            </div>


        </fieldset>
    </div>
    <div class="clr"></div>




    <?php if ($this->canDo->get('core.admin')): ?>
        <div class="width-100 fltlft">
            <?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

            <?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>

            <fieldset class="panelform">
                <?php echo $this->form->getLabel('rules'); ?>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>

            <?php echo JHtml::_('sliders.end'); ?>
        </div>
    <?php endif; ?>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>