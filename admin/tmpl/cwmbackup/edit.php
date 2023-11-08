<?php
/**
 * Form sub backup
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript("
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
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmbackup'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <div class="col-12 col-lg-6 form-horizontal">
            <h3><?php echo Text::_('JBS_CMN_EXPORT'); ?></h3>

            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo Uri::base() . '../media/com_proclaim/images/icons/export.png'; ?>"
                         alt="Export" height="48" width="48"/></div>
                <div class="controls">
                    <!--suppress HtmlUnknownTarget -->
                    <a href="<?php echo Route::_("index.php?option=com_proclaim&task=cwmadmin.export&run=1&" .
	                    Session::getFormToken() . "=1"); ?>" class="btn btn-primary">
						<?php echo Text::_('JBS_CMN_EXPORT'); ?>
                    </a>
					<?php echo '<br /><br />'; ?>
                    <!--suppress HtmlUnknownTarget -->
                    <a href="index.php?option=com_proclaim&task=cwmadmin.export&run=2&<?php echo Session::getFormToken(); ?>=1"
                       class="btn btn-secondary">
						<?php echo Text::_('JBS_IBM_SAVE_DB'); ?>
                    </a>
                </div>
            </div>
            <hr/>
            <h3><?php echo Text::_('JBS_CMN_IMPORT'); ?></h3>
            <p>
				<?php echo Text::_('JBS_IBM_MAX_UPLOAD') . ': ' . ini_get('upload_max_filesize'); ?><br/>
			    <?php echo Text::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . ini_get('max_execution_time'); ?><br/>
            </p>
            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo Uri::base() . '../media/com_proclaim/images/icons/import.png'; ?>"
                         alt="Import" height="48" width="48"/>
                </div>
                <div class="controls">
                    <div style="position:relative;">
                        <label for="importdb" class="hidden">Import File Selection Button</label>
                        <a class='btn btn-primary' href="javascript:">
                            Choose File...
                            <input id="importdb" type="file"
                                   style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;'
                                   name="importdb" size="40"
                                   onchange='jQuery("#upload-file-info").html(jQuery(this).val());'>
                        </a>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo Uri::base() . '../media/com_proclaim/images/icons/backuprestore.png'; ?>"
                         alt="Backup Folder" height="48" width="48"/>

                </div>
                <div class="controls">
                    <label for="backuprestore"><?php echo ' - ' . Text::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER') ?>
                    </label>
	                <?php echo $this->lists['backedupfiles']; ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <img src="<?php echo Uri::base() . '../media/com_proclaim/images/icons/folder.png'; ?>"
                         alt="Tmp Folder" height="48" width="48"/>
                </div>
                <div class="controls">
                    <label for="install_directory"><?php echo ' - ' . Text::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
                    </label><input type="text" id="install_directory" name="install_directory"
                                   class="form-control inputbox valid form-control-success"
                                   value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>"/>
                </div>
            </div>
            <div class="control-group">
                <input class="btn btn-primary" type="submit" value="<?php echo Text::_('JBS_CMN_SUBMIT'); ?>"
                       name="submit"/>&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="index.php?option=com_proclaim&task=cwmadmin.edit&id=1">
                    <button type="button"
                            class="btn btn-dark btn-outline-light"><?php echo Text::_('JTOOLBAR_BACK'); ?></button>
                </a>
            </div>
        </div>
    </div>
    <input type="hidden" name="option" value="com_proclaim"/>
    <input type="hidden" name="task" value="cwmadmin.doimport"/>
    <input type="hidden" name="controller" value="admin"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
