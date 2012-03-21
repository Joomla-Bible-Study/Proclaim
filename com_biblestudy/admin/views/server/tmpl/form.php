<?php
/**
 * @version $Id: form.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend>

			<?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>
			<ul class="adminformlist">
				<li>
				<?php echo $this->form->getLabel('published'); ?>

				<?php echo $this->form->getInput('published'); ?></li>
				<li>
				<?php echo $this->form->getLabel('server_name'); ?>

				<?php echo $this->form->getInput('server_name'); ?></li>
				<li>
				<?php echo $this->form->getLabel('server_path'); ?>

				<?php echo $this->form->getInput('server_path'); ?></li>
                                <li>
				<?php echo $this->form->getLabel('type'); ?>

				<?php echo $this->form->getInput('type'); ?></li>
				
			</ul>

	</div>
	<div class="clr"></div>

<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend>

			<?php echo JText::_( 'JBS_CMN_FTP' ); ?></legend>
			<ul class="adminformlist">
				
				<?php echo $this->form->getLabel('ftphost'); ?>

				<?php echo $this->form->getInput('ftphost'); ?></li>
				<li>
				<?php echo $this->form->getLabel('ftpuser'); ?>

				<?php echo $this->form->getInput('ftpuser'); ?></li>
                                <li>
				<?php echo $this->form->getLabel('ftppassword'); ?>

				<?php echo $this->form->getInput('ftppassword'); ?></li>
                                <li>
				<?php echo $this->form->getLabel('ftpport'); ?>

				<?php echo $this->form->getInput('ftpport'); ?></li>
                                <li>
				<?php echo $this->form->getLabel('aws_key'); ?>

				<?php echo $this->form->getInput('aws_key'); ?></li>
                                <li>
				<?php echo $this->form->getLabel('aws_secret'); ?>

				<?php echo $this->form->getInput('aws_secret'); ?></li>
			</ul>

	</div>



	<?php if ($this->canDo->get('core.admin')): ?>
		<div class="width-100 fltlft">
			<?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

				<?php echo JHtml::_('sliders.panel',JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>

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