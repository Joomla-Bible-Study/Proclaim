<?php
/**
 * @package BibleStudy.Admin
 * @author Joomla Bible Study
 * @copyright 2012
 * @since 7.0.2
 * @desc Form to perform migration from 1.5 version to the latest JBS version
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
    <table>
        <tr>
            <td><img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>" alt="Import" height="48" width="48" /></td>
            <td>
                <strong><?php echo JText::_('JBS_CMN_IMPORT'); ?></strong>
            </td>
            <td>
                <input class="input_box" id="importdb" name="importdb" type="file" size="57" />
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>" alt="Tmp Folder" height="48" width="48" /></td><td>
                <strong><?php echo JText::_('JBS_IBM_IMPORT_FROM_FOLDER'); ?></strong>
            </td>
            <td>
                <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>" />
            </td>
        </tr>
        <tr>
            <td>
                <img src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/copydatabase.png'; ?>" alt="Import" height="48" width="48" />
            </td>
            <td>
                <strong><?php echo JText::_('JBS_IBM_OLDPREFIX'); ?></strong>
                <br />
                <?php echo JText::_('JBS_IBM_OLDPREFIXDESC1'); ?>
                <br />
                <?php echo JText::_('JBS_IBM_OLDPREFIXDESC2'); ?>
                <br />
                <?php echo JText::_('JBS_IBM_OLDPREFIXDESC3'); ?>
            </td>
            <td>
                <input type="text" id="oldprefix" name="oldprefix" class="input_box" size="20" />
            </td>
        </tr>
        <tr>
            <td>
            <td>
            <td>
                <input type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>" name="submit"  />
            </td>
            </td>
            </td>
        </tr>
        <tr>
            <td>
            <td>
            <td>
                <?php echo JText::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br />
                <?php echo JText::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?>
            </td>
            </td>
            </td>
        </tr>
        <tr>
            <td>
                <?php
                $ver = JVERSION;
                echo '<tr><td colspan="2"><strong> ' . JText::_('JBS_IBM_CURRENT_JOOMLA_VERSION') . ': </strong>' . $ver . '</td></tr>';
                ?>
            </td>
        </tr>
    </table>
    <div class="clr"> </div>
    <div>
        <input type="hidden" name="option" value="com_biblestudy" />
        <input type="hidden" name="task" value="migration.doimport" />
        <input type="hidden" name="controller" value="migration" />
    </div>

</form>

