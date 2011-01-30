<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('study_id'); ?>
                    <?php echo $this->form->getInput('study_id'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('comment_date'); ?>
                    <?php echo $this->form->getInput('comment_date'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('full_name'); ?>
                    <?php echo $this->form->getInput('full_name'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('user_email'); ?>
                    <?php echo $this->form->getInput('user_email'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('comment_text'); ?>
                    <?php echo $this->form->getInput('comment_text'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
