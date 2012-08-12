<?php
/**
 * Form
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="formelm-buttons">
        <button type="button" onclick="Joomla.submitbutton('commentsedit.save')">
            <?php echo JText::_('JSAVE') ?>
        </button>
        <button type="button" onclick="Joomla.submitbutton('commentsedit.cancel')">
            <?php echo JText::_('JCANCEL') ?>
        </button>
    </div>
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <table border="0" width="100%" cellspacing="2" cellpadding="2">
                <tr><td width="25%">
                        <?php echo $this->form->getLabel('published'); ?>
                    </td>
                    <td>
                        <?php echo $this->form->getInput('published'); ?>
                    </td></tr>
                <tr><td>
                        <?php echo $this->form->getLabel('study_id'); ?>
                    </td><td>
                        <?php echo $this->form->getInput('study_id'); ?>
                    </td></tr>
                <tr><td>
                        <?php echo $this->form->getLabel('comment_date'); ?>
                    </td><td>
                        <?php echo $this->form->getInput('comment_date'); ?>
                    </td></tr>
                <tr><td>
                        <?php echo $this->form->getLabel('full_name'); ?>
                    </td><td>
                        <?php echo $this->form->getInput('full_name'); ?>
                    </td></tr>
                <tr><td>
                        <?php echo $this->form->getLabel('user_email'); ?>
                    </td><td>
                        <?php echo $this->form->getInput('user_email'); ?>
                    </td></tr>
                <tr><td>
                        <?php echo $this->form->getLabel('comment_text'); ?>
                    </td><td>
                        <?php echo $this->form->getInput('comment_text'); ?>
                    </td></tr>
            </table>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>