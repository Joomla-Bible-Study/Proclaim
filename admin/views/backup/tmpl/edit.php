<?php
/**
 * Form sub backup
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=migrate'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <h3><?php echo JText::_('JBS_CMN_EXPORT'); ?></h3>

            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo JUri::base() . '../media/com_biblestudy/images/icons/export.png'; ?>"
                         alt="Export" height="48" width="48"/></div>
                <div class="controls">
                    <!--suppress HtmlUnknownTarget -->
                    <a href="<?php echo JRoute::_("index.php?option=com_biblestudy&task=admin.export&run=1&" .
						JSession::getFormToken() . "=1"); ?>" class="btn btn-primary">
						<?php echo JText::_('JBS_CMN_EXPORT'); ?>
                    </a>
					<?php echo '<br /><br />'; ?>
                    <!--suppress HtmlUnknownTarget -->
                    <a href="index.php?option=com_biblestudy&task=admin.export&run=2&<?php echo JSession::getFormToken(); ?>=1"
                       class="btn">
						<?php echo JText::_('JBS_IBM_SAVE_DB'); ?>
                    </a>
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
                    <img src="<?php echo JUri::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
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
                    <img src="<?php echo JUri::base() . '../media/com_biblestudy/images/icons/backuprestore.png'; ?>"
                         alt="Backup Folder" height="48" width="48"/>

                </div>
                <div class="controls">
					<?php echo $this->lists['backedupfiles'] . ' - ' . JText::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo JUri::base() . '../media/com_biblestudy/images/icons/folder.png'; ?>"
                         alt="Tmp Folder" height="48" width="48"/>
                </div>
                <div class="controls">
					<?php echo ' - ' . JText::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
                    <input type="text" id="install_directory" name="install_directory" class="input_box" size="70"
                           value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
                </div>
            </div>
            <div class="control-group">
                <input class="btn btn-primary" type="submit" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>"
                       name="submit"/>
                <a href="index.php?option=com_biblestudy&task=admin.edit&id=1">
                    <button type="button" class="btn btn-default"><?php echo JText::_('JTOOLBAR_BACK'); ?></button>
                </a>
            </div>
        </div>
    </div>
    <input type="hidden" name="option" value="com_biblestudy"/>
    <input type="hidden" name="task" value="admin.doimport"/>
    <input type="hidden" name="controller" value="admin"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
