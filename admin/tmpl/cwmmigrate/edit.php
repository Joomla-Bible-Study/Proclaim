<?php
/**
 * Form sub migrate
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * @since      7.1.0
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('formbehavior.chosen', 'select');

Factory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'cwmadmin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'cwmadmin.doimport' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmigrate'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<h3><?php echo Text::_('JBS_CMN_IMPORT'); ?></h3>

			<div class="control-group">
				<?php echo Text::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br/>
				<?php echo Text::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JUri::base() . '../media/com_proclaim/images/icons/import.png'; ?>"
					     alt="Import" height="48" width="48"/>
				</div>
				<div class="controls">
					<div style="position:relative;">
						<a class='btn btn-primary' href="javascript:">
							Choose File...
							<input type="file"
							       style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;'
							       name="importdb" size="40"
							       onchange='jQuery("#upload-file-info").html(jQuery(this).val());'>
						</a>
						<span class='label label-info' id="upload-file-info"></span>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JUri::base() . '../media/com_proclaim/images/icons/backuprestore.png'; ?>"
					     alt="Backup Folder" height="48" width="48"/>

				</div>
				<div class="controls">
					<?php echo $this->lists['backedupfiles'] . ' - ' . Text::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JUri::base() . '../media/com_proclaim/images/icons/folder.png'; ?>"
					     alt="Tmp Folder" height="48" width="48"/>
				</div>
				<div class="controls">
					<?php echo ' - ' . Text::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
					<input type="text" id="install_directory" name="install_directory" class="input_box" size="70"
					       value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
				</div>
			</div>
			<div class="control-group">
				<input class="btn btn-primary" type="submit" value="<?php echo Text::_('JBS_CMN_SUBMIT'); ?>"
				       name="submit"/>
				<a href="index.php?option=com_proclaim&task=cwmadmin.edit&id=1">
					<button type="button" class="btn btn-default"><?php echo Text::_('JTOOLBAR_BACK'); ?></button>
				</a>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_proclaim"/>
	<input type="hidden" name="task" value="cwmadmin.doimport"/>
	<input type="hidden" name="controller" value="cwmadmin"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
