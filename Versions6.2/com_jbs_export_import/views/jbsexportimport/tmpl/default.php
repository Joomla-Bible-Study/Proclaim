<?php defined('_JEXEC') or die('Restricted access'); ?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
    <table width="100%">
        <tr>
            <td colspan="2">
                <h3><?php echo JText::_('JBS_EI_EXPORTING'); ?></h3>
            </td>
        </tr>
        <tr>
            <td width="48px">
                <img src="<?php echo JURI::base() . '/components/com_jbsexportimport/images/export.png'; ?>" alt="Export" height="48" width="48" /></td>
            <td align="left">
                <a href="index.php?option=com_jbsexportimport&task=export&run=1"> <?php echo JText::_('JBS_EI_EXPORT'); ?></a>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr/></td>
        </tr>
        <tr>
            <td colspan="2">
                <h3><?php echo JText::_('JBS_EI_IMPORTING'); ?></h3>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo JText::_('JBS_EI_UPLOAD_MAX') . ': ' . ini_get('upload_max_filesize'); ?><br />
                <?php echo JText::_('JBS_EI_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '/components/com_jbsexportimport/images/import.png'; ?>" alt="Import" height="48" width="48" /></td>
            <td>
                <?php echo JText::_('JBS_EI_IMPORT'); ?>
                <input class="input_box" id="importdb" name="importdb" type="file" size="57" />
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '/components/com_jbsexportimport/images/folder.png'; ?>" alt="Tmp Folder" height="48" width="48" />
            </td>
            <td>
                <?php echo JText::_('JBS_EI_IMPORT_FROM_FOLDER'); ?>
                <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->tmp_dest . DS; ?>" />
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <div class="button1">
                    <div class="next">
                        <a href="#" onclick="document.forms['adminForm'].submit(); return false;"><?php echo JText::_('JBS_EI_SUBMIT'); ?></a>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <input type="hidden" name="option" value="com_jbsexportimport" />
    <input type="hidden" name="task" value="doimport" />
</form>
