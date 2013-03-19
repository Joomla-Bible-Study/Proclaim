<?php
/**
 * Main subview for default
 *
 * @package     BibleStudy
 * @subpackage  JBSMigration.Admin
 * @copyright   (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;
?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
    <table>

        <tr>
            <td width="10%">
                <a href="index.php?option=com_jbsmigration&task=export&run=1">
                    <img src="<?php echo JURI::base() . '/components/com_jbsmigration/images/export.png'; ?>"
                         alt="Export" height="48" width="48"/>
                </a>
            </td>
            <td>
                <a href="index.php?option=com_jbsmigration&task=export&run=1">
                    <strong>
						<?php echo JText::_('JBS_EI_EXPORT'); ?>
                    </strong>
                </a>
            </td>
            <td></td>
            <td></td>
        </tr>
		<?php $ver = JVERSION; ?>
        <tr>
            <td colspan="2">
                <strong>
					<?php JText::_('CURRENTVERSION') ?>:
                </strong>
				<?php $ver ?>
            </td>
        </tr>
    </table>

    <input type="hidden" name="option" value="com_jbsmigration"/>
    <input type="hidden" name="task" value="doimport"/>


</form>
