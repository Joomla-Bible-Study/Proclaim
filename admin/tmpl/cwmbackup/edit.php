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
use Joomla\CMS\Utility\Utility;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmbackup\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('bootstrap.modal')
    ->useScript('bootstrap.collapse')
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
<section aria-label="<?php echo Text::_('JBS_IBM_WELCOME_TITLE'); ?>" class="card border-primary mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-auto d-none d-md-block" aria-hidden="true">
                <span class="fa-stack fa-2x text-primary">
                    <i class="fas fa-circle fa-stack-2x" style="opacity: 0.1;"></i>
                    <i class="fas fa-database fa-stack-1x"></i>
                </span>
            </div>
            <div class="col">
                <h1 class="h4 mb-1 text-primary"><?php echo Text::_('JBS_IBM_WELCOME_TITLE'); ?></h1>
                <p class="text-muted mb-0 small"><?php echo Text::_('JBS_IBM_WELCOME_DESC'); ?></p>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <!-- Export Section -->
    <div class="col-12 col-lg-6 mb-4">
        <section aria-labelledby="export-heading" class="card h-100">
            <div class="card-header bg-primary text-white">
                <h2 id="export-heading" class="card-title h5 mb-0">
                    <i class="fas fa-download me-2" aria-hidden="true"></i><?php echo Text::_('JBS_CMN_EXPORT'); ?>
                </h2>
            </div>
            <div class="card-body">
                <p class="text-muted" id="export-desc"><?php echo Text::_('JBS_IBM_EXPORT_DESC'); ?></p>

                <div class="d-grid gap-2 mb-3" role="group" aria-label="<?php echo Text::_('JBS_CMN_EXPORT'); ?>">
                    <button type="button"
                            class="btn btn-primary"
                            data-proclaim-export="download"
                            aria-describedby="export-desc">
                        <i class="fas fa-download me-2" aria-hidden="true"></i><?php echo Text::_('JBS_IBM_DOWNLOAD_BACKUP'); ?>
                    </button>
                    <button type="button"
                            class="btn btn-outline-primary"
                            data-proclaim-export="save"
                            aria-describedby="export-desc">
                        <i class="fas fa-hdd me-2" aria-hidden="true"></i><?php echo Text::_('JBS_IBM_SAVE_DB'); ?>
                    </button>
                </div>

                <!-- Saved Backups Section -->
                <div class="border-top pt-3">
                    <h3 class="h6 text-muted mb-2">
                        <i class="fas fa-folder-open me-1" aria-hidden="true"></i>
                        <?php echo Text::_('JBS_IBM_EXISTING_BACKUPS'); ?>
                    </h3>
                    <p class="small text-muted mb-2" id="backups-desc"><?php echo Text::_('JBS_IBM_EXISTING_BACKUPS_DESC'); ?></p>
                    <div aria-describedby="backups-desc">
                        <?php echo $this->lists['backedupfiles']; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Import Section -->
    <div class="col-12 col-lg-6 mb-4">
        <section aria-labelledby="import-heading" class="card h-100">
            <div class="card-header bg-success text-white">
                <h2 id="import-heading" class="card-title h5 mb-0">
                    <i class="fas fa-upload me-2" aria-hidden="true"></i><?php echo Text::_('JBS_CMN_IMPORT'); ?>
                </h2>
            </div>
            <div class="card-body">
                <form id="proclaim-import-form" enctype="multipart/form-data" aria-label="<?php echo Text::_('JBS_CMN_IMPORT'); ?>">
                    <!-- Upload File -->
                    <div class="mb-3">
                        <label for="importdb" class="form-label fw-semibold">
                            <i class="fas fa-file-upload me-1 text-muted" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_IBM_UPLOAD_FILE'); ?>
                        </label>
                        <input type="file"
                               name="importdb"
                               id="importdb"
                               accept=".sql,.zip"
                               class="form-control"
                               aria-describedby="importdb-help">
                        <div class="form-text" id="importdb-help">
                            <?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
                            &bull; <?php echo Text::_('JBS_IBM_ACCEPTS_SQL_ZIP'); ?>
                        </div>
                    </div>

                    <!-- From Backup Folder -->
                    <div class="mb-3">
                        <label for="backuprestore" class="form-label fw-semibold">
                            <i class="fas fa-folder-open me-1 text-muted" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_IBM_IMPORT_FROM_BACKUP_FOLDER'); ?>
                        </label>
                        <?php echo $this->lists['backedupfiles']; ?>
                    </div>

                    <!-- From Tmp Folder - Collapsible -->
                    <div class="mb-3">
                        <button type="button"
                                class="btn btn-link text-decoration-none small p-0"
                                data-bs-toggle="collapse"
                                data-bs-target="#advancedImport"
                                aria-expanded="false"
                                aria-controls="advancedImport">
                            <i class="fas fa-chevron-right me-1" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_IBM_ADVANCED_OPTIONS'); ?>
                        </button>
                        <div class="collapse mt-2" id="advancedImport">
                            <label for="install_directory" class="form-label small">
                                <?php echo Text::_('JBS_IBM_IMPORT_FROM_TMP_FOLDER'); ?>
                            </label>
                            <input type="text"
                                   id="install_directory"
                                   name="install_directory"
                                   class="form-control form-control-sm"
                                   value="<?php echo $this->tmp_dest . DIRECTORY_SEPARATOR; ?>">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-upload me-2" aria-hidden="true"></i><?php echo Text::_('JBS_IBM_START_IMPORT'); ?>
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light small text-muted" role="contentinfo" aria-label="<?php echo Text::_('JBS_CMN_SERVER_INFO'); ?>">
                <i class="fas fa-server me-1" aria-hidden="true"></i>
                <?php echo Text::_('JBS_IBM_MAX_UPLOAD'); ?>: <strong><?php echo \ini_get('upload_max_filesize'); ?></strong>
                <span aria-hidden="true">&nbsp;&bull;&nbsp;</span>
                <?php echo Text::_('JBS_IBM_TIMEOUT'); ?>: <strong><?php echo \ini_get('max_execution_time'); ?>s</strong>
            </div>
        </section>
    </div>
</div>

<!-- Info Cards Row -->
<div class="row">
    <!-- Media Backup Help -->
    <div class="col-12 col-lg-6 mb-4">
        <section aria-labelledby="media-warning-heading" class="card border-warning" role="alert">
            <div class="card-header bg-warning bg-opacity-10 border-warning">
                <h2 id="media-warning-heading" class="card-title h6 mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TITLE'); ?>
                </h2>
            </div>
            <div class="card-body">
                <p class="small mb-2"><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_DESC'); ?></p>
                <div class="bg-light rounded p-2 mb-2">
                    <code class="small">/media/com_proclaim/</code>
                </div>
                <ul class="small mb-0 ps-3">
                    <li><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TIP1'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MEDIA_BACKUP_TIP2'); ?></li>
                </ul>
            </div>
        </section>
    </div>

    <!-- Migration Notes -->
    <div class="col-12 col-lg-6 mb-4">
        <section aria-labelledby="migration-heading" class="card border-info">
            <div class="card-header bg-info bg-opacity-10 border-info">
                <h2 id="migration-heading" class="card-title h6 mb-0 text-info">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i><?php echo Text::_('JBS_IBM_MIGRATION_NOTES'); ?>
                </h2>
            </div>
            <div class="card-body">
                <p class="small mb-2"><?php echo Text::_('JBS_IBM_MIGRATION_DESC'); ?></p>
                <ul class="small mb-0 ps-3">
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP1'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP2'); ?></li>
                    <li><?php echo Text::_('JBS_IBM_MIGRATION_TIP3'); ?></li>
                </ul>
            </div>
        </section>
    </div>
</div>

<?php echo HTMLHelper::_('form.token'); ?>
