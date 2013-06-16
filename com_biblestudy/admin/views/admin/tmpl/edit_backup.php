<?php
/**
 * Form sub backup
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<h3><?php echo JText::_('JBS_CMN_EXPORT'); ?></h3>

			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/export.png'; ?>"
					     alt="Export" height="48" width="48"/></div>
				<div class="controls">
					<!--suppress HtmlUnknownTarget -->
					<a href="index.php?option=com_biblestudy&task=admin.export&run=1"> <?php echo JText::_('JBS_CMN_EXPORT'); ?></a>
					<?php echo '<br /><br />'; ?>
					<!--suppress HtmlUnknownTarget -->
					<a href="index.php?option=com_biblestudy&task=admin.export&run=2"> <?php echo JText::_('JBS_IBM_SAVE_DB'); ?></a>
				</div>
			</div>
			<hr/>
			<h3><?php echo JText::_('JBS_CMN_IMPORT'); ?></h3>

			<div class="control-group">
				<?php echo JText::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br/>
				<?php echo JText::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
					     alt="Import" height="48" width="48"/>
				</div>
				<div class="controls">
					<input class="input_box" id="importdb" name="importdb" type="file" size="57"/>
					<?php echo ' - ' . JText::_('JBS_IBM_IMPORT_ONLY'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/backuprestore.png'; ?>"
					     alt="Backup Folder" height="48" width="48"/>

				</div>
				<div class="controls">
					<?php echo $this->lists['backedupfiles'] . ' - ' . JText::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>"
					     alt="Tmp Folder" height="48" width="48"/>
				</div>
				<div class="controls">
					<?php echo ' - ' . JText::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
					<input type="text" id="install_directory" name="install_directory" class="input_box" size="70"
					       value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
				</div>
			</div>
			<div class="control-group">
				<input type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>" name="submit"/>
			</div>
		</div>
	</div>
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="task" value="admin.import"/>
	<input type="hidden" name="controller" value="admin"/>
</form>
