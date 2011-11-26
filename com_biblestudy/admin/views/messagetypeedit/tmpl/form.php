<?php
/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend>

			<?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
			<ul class="adminformlist">
				<li>
				<?php echo $this->form->getLabel('published'); ?>
					
				<?php echo $this->form->getInput('published'); ?></li>
				<li>
				<?php echo $this->form->getLabel('message_type'); ?>
					
				<?php echo $this->form->getInput('message_type'); ?></li>
			</ul>

		</fieldset>
	</div>
	<div class="clr"></div>
	
	
	
	
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
