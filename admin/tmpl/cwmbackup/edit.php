<?php

/**
 * Form sub backup
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
use Joomla\CMS\Utility\Utility;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmbackup\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('bootstrap.modal')
    ->registerAndUseScript(
        'proclaim.backup-restore',
        'com_proclaim/backup-restore.min.js',
        ['version' => 'auto'],
        ['defer' => true],
        ['core']
    );

// Add language strings for JavaScript
Text::script('JBS_IBM_PROCESSING');
Text::script('JBS_IBM_EXPORTING_DATABASE');
Text::script('JBS_IBM_GETTING_TABLES');
Text::script('JBS_IBM_EXPORTING_TABLE');
Text::script('JBS_IBM_FINALIZING');
Text::script('JBS_IBM_EXPORT_COMPLETE');
Text::script('JBS_IBM_BACKUP_SAVED');
Text::script('JBS_IBM_IMPORTING_DATABASE');
Text::script('JBS_IBM_PREPARING_IMPORT');
Text::script('JBS_IBM_ANALYZING_FILE');
Text::script('JBS_IBM_IMPORTING_DATA');
Text::script('JBS_IBM_BATCH');
Text::script('JBS_IBM_FIXING_ASSETS');
Text::script('JBS_IBM_IMPORT_COMPLETE');
Text::script('JBS_IBM_COMPLETE');
Text::script('JBS_IBM_FAILED');
Text::script('JBS_CMN_NO_FILE_SELECTED');
Text::script('JCANCEL');
Text::script('JCLOSE');

$maxSize = HTMLHelper::_('number.bytes', Utility::getMaxUploadSize());
?>

<!-- Welcome Section -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <i class="fas fa-database fa-3x text-primary"></i>
            </div>
            <div class="col">
                <h2 class="card-title mb-1"><?php echo Text::_('JBS_IBM_WELCOME_TITLE'); ?></h2>
                <p class="card-text text-muted mb-0"><?php echo Text::_('JBS_IBM_WELCOME_DESC'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Export Section -->
    <div class="col-12 col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-download me-2"></i><?php echo Text::_('JBS_CMN_EXPORT'); ?>
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo Text::_('JBS_IBM_EXPORT_DESC'); ?></p>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg" data-proclaim-export="download">
                        <i class="fas fa-download me-2"></i><?php echo Text::_('JBS_CMN_EXPORT'); ?>
                    </button>
                    <button type="button" class="btn btn-secondary" data-proclaim-export="save">
                        <i class="fas fa-save me-2"></i><?php echo Text::_('JBS_IBM_SAVE_DB'); ?>
                    </button>
                </div>

                <?php if (!empty($this->lists['backedupfiles'])): ?>
                <hr>
                <h5><?php echo Text::_('JBS_IBM_EXISTING_BACKUPS'); ?></h5>
                <p class="small text-muted"><?php echo Text::_('JBS_IBM_EXISTING_BACKUPS_DESC'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Media Backup Help Section -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TITLE'); ?>
                </h5>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_DESC'); ?></p>
                <div class="alert alert-info">
                    <strong><?php echo Text::_('JBS_IBM_MEDIA_LOCATION'); ?>:</strong>
                    <code>/media/com_proclaim/</code>
                </div>
                <ul class="mb-0">
                    <li><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TIP1'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TIP2'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TIP3'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="col-12 col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-upload me-2"></i><?php echo Text::_('JBS_CMN_IMPORT'); ?>
                </h3>
            </div>
            <div class="card-body">
                <form id="proclaim-import-form" enctype="multipart/form-data">
                    <!-- Server Info -->
                    <div class="alert alert-secondary small">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo Text::_('JBS_IBM_MAX_UPLOAD') . ': ' . \ini_get('upload_max_filesize'); ?>
                        &nbsp;|&nbsp;
                        <?php echo Text::_('JBS_IBM_MAX_EXECUTION_TIME') . ': ' . \ini_get('max_execution_time') . 's'; ?>
                    </div>

                    <!-- Upload File -->
                    <div class="mb-4">
                        <label for="importdb" class="form-label fw-bold">
                            <i class="fas fa-file-upload me-1"></i>
                            <?php echo Text::_('JBS_IBM_UPLOAD_FILE'); ?>
                        </label>
                        <input type="file"
                               name="importdb"
                               id="importdb"
                               accept=".sql,.zip"
                               class="form-control">
                        <div class="form-text">
                            <?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
                            - <?php echo Text::_('JBS_IBM_ACCEPTS_SQL_ZIP'); ?>
                        </div>
                    </div>

                    <!-- From Backup Folder -->
                    <div class="mb-4">
                        <label for="backuprestore" class="form-label fw-bold">
                            <i class="fas fa-folder-open me-1"></i>
                            <?php echo Text::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
                        </label>
                        <?php echo $this->lists['backedupfiles']; ?>
                    </div>

                    <!-- From Tmp Folder -->
                    <div class="mb-4">
                        <label for="install_directory" class="form-label fw-bold">
                            <i class="fas fa-folder me-1"></i>
                            <?php echo Text::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
                        </label>
                        <input type="text"
                               id="install_directory"
                               name="install_directory"
                               class="form-control"
                               value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" type="submit">
                            <i class="fas fa-upload me-2"></i><?php echo Text::_('JBS_CMN_IMPORT'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Migration Notes -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i><?php echo Text::_('JBS_IBM_MIGRATION_NOTES'); ?>
                </h5>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_IBM_MIGRATION_DESC'); ?></p>
                <ul class="mb-0">
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP1'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP2'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP3'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="mt-3">
    <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmcpanel'); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i><?php echo Text::_('JTOOLBAR_BACK'); ?>
    </a>
</div>

<?php echo HTMLHelper::_('form.token'); ?>
