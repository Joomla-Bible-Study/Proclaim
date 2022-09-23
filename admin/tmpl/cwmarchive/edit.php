<?php
/**
 * Form sub Archive
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * @since      7.1.0
 * */

// Load the tooltip behavior.
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('formbehavior.chosen', 'select');

defined('_JEXEC') or die;

Factory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'cwmadmin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmcpanel'); ?>" enctype="multipart/form-data"
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
				<input class="btn btn-primary" type="submit" value="<?php echo Text::_('JBS_CMN_SUBMIT'); ?>"
				       name="submit"/>
				<button onclick="Joomla.submitbutton('administration.back')" class="btn btn-default">
					<span class="icon-back"></span>
					Back
				</button>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_proclaim"/>
	<input type="hidden" name="task" value="admin.doArchive"/>
	<input type="hidden" name="controller" value="admin"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
