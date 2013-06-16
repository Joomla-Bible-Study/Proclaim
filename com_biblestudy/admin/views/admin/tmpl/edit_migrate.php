<?php
/**
 * Form sub migrate
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      7.1.0
 * */

defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=admin'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<table class="row-fluid">
			<tr>
				<td><img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
				         alt="Import"
				         height="48" width="48"/></td>
				<td>
					<strong><?php echo JText::_('JBS_IBM_IMPORT_FROM_FILE'); ?></strong>
				</td>
				<td>
					<input class="input_box" id="importdb" name="importdb" type="file" size="57"/>
				</td>
			</tr>
			<tr>
				<td>
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>"
					     alt="Tmp Folder" height="48" width="48"/></td>
				<td>
					<strong><?php echo JText::_('JBS_IBM_IMPORT_MIGRATE_FROM_SERVER_FOLDER'); ?></strong>
				</td>
				<td>
					<input type="text" id="install_directory" name="install_directory" class="input_box" size="70"
					       value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
					<img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/copydatabase.png'; ?>"
					     alt="Import" height="48" width="48"/>
				</td>
				<td>
					<strong><?php echo JText::_('JBS_IBM_OLDPREFIX'); ?></strong>
					<br/>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC1'); ?>
					<br/>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC2'); ?>
					<br/>
					<?php echo JText::_('JBS_IBM_OLDPREFIXDESC3'); ?>
				</td>
				<td>
					<input type="text" id="oldprefix" name="oldprefix" class="input_box" size="20"/>
				</td>
			</tr>
			<tr>
				<td>
					<input type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>" name="submit"/>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php echo JText::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br/>
					<?php echo JText::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php
					$ver = JVERSION;
					echo '<tr><td colspan="2"><strong> ' . JText::_('JBS_IBM_CURRENT_JOOMLA_VERSION') . ': </strong>' . $ver . '</td></tr>';
					?>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="task" value="admin.doimport"/>
	<input type="hidden" name="controller" value="admin"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

