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

use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
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
    ->useScript('com_proclaim.bible-translations')
    ->useScript('com_proclaim.csv-import')
    ->useStyle('com_proclaim.general');

// Auto-register all component language strings for JavaScript.
// Any JBS_* key added to the .ini file is immediately available as Joomla.Text._('KEY').
CwmlangHelper::registerAllForJs();
// Register Joomla core string(s) also needed in JS
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');

if ($this->has9xSchema) {
    $wa->useScript('com_proclaim.upgrade-wizard');
}

$app   = Factory::getApplication();
$input = $app->getInput();

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
echo Route::_('index.php?option=com_proclaim&view=cwmadmin'); ?>"
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
                        <!-- New: Scheduled Tasks Button -->
                        <a href="<?php echo Route::_('index.php?option=com_scheduler&view=tasks&filter[search]=proclaim'); ?>"
                           class="cwmadmin-action-card"
                           title="<?php echo Text::_('JBS_ADM_SCHEDULED_TASKS'); ?>">
                            <i class="icon-calendar" aria-hidden="true"></i>
                            <span><?php echo Text::_('JBS_ADM_SCHEDULED_TASKS'); ?></span>
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
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_SIMPLE_MODE'); ?></h3>
                    <?php echo $this->form->renderField('simple_mode', 'params'); ?>
                    <?php echo $this->form->renderField('simple_mode_template', 'params'); ?>
                    <?php echo $this->form->renderField('simplegridtextoverlay', 'params'); ?>
                    <?php echo $this->form->renderField('simple_mode_display', 'params'); ?>
                    <?php echo $this->form->renderField('users', 'params'); ?>
                </div>
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_SEO_METADATA'); ?></h3>
                    <?php echo $this->form->renderField('metakey', 'params'); ?>
                    <?php echo $this->form->renderField('metadesc', 'params'); ?>
                    <?php echo $this->form->renderField('enable_structured_data', 'params'); ?>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_DISPLAY_SETTINGS'); ?></h3>
                    <?php echo $this->form->renderField('studylistlimit', 'params'); ?>
                    <?php echo $this->form->renderField('show_location_media', 'params'); ?>
                    <?php echo $this->form->renderField('popular_limit', 'params'); ?>
                    <?php echo $this->form->renderField('format_popular', 'params'); ?>
                    <?php echo $this->form->renderField('character_filter', 'params'); ?>
                </div>
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_PRIVACY_MAINTENANCE'); ?></h3>
                    <?php echo $this->form->renderField('gdpr_mode', 'params'); ?>
                    <?php echo $this->form->renderField('compat_mode', 'params'); ?>
                    <?php echo $this->form->renderField('filestokeep'); ?>
                    <?php echo $this->form->renderField('drop_tables'); ?>
                    <?php echo $this->form->renderField('debug'); ?>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'defaults', Text::_('JBS_ADM_SYSTEM_DEFAULTS')); ?>
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h3>
                    <?php echo $this->form->renderField('location_id', 'params'); ?>
                    <?php echo $this->form->renderField('teacher_id', 'params'); ?>
                    <?php echo $this->form->renderField('series_id', 'params'); ?>
                    <?php echo $this->form->renderField('booknumber', 'params'); ?>
                    <?php echo $this->form->renderField('messagetype', 'params'); ?>
                </div>
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_CMN_DEFAULT_IMAGES'); ?></h3>
                    <?php echo $this->form->renderField('main_image_icon_or_image', 'params'); ?>
                    <?php echo $this->form->renderField('default_main_image', 'params'); ?>
                    <?php echo $this->form->renderField('default_study_image', 'params'); ?>
                    <?php echo $this->form->renderField('default_series_image', 'params'); ?>
                    <?php echo $this->form->renderField('default_teacher_image', 'params'); ?>
                    <?php echo $this->form->renderField('default_showHide_image', 'params'); ?>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h3>
                    <?php echo $this->form->renderField('download', 'params'); ?>
                    <?php echo $this->form->renderField('target', 'params'); ?>
                    <?php echo $this->form->renderField('server', 'params'); ?>
                    <?php echo $this->form->renderField('podcast', 'params'); ?>
                    <?php echo $this->form->renderField('uploadpath', 'params'); ?>
                </div>
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_CMN_DEFAULT_IMAGES_SIZES'); ?></h3>
                    <?php echo $this->form->renderField('thumbnail_teacher_size', 'params'); ?>
                    <?php echo $this->form->renderField('thumbnail_series_size', 'params'); ?>
                    <?php echo $this->form->renderField('thumbnail_study_size', 'params'); ?>
                </div>
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_DOWNLOAD_BUTTON_DEFAULTS'); ?></h3>
                    <?php echo $this->form->renderField('download_show', 'params'); ?>
                    <?php echo $this->form->renderField('download_use_button_icon', 'params'); ?>
                    <?php echo $this->form->renderField('default_download_image', 'params'); ?>
                    <?php echo $this->form->renderField('download_button_text', 'params'); ?>
                    <?php echo $this->form->renderField('download_button_type', 'params'); ?>
                    <?php echo $this->form->renderField('download_button_color', 'params'); ?>
                    <?php echo $this->form->renderField('download_icon_type', 'params'); ?>
                    <?php echo $this->form->renderField('download_custom_icon', 'params'); ?>
                    <?php echo $this->form->renderField('download_icon_text_size', 'params'); ?>
                </div>
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

                    <div class="mb-3">
                        <label><?php echo Text::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?></label>
                        <div id="player-stats-container" class="cwmadmin-stats-container">
                            <div class="cwmadmin-stats-loading">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span><?php echo Text::_('JBS_ADM_LOADING'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('from', 'params'); ?>
                    <?php echo $this->form->renderField('to', 'params'); ?>
                    <div class="mb-3">
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

                    <div class="mb-3">
                        <label><?php echo Text::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?></label>
                        <div id="popup-stats-container" class="cwmadmin-stats-container">
                            <div class="cwmadmin-stats-loading">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span><?php echo Text::_('JBS_ADM_LOADING'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('pFrom', 'params'); ?>
                    <?php echo $this->form->renderField('pTo', 'params'); ?>
                    <div class="mb-3">
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
                    <div class="mb-3">
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

        <?php if ($this->has9xSchema) : ?>
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'upgrade', Text::_('JBS_UPG_TAB_TITLE')); ?>
        <?php echo $this->loadTemplate('upgrade'); ?>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'imagetools', Text::_('JBS_ADM_IMAGE_TOOLS')); ?>
        <div id="imagetools-nav-warning" class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="display:none !important;">
            <i class="icon-warning-2 fs-3 me-3" aria-hidden="true"></i>
            <div>
                <strong><?php echo Text::_('JBS_ADM_OPERATION_IN_PROGRESS'); ?></strong>
            </div>
        </div>

        <!-- Post-upgrade notice: shown when arriving from the upgrade wizard -->
        <div id="imagetools-post-upgrade-notice" class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="display:none;">
            <i class="icon-warning-2 me-2" aria-hidden="true"></i>
            <strong><?php echo Text::_('JBS_ADM_POST_UPGRADE_NOTICE_TITLE'); ?></strong>
            <?php echo Text::_('JBS_ADM_POST_UPGRADE_NOTICE_DESC'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
        </div>

        <!-- Image Migration Pipeline -->
        <div id="imagetools-pipeline-panel" class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_ADM_PIPELINE_TITLE'); ?></h3>
            <p class="text-body-secondary"><?php echo Text::_('JBS_ADM_PIPELINE_DESC'); ?></p>
            <ol id="pipeline-steps" class="list-group list-group-numbered list-group-flush mb-3">
                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-pipeline-step="migrate">
                    <span><?php echo Text::_('JBS_ADM_PIPELINE_STEP_MIGRATE'); ?></span>
                    <span data-pipeline-badge="migrate" class="badge bg-secondary ms-auto" style="display:none;"></span>
                </li>
                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-pipeline-step="recover">
                    <span><?php echo Text::_('JBS_ADM_PIPELINE_STEP_RECOVER'); ?></span>
                    <span data-pipeline-badge="recover" class="badge bg-secondary ms-auto" style="display:none;"></span>
                </li>
                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-pipeline-step="webp">
                    <span><?php echo Text::_('JBS_ADM_PIPELINE_STEP_WEBP'); ?></span>
                    <span data-pipeline-badge="webp" class="badge bg-secondary ms-auto" style="display:none;"></span>
                </li>
            </ol>
            <div id="pipeline-progress-wrap" style="display:none;" class="mb-3">
                <div class="progress" role="progressbar" aria-label="<?php echo Text::_('JBS_ADM_PIPELINE_PROGRESS'); ?>" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div id="pipeline-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;"></div>
                </div>
            </div>
            <p id="pipeline-status-text" class="mb-3 text-body-secondary" aria-live="polite"></p>
            <div class="d-flex gap-2">
                <button type="button" id="btn-run-pipeline" class="btn btn-primary">
                    <i class="icon-play" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_PIPELINE_RUN'); ?>
                </button>
                <button type="button" id="btn-cancel-pipeline" class="btn btn-outline-danger" style="display:none;">
                    <?php echo Text::_('JCANCEL'); ?>
                </button>
            </div>
        </div>

        <!-- Regenerate Thumbnails — always visible, standalone tool -->
        <div class="row mt-3" id="imagetools-row1b">
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_THUMB_REGENERATION'); ?></h3>
                    <p><?php echo Text::_('JBS_ADM_THUMB_REGENERATION_DESC'); ?></p>
                    <div id="thumb-regen-counts" class="mb-3">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_ADM_LOADING'); ?>
                    </div>
                    <div id="thumb-regen-progress" class="mb-3" style="display:none;">
                        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-info" style="width: 0%"></div>
                        </div>
                        <div class="mt-2" id="thumb-regen-status"></div>
                    </div>
                    <button type="button" class="btn btn-info" id="btn-start-thumb-regen" disabled>
                        <i class="icon-refresh" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_REGENERATE_THUMBS'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Tools (collapsible) -->
        <div class="accordion accordion-flush mt-2" id="imagetools-accordion">

            <!-- Migration Tools (on-demand) -->
            <div class="accordion-item border-0 bg-transparent">
                <h4 class="accordion-header" id="accordion-migration-heading">
                    <button class="accordion-button collapsed bg-body-secondary rounded px-3 py-2 fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#accordion-migration"
                            aria-expanded="false"
                            aria-controls="accordion-migration">
                        <?php echo Text::_('JBS_ADM_IMAGETOOLS_MIGRATION_HEADING'); ?>
                    </button>
                </h4>
                <div id="accordion-migration"
                     class="accordion-collapse collapse"
                     aria-labelledby="accordion-migration-heading">
                    <div class="accordion-body px-0 pt-3 pb-0">
                        <div class="row" id="imagetools">
                            <!-- WebP Generation Section -->
                            <div class="col-12 col-lg-6">
                                <div class="cwmadmin-panel mb-4">
                                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_WEBP_GENERATION'); ?></h3>
                                    <p><?php echo Text::_('JBS_ADM_WEBP_GENERATION_DESC'); ?></p>
                                    <div id="webp-counts" class="mb-3">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <?php echo Text::_('JBS_ADM_LOADING'); ?>
                                    </div>
                                    <div id="webp-progress" class="mb-3" style="display:none;">
                                        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar bg-success" style="width: 0%"></div>
                                        </div>
                                        <div class="mt-2" id="webp-status"></div>
                                    </div>
                                    <button type="button" class="btn btn-success" id="btn-start-webp" disabled>
                                        <i class="icon-images" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_GENERATE_WEBP'); ?>
                                    </button>
                                </div>
                            </div>
                            <!-- Recover Bare-ID Folders -->
                            <div class="col-12 col-lg-6">
                                <div class="cwmadmin-panel mb-4">
                                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_RECOVER_IMAGES'); ?></h3>
                                    <p><?php echo Text::_('JBS_ADM_RECOVER_IMAGES_DESC'); ?></p>
                                    <div id="recovery-counts" class="mb-3">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <?php echo Text::_('JBS_ADM_LOADING'); ?>
                                    </div>
                                    <div id="recovery-progress" class="mb-3" style="display:none;">
                                        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar bg-warning" style="width: 0%"></div>
                                        </div>
                                        <div class="mt-2" id="recovery-status"></div>
                                    </div>
                                    <button type="button" class="btn btn-warning" id="btn-start-recovery" disabled>
                                        <i class="icon-refresh" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_RECOVER_IMAGES_BTN'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cleanup & Maintenance -->
            <div class="accordion-item border-0 bg-transparent mt-1">
                <h4 class="accordion-header" id="accordion-cleanup-heading">
                    <button class="accordion-button collapsed bg-body-secondary rounded px-3 py-2 fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#accordion-cleanup"
                            aria-expanded="false"
                            aria-controls="accordion-cleanup">
                        <?php echo Text::_('JBS_ADM_IMAGETOOLS_CLEANUP_HEADING'); ?>
                    </button>
                </h4>
                <div id="accordion-cleanup"
                     class="accordion-collapse collapse"
                     aria-labelledby="accordion-cleanup-heading">
                    <div class="accordion-body px-0 pt-3 pb-0">

                        <!-- Cleanup & Maintenance Pipeline -->
                        <div id="cleanup-pipeline-panel" class="cwmadmin-panel mb-4">
                            <h3 class="tab-description"><?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_TITLE'); ?></h3>
                            <p class="text-body-secondary"><?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_DESC'); ?></p>
                            <ol id="cleanup-pipeline-steps" class="list-group list-group-numbered list-group-flush mb-3">
                                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-cleanup-step="unresolvable">
                                    <span><?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_STEP_UNRESOLVABLE'); ?></span>
                                    <span data-cleanup-badge="unresolvable" class="badge bg-secondary ms-auto" style="display:none;"></span>
                                </li>
                                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-cleanup-step="legacy">
                                    <span><?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_STEP_LEGACY'); ?></span>
                                    <span data-cleanup-badge="legacy" class="badge bg-secondary ms-auto" style="display:none;"></span>
                                </li>
                                <li class="list-group-item d-flex align-items-center gap-2 bg-transparent px-0" data-cleanup-step="orphans">
                                    <span><?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_STEP_ORPHANS'); ?></span>
                                    <span data-cleanup-badge="orphans" class="badge bg-secondary ms-auto" style="display:none;"></span>
                                </li>
                            </ol>
                            <div id="cleanup-pipeline-progress-wrap" style="display:none;" class="mb-3">
                                <div class="progress" role="progressbar" aria-label="<?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_PROGRESS'); ?>" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div id="cleanup-pipeline-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%;"></div>
                                </div>
                            </div>
                            <div id="cleanup-pipeline-confirm" class="alert alert-warning mb-3" style="display:none;">
                                <p id="cleanup-pipeline-confirm-text" class="mb-2"></p>
                                <div class="d-flex gap-2">
                                    <button type="button" id="btn-cleanup-confirm-delete" class="btn btn-danger btn-sm">
                                        <i class="icon-trash" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_DELETE_CONTINUE'); ?>
                                    </button>
                                    <button type="button" id="btn-cleanup-confirm-skip" class="btn btn-outline-secondary btn-sm">
                                        <?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_SKIP'); ?>
                                    </button>
                                </div>
                            </div>
                            <p id="cleanup-pipeline-status-text" class="mb-3 text-body-secondary" aria-live="polite"></p>
                            <div class="d-flex gap-2">
                                <button type="button" id="btn-run-cleanup-pipeline" class="btn btn-warning">
                                    <i class="icon-play" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_CLEANUP_PIPELINE_RUN'); ?>
                                </button>
                                <button type="button" id="btn-cancel-cleanup-pipeline" class="btn btn-outline-danger" style="display:none;">
                                    <?php echo Text::_('JCANCEL'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Individual Tools -->
                        <div class="row" id="imagetools-row2">
                            <div class="col-12 col-lg-6">
                                <div class="cwmadmin-panel mb-4">
                                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_CLEAR_UNRESOLVABLE'); ?></h3>
                                    <p><?php echo Text::_('JBS_ADM_CLEAR_UNRESOLVABLE_DESC'); ?></p>
                                    <div id="unresolvable-preview" class="mb-3" style="display:none;"></div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary" id="btn-preview-unresolvable">
                                            <i class="icon-search" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_PREVIEW_UNRESOLVABLE'); ?>
                                        </button>
                                        <button type="button" class="btn btn-warning" id="btn-clear-unresolvable" style="display:none;">
                                            <i class="icon-trash" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_CLEAR_UNRESOLVABLE_BTN'); ?>
                                        </button>
                                        <a href="index.php?option=com_proclaim&task=cwmadmin.downloadClearedLogXHR&<?php echo Session::getFormToken(); ?>=1"
                                           class="btn btn-outline-secondary" id="btn-download-cleared-log" style="display:none;" download>
                                            <i class="icon-download" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_DOWNLOAD_CLEARED_LOG'); ?>
                                        </a>
                                    </div>
                                    <div id="unresolvable-status" class="mt-3" style="display:none;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="cwmadmin-panel mb-4">
                                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_LEGACY_FILES'); ?></h3>
                                    <p><?php echo Text::_('JBS_ADM_LEGACY_FILES_DESC'); ?></p>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-scan-legacy">
                                        <i class="icon-search" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_SCAN_LEGACY'); ?>
                                    </button>
                                    <div id="legacy-results" class="mt-3" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Orphan Cleanup -->
                        <div class="row mt-3" id="imagetools-row3">
                            <div class="col-12 col-lg-6">
                                <div class="cwmadmin-panel mb-4">
                                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_ORPHAN_CLEANUP'); ?></h3>
                                    <p><?php echo Text::_('JBS_ADM_ORPHAN_CLEANUP_DESC'); ?></p>
                                    <p class="text-muted small" id="orphan-step-indicator"><?php echo Text::_('JBS_ADM_ORPHAN_STEP1'); ?></p>
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
                </div>
            </div>

        </div>

        <!-- Hidden migration driver — used by the pipeline; no visible UI for standalone use -->
        <div id="imagetools-migration-driver" style="display:none;" aria-hidden="true">
            <div id="migration-counts"></div>
            <div id="migration-progress">
                <div class="progress"><div class="progress-bar" style="width: 0%"></div></div>
                <div id="migration-status"></div>
            </div>
            <div id="migration-error-report"></div>
            <button type="button" id="btn-start-migration" disabled></button>
        </div>

        <?php
        // Pass PHP data to external JS via data attributes
        $wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_proclaim.admin-imagetools');

// Language strings for imagetools JS are auto-registered via CwmlangHelper::registerAllForJs()
// above and accessed directly via Joomla.Text._('JBS_ADM_KEY') in admin-imagetools.es6.js.
?>
        <div id="imagetools-config"
             data-token="<?php echo Session::getFormToken(); ?>"
             style="display:none;"></div>
        <?php
echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
echo HTMLHelper::_('uitab.addTab', 'myTab', 'scripture', Text::_('JBS_ADM_SCRIPTURE_TAB')); ?>
        <div class="row" id="scripture-settings">
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_SCRIPTURE_PROVIDERS'); ?></h3>
                    <p class="text-muted"><?php echo Text::_('JBS_ADM_SCRIPTURE_PROVIDERS_DESC'); ?></p>

                    <?php echo $this->form->renderField('provider_getbible', 'params'); ?>
                    <?php echo $this->form->renderField('provider_api_bible', 'params'); ?>
                    <?php echo $this->form->renderField('api_bible_api_key', 'params'); ?>
                    <div id="api-bible-key-row" class="mb-3" style="display:none;">
                        <a href="https://api.bible/sign-in" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="icon-key" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_ADM_API_BIBLE_GET_KEY'); ?>
                        </a>
                    </div>
                    <div id="api-bible-sync-row" class="mb-3" style="display:none;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-sync-api-bible">
                            <i class="icon-refresh" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_ADM_SYNC_TRANSLATIONS'); ?>
                        </button>
                        <span id="api-bible-sync-status" class="ms-2 small"></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_SCRIPTURE_SETTINGS'); ?></h3>
                    <?php echo $this->form->renderField('default_bible_version', 'params'); ?>
                    <?php echo $this->form->renderField('scripture_cache_days', 'params'); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="cwmadmin-panel mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="tab-description mb-0" id="translations-card-header"><?php echo Text::_('JBS_ADM_LOCAL_TRANSLATIONS'); ?></h3>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-danger d-none" id="btn-remove-all-translations"
                                    title="<?php echo Text::_('JBS_ADM_REMOVE_ALL'); ?>">
                                <i class="icon-trash" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_REMOVE_ALL'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-refresh-translations"
                                    title="<?php echo Text::_('JBS_ADM_REFRESH'); ?>">
                                <i class="icon-refresh" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-muted"><?php echo Text::_('JBS_ADM_LOCAL_TRANSLATIONS_DESC'); ?></p>
                    <div id="translations-list">
                        <div class="text-center py-3">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            <?php echo Text::_('JBS_ADM_LOADING'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="bible-translations-config" class="d-none"
             data-gdpr-mode="<?php echo (int) ($this->item->params['gdpr_mode'] ?? 0); ?>"
             data-token="<?php echo Session::getFormToken(); ?>"
             data-str-loading="<?php echo Text::_('JBS_ADM_LOADING'); ?>"
             data-str-no-translations="<?php echo Text::_('JBS_ADM_NO_TRANSLATIONS'); ?>"
             data-str-load-error="<?php echo Text::_('JBS_ADM_PROVIDER_STATUS_UNKNOWN'); ?>"
             data-str-title="<?php echo Text::_('JGLOBAL_TITLE'); ?>"
             data-str-abbreviation="<?php echo Text::_('JBS_ADM_ABBREVIATION'); ?>"
             data-str-source="<?php echo Text::_('JBS_ADM_SOURCE'); ?>"
             data-str-status="<?php echo Text::_('JSTATUS'); ?>"
             data-str-verses="<?php echo Text::_('JBS_ADM_VERSES'); ?>"
             data-str-installed="<?php echo Text::_('JBS_ADM_INSTALLED'); ?>"
             data-str-not-installed="<?php echo Text::_('JBS_ADM_NOT_INSTALLED'); ?>"
             data-str-download="<?php echo Text::_('JBS_ADM_DOWNLOAD'); ?>"
             data-str-downloading="<?php echo Text::_('JBS_ADM_DOWNLOADING'); ?>"
             data-str-remove="<?php echo Text::_('JBS_ADM_REMOVE'); ?>"
             data-str-download-failed="<?php echo Text::_('JBS_ADM_BIBLE_DOWNLOAD_FAILED_GENERIC'); ?>"
             data-str-confirm-remove="<?php echo Text::_('JBS_ADM_CONFIRM_REMOVE_TRANSLATION'); ?>"
             data-str-bundled-done="<?php echo Text::_('JBS_ADM_BUNDLED_AUTO_DOWNLOADED'); ?>"
             data-str-status-ready="<?php echo Text::_('JBS_ADM_PROVIDER_STATUS_READY'); ?>"
             data-str-status-installed="<?php echo Text::_('JBS_ADM_PROVIDER_STATUS_INSTALLED'); ?>"
             data-str-status-none="<?php echo Text::_('JBS_ADM_PROVIDER_STATUS_NONE'); ?>"
             data-str-status-unknown="<?php echo Text::_('JBS_ADM_PROVIDER_STATUS_UNKNOWN'); ?>"
             data-str-remove-all="<?php echo Text::_('JBS_ADM_REMOVE_ALL'); ?>"
             data-str-confirm-remove-all="<?php echo Text::_('JBS_ADM_CONFIRM_REMOVE_ALL'); ?>"
             data-str-size="<?php echo Text::_('JBS_ADM_SIZE'); ?>"
             data-str-total-size="<?php echo Text::_('JBS_ADM_TOTAL_SIZE'); ?>"
             data-str-syncing="<?php echo Text::_('JBS_ADM_SYNCING'); ?>"
             data-str-sync-complete="<?php echo Text::_('JBS_ADM_SYNC_COMPLETE'); ?>"
             data-str-sync-failed="<?php echo Text::_('JBS_ADM_SYNC_FAILED'); ?>"
             data-str-gdpr-disabled="<?php echo Text::_('JBS_ADM_GDPR_PROVIDERS_DISABLED'); ?>"
             data-str-online="<?php echo Text::_('JBS_ADM_ONLINE'); ?>"
             data-str-language="<?php echo Text::_('JBS_ADM_LANGUAGE'); ?>"
             data-str-all-languages="<?php echo Text::_('JBS_ADM_ALL_LANGUAGES'); ?>"
             data-str-filter-all="<?php echo Text::_('JBS_ADM_FILTER_STATUS_ALL'); ?>"
             data-str-filter-installed="<?php echo Text::_('JBS_ADM_FILTER_STATUS_INSTALLED'); ?>"
             data-str-filter-not-installed="<?php echo Text::_('JBS_ADM_FILTER_STATUS_NOT_INSTALLED'); ?>"
             data-str-filter-in-use="<?php echo Text::_('JBS_ADM_FILTER_STATUS_IN_USE'); ?>"
             data-str-search-placeholder="<?php echo Text::_('JBS_ADM_SEARCH_TRANSLATIONS'); ?>"
             data-str-usage-count="<?php echo Text::_('JBS_ADM_USAGE_COUNT'); ?>"
             data-str-usage-badge="<?php echo Text::_('JBS_ADM_USAGE_BADGE'); ?>"
             data-str-suggested="<?php echo Text::_('JBS_ADM_SUGGESTED'); ?>"
             data-str-showing-count="<?php echo Text::_('JBS_ADM_SHOWING_COUNT'); ?>"
             data-admin-language="<?php echo Factory::getApplication()->getLanguage()->getTag(); ?>"
             data-str-core-translation="<?php echo Text::_('JBS_ADM_CORE_TRANSLATION'); ?>"
             data-str-core-cannot-remove="<?php echo Text::_('JBS_ADM_CORE_CANNOT_REMOVE'); ?>"
             data-str-suggested-desc="<?php echo Text::_('JBS_ADM_SUGGESTED_DESC'); ?>"
             data-str-online-only="<?php echo Text::_('JBS_ADM_ONLINE_ONLY'); ?>"
             data-str-online-only-desc="<?php echo Text::_('JBS_ADM_ONLINE_ONLY_DESC'); ?>"
             data-str-provider-disable-confirm="<?php echo Text::_('JBS_ADM_PROVIDER_DISABLE_CONFIRM'); ?>"
             data-str-provider-cleanup-done="<?php echo Text::_('JBS_ADM_PROVIDER_CLEANUP_DONE'); ?>"
        ></div>
        <?php
echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
echo HTMLHelper::_('uitab.addTab', 'myTab', 'csvimport', Text::_('JBS_CSV_TAB_TITLE')); ?>
        <?php echo $this->loadTemplate('csvimport'); ?>
        <?php
echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
echo HTMLHelper::_('uitab.addTab', 'myTab', 'analytics', Text::_('JBS_ANA_ANALYTICS')); ?>
        <div class="row" id="analytics-tab">
            <div class="col-12">
                <div class="cwmadmin-panel mb-4">
                    <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmanalytics'); ?>"
                       class="btn btn-primary btn-lg">
                        <i class="icon-chart-bar me-2" aria-hidden="true"></i>
                        <?php echo Text::_('JBS_ANA_FULL_DASHBOARD'); ?>
                    </a>
                </div>

                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ANA_TRACKING_SETTINGS'); ?></h3>
                    <?php echo $this->form->renderField('analytics_enabled', 'params'); ?>
                    <?php echo $this->form->renderField('analytics_gdpr_optout', 'params'); ?>
                    <?php echo $this->form->renderField('analytics_referrer_mode', 'params'); ?>
                    <?php echo $this->form->renderField('analytics_retention_days', 'params'); ?>
                </div>

                <div class="cwmadmin-panel mb-4">
                    <h3 class="tab-description"><?php echo Text::_('JBS_ADM_RESET_STATS_TITLE'); ?></h3>
                    <p class="text-body-secondary"><?php echo Text::_('JBS_ADM_RESET_STATS_DESC'); ?></p>
                    <!-- Two-step inline confirm — shown after first button click -->
                    <div id="reset-stats-confirm" class="alert alert-warning mb-3" style="display:none;">
                        <p id="reset-stats-confirm-text" class="fw-semibold mb-2"></p>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-reset-stats-confirm" class="btn btn-danger btn-sm">
                                <i class="icon-trash" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_RESET_STATS_CONFIRM_BTN'); ?>
                            </button>
                            <button type="button" id="btn-reset-stats-cancel" class="btn btn-outline-secondary btn-sm">
                                <?php echo Text::_('JCANCEL'); ?>
                            </button>
                        </div>
                    </div>
                    <div id="reset-stats-status" class="mb-3" style="display:none;"></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-warning" data-reset-stat="hits">
                            <i class="icon-remove" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_RESET_ALL_HITS'); ?>
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-reset-stat="downloads">
                            <i class="icon-remove" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_RESET_ALL_DOWNLOAD_HITS'); ?>
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-reset-stat="plays">
                            <i class="icon-remove" aria-hidden="true"></i> <?php echo Text::_('JBS_ADM_RESET_ALL_PLAYS'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
