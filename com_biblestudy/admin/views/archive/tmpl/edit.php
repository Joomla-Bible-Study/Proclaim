<?php
/**
 * Form sub Archive
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      7.1.0
 * */

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'admin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=cpanel'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
	<div class="row-fluid" style="margin-top: 50px;">
		<div class="span12 form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('timeframe'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('timeframe'); ?>
					<?php echo $this->form->getInput('swich'); ?>
				</div>
			</div>
			<div class="control-group">
				<input class="btn btn-primary" type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>"
				       name="submit"/>
				<button onclick="Joomla.submitbutton('admin.back')" class="btn btn-default">
					<span class="icon-back"></span>
					Back
				</button>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="task" value="admin.doArchive"/>
	<input type="hidden" name="controller" value="admin"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
