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
    <div class="width-60 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('foldername'); ?>
                    <?php echo $this->form->getInput('foldername'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('folderpath'); ?>
                    <?php echo $this->form->getInput('folderpath'); ?>
                </li>
            </ul>

        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
