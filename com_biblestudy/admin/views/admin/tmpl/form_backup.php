<?php
/**
 * @version $Id: form_backup.php 1 $
 * @package Bible Study
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
    <table width="100%">
        <tr>
            <td colspan="2">
                <h3><?php echo JText::_('JBS_CMN_EXPORT'); ?></h3>
            </td>
        </tr>
        <tr>
            <td width="48px">
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/export.png'; ?>" alt="Export" height="48" width="48" /></td>
            <td align="left">
                <a href="index.php?option=com_biblestudy&task=migration.export&run=1"> <?php echo JText::_('JBS_CMN_EXPORT'); ?></a>
                <?php echo '<br /><br />'; ?>
                <a href="index.php?option=com_biblestudy&task=migration.export&run=2"> <?php echo JText::_('JBS_IBM_SAVE_DB'); ?></a>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr/></td>
        </tr>
        <tr>
            <td colspan="2">
                <h3><?php echo JText::_('JBS_CMN_IMPORT'); ?></h3>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo JText::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br />
                <?php echo JText::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>" alt="Import" height="48" width="48" />
            </td>
            <td>
                <input class="input_box" id="importdb" name="importdb" type="file" size="57" />
                <?php echo ' - ' . JText::_('JBS_IBM_IMPORT_ONLY'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/backuprestore.png'; ?>" alt="Backup Folder" height="48" width="48" />

            </td>
            <td>
                <?php echo $this->lists['backedupfiles'] . ' - ' . JText::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>" alt="Tmp Folder" height="48" width="48" />
            </td>
            <td>
                <?php echo ' - ' . JText::_('JBS_IBM_IMPORT_FROM_FOLDER_ONLY'); ?>
                <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>" />
            </td>
        </tr>
        <tr>
            <td >
                <input type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>" name="submit"  />
            </td>
        </tr>
    </table>
    <input type="hidden" name="option" value="com_biblestudy" />
    <input type="hidden" name="task" value="migration.import" />
    <input type="hidden" name="controller" value="migration" />
</form>
