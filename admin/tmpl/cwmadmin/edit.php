<?php

/**
 * Admin Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmadmin\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_proclaim.cwmadmin')
    ->useStyle('com_proclaim.general');

// Make language strings available to JavaScript
Text::script('JBS_ADM_STATS_LOADED');
Text::script('JBS_ADM_ALIAS_UPDATING');
Text::script('JBS_ADM_ALIAS_COMPLETE');
Text::script('JBS_ADM_ALIAS_NONE');
Text::script('JBS_ADM_ERROR');
Text::script('JBS_ADM_SELECT_FROM_TO');
Text::script('JBS_ADM_PLAYER_TOOLS_PROCESSING');
Text::script('JBS_ADM_PLAYER_TOOLS_COMPLETE');
Text::script('JBS_ADM_THUMBNAIL_RESIZE_CONFIRM');
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');

$app   = Factory::getApplication();
$input = $app->input;

$this->useCoreUI = true;
?>
<!-- Bootstrap 5 Modal for Thumbnail Resize -->
<div class="modal fade" id="dialog_thumbnail_resize" tabindex="-1"
     aria-labelledby="thumbnailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thumbnailModalLabel"><?php echo Text::_('JBS_ADM_CREATING_THUMBNAILS'); ?></h5>
            </div>
            <div class="modal-body">
                <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo Text::_('JBS_ADM_THUMBNAIL_PROGRESS'); ?>">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                </div>
                <p class="status-text text-center mt-2 mb-0" aria-live="polite"></p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Modal for Alias Update -->
<div class="modal fade" id="alias-update-modal" tabindex="-1"
     aria-labelledby="aliasModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aliasModalLabel">
                    <i class="icon-tree-2 me-2" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_ADM_ALIAS_UPDATE'); ?>
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="alias-spinner mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo Text::_('JBS_ADM_LOADING'); ?></span>
                    </div>
                </div>
                <p class="alias-status-text fw-bold mb-2" aria-live="polite"><?php echo Text::_('JBS_ADM_ALIAS_UPDATING'); ?></p>
                <p class="alias-result-text text-muted small mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center" style="display: none;">
                <button type="button" class="btn btn-success btn-close-alias-modal">
                    <i class="icon-checkmark me-1" aria-hidden="true"></i><?php echo Text::_('JCLOSE'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Modal for Player Tools -->
<div class="modal fade" id="player-tools-modal" tabindex="-1"
     aria-labelledby="playerToolsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="playerToolsModalLabel">
                    <i class="icon-cog me-2" aria-hidden="true"></i>
                    <span class="modal-title-text"><?php echo Text::_('JBS_ADM_PLAYER_TOOLS_TITLE'); ?></span>
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="player-tools-spinner mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo Text::_('JBS_ADM_LOADING'); ?></span>
                    </div>
                </div>
                <p class="player-tools-status-text fw-bold mb-2" aria-live="polite"><?php echo Text::_('JBS_ADM_PLAYER_TOOLS_PROCESSING'); ?></p>
                <p class="player-tools-result-text text-muted small mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center" style="display: none;">
                <button type="button" class="btn btn-success btn-close-player-tools-modal">
                    <i class="icon-checkmark me-1" aria-hidden="true"></i><?php echo Text::_('JCLOSE'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-admin"
      aria-label="<?php
        echo Text::_('COM_CONTENT_FORM_TITLE_' . ((int)$this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
      class="form-validate">
    <?php
    echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
    <div class="row">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'cpanl', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'cpanl', Text::_('JBS_ADM_ADMIN_CPANL')); ?>
        <!-- Begin Tabs -->
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card card-body">
                    <div class="cwmadmin-dashboard-cards">
                        <a href="<?php
                        echo Route::_(
                            'index.php?option=com_proclaim&view=cwmassets&task=cwmassets.checkassets&' .
                            Session::getFormToken() . '=1'
                        ); ?>"
                           class="cwmadmin-action-card"
                           title="<?php echo Text::_('JBS_ADM_ASSET_CHECK'); ?>">
                            <i class="icon-list" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_ASSET_CHECK'); ?></span>
                        </a>
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmbackup'); ?>"
                           class="cwmadmin-action-card"
                           title="<?php echo Text::_('JBS_ADM_BACKUP_RESTORE'); ?>">
                            <i class="icon-database" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_BACKUP_RESTORE'); ?></span>
                        </a>
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmarchive'); ?>"
                           class="cwmadmin-action-card"
                           title="<?php echo Text::_('JBS_ADM_ARCHIVE'); ?>">
                            <i class="icon-archive" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_ARCHIVE'); ?></span>
                        </a>
                        <button type="button"
                           class="cwmadmin-action-card"
                           id="btn-alias-update"
                           title="<?php echo Text::_('JBS_ADM_RESET_ALIAS'); ?>">
                            <i class="icon-tree-2" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_RESET_ALIAS'); ?></span>
                        </button>
                        <a href="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>"
                           class="cwmadmin-action-card"
                           title="<?php echo Text::_('JBS_ADM_DATABASE'); ?>">
                            <i class="icon-database" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_DATABASE'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'administrator', Text::_('JBS_ADM_ADMIN_PARAMS')); ?>
        <div class="row">
            <h3 class="tab-description"><?php echo Text::_('JBS_ADM_COMPONENT_SETTINGS'); ?></h3>
            <div class="col-12 col-lg-6">
                <?php echo $this->form->renderField('simple_mode', 'params'); ?>
                <?php echo $this->form->renderField('simple_mode_template', 'params'); ?>
                <?php echo $this->form->renderField('simplegridtextoverlay', 'params'); ?>
                <?php echo $this->form->renderField('simple_mode_display', 'params'); ?>
                <?php echo $this->form->renderField('users', 'params'); ?>
                <?php echo $this->form->renderField('metakey', 'params'); ?>
                <?php echo $this->form->renderField('metadesc', 'params'); ?>
                <?php echo $this->form->renderField('compat_mode', 'params'); ?>
                <?php echo $this->form->renderField('drop_tables'); ?>
            </div>
            <div class="col-12 col-lg-6">
                <?php echo $this->form->renderField('filestokeep'); ?>
                <?php echo $this->form->renderField('studylistlimit', 'params'); ?>
                <?php echo $this->form->renderField('show_location_media', 'params'); ?>
                <?php echo $this->form->renderField('popular_limit', 'params'); ?>
                <?php echo $this->form->renderField('character_filter', 'params'); ?>
                <?php echo $this->form->renderField('format_popular', 'params'); ?>
                <?php echo $this->form->renderField('debug'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'defaults', Text::_('JBS_ADM_SYSTEM_DEFAULTS')); ?>
        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php echo Text::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h3>
                <?php echo $this->form->renderField('location_id', 'params'); ?>
                <?php echo $this->form->renderField('teacher_id', 'params'); ?>
                <?php echo $this->form->renderField('series_id', 'params'); ?>
                <?php echo $this->form->renderField('booknumber', 'params'); ?>
                <?php echo $this->form->renderField('messagetype', 'params'); ?>
                <?php echo $this->form->renderField('main_image_icon_or_image', 'params'); ?>
                <?php echo $this->form->renderField('default_study_image', 'params'); ?>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php echo Text::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h3>
                <?php echo $this->form->renderField('download', 'params'); ?>
                <?php echo $this->form->renderField('target', 'params'); ?>
                <?php echo $this->form->renderField('server', 'params'); ?>
                <?php echo $this->form->renderField('podcast', 'params'); ?>
                <?php echo $this->form->renderField('uploadpath', 'params'); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php echo Text::_('JBS_CMN_DEFAULT_IMAGES'); ?></h3>
                <?php echo $this->form->renderField('default_main_image', 'params'); ?>
                <?php echo $this->form->renderField('default_series_image', 'params'); ?>
                <?php echo $this->form->renderField('default_teacher_image', 'params'); ?>
                <?php echo $this->form->renderField('download_show', 'params'); ?>
                <?php echo $this->form->renderField('download_use_button_icon', 'params'); ?>
                <?php echo $this->form->renderField('default_download_image', 'params'); ?>
                <?php echo $this->form->renderField('download_button_text', 'params'); ?>
                <?php echo $this->form->renderField('download_button_type', 'params'); ?>
                <?php echo $this->form->renderField('download_button_color', 'params'); ?>
                <?php echo $this->form->renderField('download_icon_type', 'params'); ?>
                <?php echo $this->form->renderField('download_custom_icon', 'params'); ?>
                <?php echo $this->form->renderField('download_icon_text_size', 'params'); ?>
                <?php echo $this->form->renderField('default_showHide_image', 'params'); ?>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php echo Text::_('JBS_CMN_DEFAULT_IMAGES_SIZES'); ?></h3>
                <?php echo $this->form->renderField('thumbnail_teacher_size', 'params'); ?>
                <?php echo $this->form->renderField('thumbnail_series_size', 'params'); ?>
                <?php echo $this->form->renderField('thumbnail_study_size', 'params'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'playersettings', Text::_('JBS_ADM_PLAYER_SETTINGS')); ?>
        <div class="row" id="playersettings">
            <div class="col-12 col-lg-4">
                <div class="cwmadmin-panel">
                    <h3 class="tab-description"><?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?></h3>

                    <div class="control-group">
                        <label><?php echo Text::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?></label>
                        <div id="player-stats-container" class="cwmadmin-stats-container">
                            <div class="cwmadmin-stats-loading">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('from', 'params'); ?>
                    <?php echo $this->form->renderField('to', 'params'); ?>
                    <div class="control-group">
                        <button type="button" class="btn btn-primary"
                                data-player-tool="players"
                                data-from-field="jform_params_from"
                                data-to-field="jform_params_to"
                                data-title="<?php echo Text::_('JBS_ADM_CHANGE_PLAYERS'); ?>">
                            <i class="icon-cog icon-white" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_CMN_SUBMIT'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="cwmadmin-panel">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_POPUP_OPTIONS'); ?></h3>

                    <div class="control-group">
                        <label><?php echo Text::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?></label>
                        <div id="popup-stats-container" class="cwmadmin-stats-container">
                            <div class="cwmadmin-stats-loading">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('pFrom', 'params'); ?>
                    <?php echo $this->form->renderField('pTo', 'params'); ?>
                    <div class="control-group">
                        <button type="button" class="btn btn-primary"
                                data-player-tool="popups"
                                data-from-field="jform_params_pFrom"
                                data-to-field="jform_params_pTo"
                                data-title="<?php echo Text::_('JBS_ADM_CHANGE_POPUP'); ?>">
                            <i class="icon-cog icon-white" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_CMN_SUBMIT'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="cwmadmin-panel">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_MEDIATYPES_OPTIONS'); ?></h3>

                    <?php echo $this->form->renderField('mtFrom', 'params'); ?>
                    <?php echo $this->form->renderField('mtTo', 'params'); ?>
                    <div class="control-group">
                        <button type="button" class="btn btn-primary"
                                data-player-tool="playerbymediatype"
                                data-from-field="jform_params_mtFrom"
                                data-to-field="jform_params_mtTo"
                                data-title="<?php echo Text::_('JBS_ADM_MEDIATYPES_OPTIONS'); ?>">
                            <i class="icon-cog icon-white" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_CMN_SUBMIT'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'convert', Text::_('JBS_IBM_CONVERT'));
        // Check if SermonSpeaker or PreachIt is installed
        $ssInstalled = strpos($this->ss, 'href=') !== false;
        $piInstalled = strpos($this->pi, 'href=') !== false;
        ?>
        <div class="row" id="convert">
            <div class="col-12">
                <h4><?php echo Text::_('JBS_IBM_CONVERT'); ?></h4>
                <p class="text-muted"><?php echo Text::_('JBS_IBM_CONVERT_DESC'); ?></p>

                <?php if (!$ssInstalled && !$piInstalled) : ?>
                    <div class="alert alert-info">
                        <i class="icon-info-circle me-2" aria-hidden="true"></i>
                        <?php echo Text::_('JBS_IBM_NO_CONVERSION_AVAILABLE'); ?>
                    </div>
                <?php else : ?>
                    <div class="cwmadmin-dashboard-cards" style="max-width: 400px;">
                        <?php if ($ssInstalled) : ?>
                            <a href="<?php
                            echo Route::_(
                                'index.php?option=com_proclaim&view=assets&task=cwmadmin.convertSermonSpeaker&' .
                                Session::getFormToken() . '=1'
                            ); ?>"
                               class="cwmadmin-action-card"
                               title="<?php echo Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER'); ?>">
                                <i class="icon-book" aria-hidden="true"></i>
                                <span><?php echo Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER'); ?></span>
                            </a>
                        <?php else : ?>
                            <div class="cwmadmin-action-card disabled" title="<?php echo Text::_('JBS_IBM_NO_SERMON_SPEAKER_FOUND'); ?>">
                                <i class="icon-book text-muted" aria-hidden="true"></i>
                                <span class="text-muted"><?php echo Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER'); ?></span>
                                <small class="text-danger d-block mt-1"><?php echo Text::_('JBS_IBM_NOT_INSTALLED'); ?></small>
                            </div>
                        <?php endif; ?>

                        <?php if ($piInstalled) : ?>
                            <a href="<?php
                            echo Route::_(
                                'index.php?option=com_proclaim&view=assets&task=cwmadmin.convertPreachIt&' .
                                Session::getFormToken() . '=1'
                            ); ?>"
                               class="cwmadmin-action-card"
                               title="<?php echo Text::_('JBS_IBM_CONVERT_PREACH_IT'); ?>">
                                <i class="icon-list" aria-hidden="true"></i>
                                <span><?php echo Text::_('JBS_IBM_CONVERT_PREACH_IT'); ?></span>
                            </a>
                        <?php else : ?>
                            <div class="cwmadmin-action-card disabled" title="<?php echo Text::_('JBS_IBM_NO_PREACHIT_FOUND'); ?>">
                                <i class="icon-list text-muted" aria-hidden="true"></i>
                                <span class="text-muted"><?php echo Text::_('JBS_IBM_CONVERT_PREACH_IT'); ?></span>
                                <small class="text-danger d-block mt-1"><?php echo Text::_('JBS_IBM_NOT_INSTALLED'); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'imagetools', Text::_('JBS_ADM_IMAGE_TOOLS')); ?>
        <div class="row" id="imagetools">
            <!-- Image Migration Section -->
            <div class="col-12 col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><?php echo Text::_('JBS_ADM_IMAGE_MIGRATION'); ?></h4>
                    </div>
                    <div class="card-body">
                        <p><?php echo Text::_('JBS_ADM_IMAGE_MIGRATION_DESC'); ?></p>
                        <div id="migration-counts" class="mb-3">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <?php echo Text::_('JBS_ADM_LOADING'); ?>
                        </div>
                        <div id="migration-progress" class="mb-3" style="display:none;">
                            <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="mt-2" id="migration-status"></div>
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-start-migration" disabled>
                            <i class="icon-refresh" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_START_MIGRATION'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orphan Cleanup Section -->
            <div class="col-12 col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4><?php echo Text::_('JBS_ADM_ORPHAN_CLEANUP'); ?></h4>
                    </div>
                    <div class="card-body">
                        <p><?php echo Text::_('JBS_ADM_ORPHAN_CLEANUP_DESC'); ?></p>
                        <div id="orphan-status" class="mb-3">
                            <button type="button" class="btn btn-secondary" id="btn-scan-orphans">
                                <i class="icon-search" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>
                            </button>
                        </div>
                        <div id="orphan-results" class="mb-3" style="display:none;">
                            <div class="alert alert-info" id="orphan-summary"></div>
                            <div id="orphan-list" class="cwmadmin-orphan-list table-responsive"></div>
                        </div>
                        <button type="button" class="btn btn-danger" id="btn-delete-orphans" style="display:none;">
                            <i class="icon-trash" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_DELETE_SELECTED'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var token = '<?php echo Session::getFormToken(); ?>';

            // Load migration counts on page load
            loadMigrationCounts();

            function loadMigrationCounts() {
                fetch('index.php?option=com_proclaim&task=cwmadmin.getMigrationCountsXHR&' + token + '=1')
                    .then(response => response.json())
                    .then(data => {
                        var countsHtml = '<ul class="list-unstyled">' +
                            '<li><strong><?php echo Text::_('JBS_CMN_STUDIES'); ?>:</strong> ' + data.studies + '</li>' +
                            '<li><strong><?php echo Text::_('JBS_CMN_TEACHERS'); ?>:</strong> ' + data.teachers + '</li>' +
                            '<li><strong><?php echo Text::_('JBS_CMN_SERIES'); ?>:</strong> ' + data.series + '</li>' +
                            '<li><strong><?php echo Text::_('JBS_CMN_TOTAL'); ?>:</strong> ' + data.total + '</li>' +
                            '</ul>';
                        document.getElementById('migration-counts').innerHTML = countsHtml;
                        document.getElementById('btn-start-migration').disabled = (data.total === 0);
                    })
                    .catch(error => {
                        document.getElementById('migration-counts').innerHTML =
                            '<span class="text-danger"><?php echo Text::_('JBS_ADM_ERROR_LOADING'); ?></span>';
                    });
            }

            // Start migration
            document.getElementById('btn-start-migration').addEventListener('click', function() {
                var btn = this;
                btn.disabled = true;
                document.getElementById('migration-progress').style.display = 'block';

                var types = ['studies', 'teachers', 'series'];
                var typeIndex = 0;
                var totalMigrated = 0;
                var totalErrors = 0;

                function migrateType() {
                    if (typeIndex >= types.length) {
                        document.getElementById('migration-status').innerHTML =
                            '<span class="text-success"><?php echo Text::_('JBS_ADM_MIGRATION_COMPLETE'); ?> ' +
                            totalMigrated + ' <?php echo Text::_('JBS_ADM_RECORDS_MIGRATED'); ?></span>';
                        loadMigrationCounts();
                        return;
                    }

                    var type = types[typeIndex];
                    document.getElementById('migration-status').textContent =
                        '<?php echo Text::_('JBS_ADM_MIGRATING'); ?> ' + type + '...';

                    migrateBatch(type);
                }

                function migrateBatch(type) {
                    fetch('index.php?option=com_proclaim&task=cwmadmin.getMigrationBatchXHR&' + token + '=1&type=' + type + '&limit=5')
                        .then(response => response.json())
                        .then(data => {
                            if (data.records.length === 0) {
                                typeIndex++;
                                migrateType();
                                return;
                            }

                            var promises = data.records.map(function(record) {
                                var params = new URLSearchParams();
                                params.append('type', type);
                                params.append('id', record.id);
                                params.append('title', record.studytitle || record.teachername || record.title || '');
                                params.append('old_path', record.image_path);

                                return fetch('index.php?option=com_proclaim&task=cwmadmin.migrateRecordXHR&' + token + '=1&' + params.toString())
                                    .then(response => response.json())
                                    .then(result => {
                                        if (result.success) {
                                            totalMigrated++;
                                        } else {
                                            totalErrors++;
                                        }
                                        return result;
                                    });
                            });

                            Promise.all(promises).then(function() {
                                var progress = ((typeIndex + 1) / types.length) * 100;
                                document.querySelector('#migration-progress .progress-bar').style.width = progress + '%';

                                if (data.remaining > 0) {
                                    migrateBatch(type);
                                } else {
                                    typeIndex++;
                                    migrateType();
                                }
                            });
                        })
                        .catch(error => {
                            document.getElementById('migration-status').innerHTML =
                                '<span class="text-danger"><?php echo Text::_('JBS_ADM_MIGRATION_ERROR'); ?></span>';
                        });
                }

                migrateType();
            });

            // Scan for orphans
            document.getElementById('btn-scan-orphans').addEventListener('click', function() {
                var btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> <?php echo Text::_('JBS_ADM_SCANNING'); ?>';

                fetch('index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&' + token + '=1')
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="icon-search" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>';

                        document.getElementById('orphan-results').style.display = 'block';
                        document.getElementById('orphan-summary').innerHTML =
                            '<?php echo Text::_('JBS_ADM_FOUND'); ?> <strong>' + data.totals.folders + '</strong> <?php echo Text::_('JBS_ADM_ORPHAN_FOLDERS'); ?> (' + data.totals.size_formatted + ')';

                        if (data.totals.folders > 0) {
                            var tableHtml = '<table class="table table-sm table-striped">' +
                                '<thead><tr><th><input type="checkbox" id="select-all-orphans" aria-label="<?php echo Text::_('JBS_ADM_SELECT_ALL'); ?>"></th><th><?php echo Text::_('JBS_ADM_FOLDER'); ?></th><th><?php echo Text::_('JBS_ADM_FILES'); ?></th><th><?php echo Text::_('JBS_ADM_SIZE'); ?></th></tr></thead><tbody>';

                            ['studies', 'teachers', 'series'].forEach(function(type) {
                                if (data.orphans[type] && data.orphans[type].length > 0) {
                                    data.orphans[type].forEach(function(orphan) {
                                        tableHtml += '<tr>' +
                                            '<td><input type="checkbox" class="orphan-checkbox" value="' + orphan.path + '" aria-label="' + orphan.path + '"></td>' +
                                            '<td><small>' + orphan.path + '</small></td>' +
                                            '<td>' + orphan.files + '</td>' +
                                            '<td>' + formatBytes(orphan.size) + '</td>' +
                                            '</tr>';
                                    });
                                }
                            });

                            tableHtml += '</tbody></table>';
                            document.getElementById('orphan-list').innerHTML = tableHtml;
                            document.getElementById('btn-delete-orphans').style.display = 'inline-block';

                            document.getElementById('select-all-orphans').addEventListener('change', function() {
                                document.querySelectorAll('.orphan-checkbox').forEach(function(cb) {
                                    cb.checked = this.checked;
                                }.bind(this));
                            });
                        } else {
                            document.getElementById('orphan-list').innerHTML =
                                '<p class="text-success"><?php echo Text::_('JBS_ADM_NO_ORPHANS'); ?></p>';
                            document.getElementById('btn-delete-orphans').style.display = 'none';
                        }
                    })
                    .catch(error => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="icon-search" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>';
                        alert('<?php echo Text::_('JBS_ADM_ERROR_SCANNING'); ?>');
                    });
            });

            // Delete orphans
            document.getElementById('btn-delete-orphans').addEventListener('click', function() {
                var selected = [];
                document.querySelectorAll('.orphan-checkbox:checked').forEach(function(cb) {
                    selected.push(cb.value);
                });

                if (selected.length === 0) {
                    alert('<?php echo Text::_('JBS_ADM_SELECT_FOLDERS'); ?>');
                    return;
                }

                if (!confirm('<?php echo Text::_('JBS_ADM_CONFIRM_DELETE'); ?> ' + selected.length + ' <?php echo Text::_('JBS_ADM_FOLDERS'); ?>?')) {
                    return;
                }

                var btn = this;
                btn.disabled = true;

                var params = new URLSearchParams();
                selected.forEach(function(path) {
                    params.append('paths[]', path);
                });

                fetch('index.php?option=com_proclaim&task=cwmadmin.deleteOrphanedFoldersXHR&' + token + '=1', {
                    method: 'POST',
                    body: params
                })
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        alert('<?php echo Text::_('JBS_ADM_DELETED'); ?> ' + data.deleted + ' <?php echo Text::_('JBS_ADM_FOLDERS'); ?>');
                        document.getElementById('btn-scan-orphans').click();
                    })
                    .catch(error => {
                        btn.disabled = false;
                        alert('<?php echo Text::_('JBS_ADM_ERROR_DELETING'); ?>');
                    });
            });

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                var k = 1024;
                var sizes = ['Bytes', 'KB', 'MB', 'GB'];
                var i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
        </script>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <!-- Track thumbnail sizes to fire event if they are changed -->
        <input type="hidden" id="thumbnail_teacher_size_old"
               value="<?php
                echo @$this->item->params['thumbnail_teacher_size']; ?>"/>
        <input type="hidden" id="thumbnail_series_size_old"
               value="<?php
                echo @$this->item->params['thumbnail_series_size']; ?>"/>
        <input type="hidden" id="thumbnail_study_size_old"
               value="<?php
                echo @$this->item->params['thumbnail_study_size']; ?>"/>
        <input type="hidden" name="tooltype" value=""/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getCmd('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
