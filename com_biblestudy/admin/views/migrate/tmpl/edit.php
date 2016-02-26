<?php
/**
 * Form sub migrate
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      7.1.0
 * */

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('jquery.framework');

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'admin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'admin.doimport' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=admin&id=1'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12 form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
					     alt="Import" height="48" width="48"/>
					<strong><?php echo JText::_('JBS_IBM_IMPORT_FROM_FILE'); ?></strong>
				</div>
				<div class="controls">
					<input class="input_box" id="importdb" name="importdb" type="file" size="57"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>"
					     alt="Tmp Folder" height="48" width="48"/>
					<strong><?php echo JText::_('JBS_IBM_IMPORT_MIGRATE_FROM_SERVER_FOLDER'); ?></strong>
				</div>
				<div class="controls">
					<input type="text" id="install_directory" name="install_directory" class="input_box" size="70"
					       value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/copydatabase.png'; ?>"
					     alt="Import" height="48" width="48"/>
					<strong><?php echo JText::_('JBS_IBM_OLDPREFIX'); ?></strong>
				</div>
				<div class="controls">
					<input type="text" id="oldprefix" name="oldprefix" class="input_box" size="20"/>
				</div>
				<div class="control-label">
					<p>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC1'); ?>
					<br/>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC2'); ?>
					<br/>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC3'); ?>
					</p>
				</div>
			</div>
			<div class="control-group">
				<button onclick="Joomla.submitbutton('admin.doimport')" class="btn btn-primary"><?php echo JText::_('JBS_CMN_SUBMIT'); ?></button>
				<a href="index.php?option=com_biblestudy&task=admin.edit&id=1">
					<button type="button" class="btn btn-default"><?php echo JText::_('JTOOLBAR_BACK'); ?></button>
				</a>
			</div>
		</div>
		<div class="control-group">
			<?php echo JText::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br/>
			<?php echo JText::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
			<?php
			$ver = JVERSION;
			echo '<tr><td colspan="2"><strong> ' . JText::_('JBS_IBM_CURRENT_JOOMLA_VERSION') . ': </strong>' . $ver . '</td></tr>';
			?>
		</div>
	</div>
	</div>
	<input type="hidden" name="task" value="admin.doimport"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
