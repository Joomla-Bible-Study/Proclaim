<?php
/**
 * @version     $Id: form.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('mediafilesedit.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="Joomla.submitbutton('mediafilesedit.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
           
                <div class="formelm">
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('study_id'); ?>
                    <?php echo $this->form->getInput('study_id'); ?>
                </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('comment_date'); ?>
                    <?php echo $this->form->getInput('comment_date'); ?>
                </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('full_name'); ?>
                    <?php echo $this->form->getInput('full_name'); ?>
                </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('user_email'); ?>
                    <?php echo $this->form->getInput('user_email'); ?>
                </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('comment_text'); ?>
                    <?php echo $this->form->getInput('comment_text'); ?>
                </div>
            
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
