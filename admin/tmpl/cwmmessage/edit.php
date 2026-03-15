<?php

/**
 * Form
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmaiHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmessage\HtmlView $this */

CwmlangHelper::registerAllForJs();

$this->configFieldsets  = ['editorConfig'];
$this->hiddenFieldsets  = ['basic-limited'];
$this->ignore_fieldsets = ['jmetadata', 'item_associations'];
$this->canDo            = ContentHelper::getActions('com_proclaim', 'message');

// Create shortcut to parameters.
$params = $this->form->getFieldsets('params');

$app   = Factory::getApplication();
$input = $app->getInput();

$return  = base64_encode('index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int)$this->item->id);
$options = base64_encode('study_id=' . $this->item->id . '&createdate=' . $this->item->studydate);

// Set up defaults
if ($input->getInt('id')) {
    $booknumber   = $this->item->booknumber;
    $teacher_id   = $this->item->teacher_id;
    $location_id  = $this->item->location_id;
    $series_id    = $this->item->series_id;
    $messagetype  = $this->item->messagetype;
    $user_id      = $this->item->user_id;
    // Use the original image path (image column); fall back to thumbnailm for pre-migration records
    $imageDefault = !empty($this->item->image) ? $this->item->image : ($this->item->thumbnailm ?? '');
} else {
    $booknumber   = $this->admin_params->get('booknumber');
    $teacher_id   = $this->admin_params->get('teacher_id');
    $location_id  = $this->admin_params->get('location_id');
    $series_id    = $this->admin_params->get('series_id');
    $messagetype  = $this->admin_params->get('messagetype');
    $imageDefault = $this->admin_params->get('default_study_image', '');
    $user_id      = $this->admin->user_id;
}

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_proclaim.general');
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmmessage.cancel', 'formId' => 'message-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit');

// In the case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<?php // Prevent TinyMCE / subform / Choices.js init from scrolling the page down.
// Placed as raw <script> so it runs immediately, before any element init fires. ?>
<script>
(function () {
    if (location.hash) return;
    function hold() { window.scrollTo(0, 0); }
    window.addEventListener("scroll", hold);
    window.addEventListener("load", function () {
        hold();
        setTimeout(function () { window.removeEventListener("scroll", hold); }, 500);
    });
})();
</script>
<form action="<?php
echo Route::_(
    'index.php?option=com_proclaim&view=cwmmessage&layout=' . $layout . $tmpl . '&id=' . (int)$this->item->id
); ?>"
      method="post" name="adminForm" id="message-form" aria-label="<?php
        echo Text::_('JBS_CMN_' . ((int)$this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate"
      enctype="multipart/form-data">
    <?php
    echo LayoutHelper::render('edit.studytitle_alias', $this); ?>
    <?php
    // Media Files section
    $mediaCount = \count($this->mediafiles);
    $addMediaLink = 'index.php?option=com_proclaim&amp;task=cwmmediafile.edit&amp;sid='
        . $this->form->getValue('id') . '&amp;options=' . $options . '&amp;return='
        . $return . '&amp;' . Session::getFormToken() . '=1';

    // Server type → badge color mapping
    $serverBadgeMap = [
        'youtube' => 'bg-danger',
        'local'   => 'bg-success',
        'vimeo'   => 'bg-info',
        'legacy'  => 'bg-secondary',
    ];

    // Server types that support description sync (copy/paste to platform)
    $descSyncTypes = ['youtube', 'vimeo', 'wistia', 'facebook', 'dailymotion', 'rumble', 'soundcloud'];

    // Pre-scan mediafiles for YouTube type
    $hasYouTubeMedia = false;

    foreach ($this->mediafiles as $mf) {
        if (strtolower(trim($mf->server_type ?? '')) === 'youtube') {
            $hasYouTubeMedia = true;
            break;
        }
    }
    ?>
    <div class="card mb-3" id="media">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?>
                <?php if ($mediaCount > 0) : ?>
                    <span class="badge bg-secondary"><?php echo $mediaCount; ?></span>
                <?php endif; ?>
            </h4>
            <div class="d-flex gap-2">
                <?php if (!empty($this->item->id) && $hasYouTubeMedia) : ?>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-yt-sync">
                        <span class="icon-youtube" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_CMN_YT_SYNC_BUTTON'); ?>
                    </button>
                <?php endif; ?>
                <?php if (!empty($this->item->id)) : ?>
                    <a class="btn btn-success btn-sm" href="<?php echo $addMediaLink; ?>">
                        <span class="icon-plus" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($this->item->id)) : ?>
                <div class="p-4 text-center text-muted">
                    <span class="icon-info-circle fs-1 d-block mb-2" aria-hidden="true"></span>
                    <p class="mb-2"><?php echo Text::_('JBS_STY_SAVE_FIRST'); ?></p>
                    <button type="button" class="btn btn-primary btn-sm"
                            data-submit-task="cwmmessage.apply">
                        <span class="icon-save" aria-hidden="true"></span>
                        <?php echo Text::_('JAPPLY'); ?>
                    </button>
                </div>
            <?php elseif ($mediaCount === 0) : ?>
                <div class="p-4 text-center text-muted">
                    <span class="icon-file-add fs-1 d-block mb-2" aria-hidden="true"></span>
                    <p class="mb-2"><?php echo Text::_('JBS_STY_NO_MEDIAFILES'); ?></p>
                    <a class="btn btn-success btn-sm" href="<?php echo $addMediaLink; ?>">
                        <span class="icon-plus" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <span class="icon-menu" aria-hidden="true"
                                      title="<?php echo Text::_('JORDERINGDISABLED'); ?>"></span>
                            </th>
                            <th scope="col"><?php echo Text::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
                            <th scope="col" class="w-10 text-center"><?php echo Text::_('JBS_CMN_SERVER'); ?></th>
                            <th scope="col" class="w-5 text-center"><?php echo Text::_('JSTATUS'); ?></th>
                            <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                <?php echo Text::_('JBS_MED_DURATION'); ?>
                            </th>
                            <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                <?php echo Text::_('JBS_CMN_MEDIA_CREATE_DATE'); ?>
                            </th>
                            <th scope="col" class="w-5 text-center d-none d-md-table-cell">ID</th>
                            <th scope="col" class="w-10 text-center d-none d-md-table-cell"><?php echo Text::_('JBS_MED_DESCRIPTION'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->mediafiles as $i => $item) :
                            $editLink = 'index.php?option=com_proclaim&amp;task=cwmmediafile.edit&amp;id='
                                . (int) $item->id . '&amp;return=' . $return . '&amp;options=' . $options;
                            $mediaName = $this->escape($item->params->get('filename'))
                                ?: $this->escape($item->params->get('media_image_name'))
                                ?: Text::_('JBS_CMN_EDIT_MEDIA_FILE');
                            $serverType = strtolower(trim($item->server_type ?? ''));
                            $badgeClass = $serverBadgeMap[$serverType] ?? 'bg-primary';
                            $dH = (int) $item->params->get('media_hours', 0);
                            $dM = (int) $item->params->get('media_minutes', 0);
                            $dS = (int) $item->params->get('media_seconds', 0);
                            $duration = ($dH * 3600) + ($dM * 60) + $dS;
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center d-none d-md-table-cell">
                                    <span class="icon-ellipsis-v text-muted" aria-hidden="true"
                                          title="<?php echo (int) $item->ordering; ?>"></span>
                                </td>
                                <td>
                                    <a href="<?php echo $editLink; ?>" title="<?php echo $mediaName; ?>">
                                        <?php echo $mediaName; ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php if ($item->server_name) : ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo $this->escape($item->server_name); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="badge bg-warning text-dark"><?php echo Text::_('JNONE'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo HTMLHelper::_(
                                        'jgrid.published',
                                        $item->published,
                                        $i,
                                        'message.',
                                        true,
                                        'cb',
                                        '',
                                        ''
                                    ); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php if ($duration > 0) :
                                        $hours = intdiv($duration, 3600);
                                        $mins  = intdiv($duration % 3600, 60);
                                        $secs  = $duration % 60;
                                        echo $hours > 0
                                            ? sprintf('%d:%02d:%02d', $hours, $mins, $secs)
                                            : sprintf('%d:%02d', $mins, $secs);
                                    else :
                                        echo '&mdash;';
                                    endif; ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('date', $item->createdate, Text::_('DATE_FORMAT_LC4')); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo (int) $item->id; ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php if (\in_array($serverType, $descSyncTypes, true)) : ?>
                                        <button type="button"
                                                class="btn btn-primary btn-sm cwm-copy-desc-btn"
                                                data-media-id="<?php echo (int) $item->id; ?>"
                                                data-study-id="<?php echo (int) $this->item->id; ?>"
                                                title="<?php echo Text::_('JBS_MED_COPY_DESC_TIP'); ?>">
                                            <span class="icon-copy" aria-hidden="true"></span>
                                            <?php echo Text::_('JBS_MED_COPY_DESC'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <!-- Begin Content -->
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_STY_DETAILS')); ?>
        <div class="row">
            <?php
            if (!$this->simple->mode) { ?>
                <?php if (CwmaiHelper::isConfigured()) : ?>
                    <!-- AI Assist — generates description and study text from sermon context -->
                    <div class="row mt-2">
                        <div class="col-12 d-flex align-items-center flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-ai-assist">
                                <span class="icon-wand-magic-sparkles" aria-hidden="true"></span>
                                <?php echo Text::_('JBS_CMN_AI_ASSIST'); ?>
                            </button>
                            <div class="form-check form-check-inline ms-2 mb-0">
                                <input class="form-check-input" type="checkbox" id="ai-gen-topics" checked>
                                <label class="form-check-label" for="ai-gen-topics"><?php echo Text::_('JBS_CMN_AI_GEN_TOPICS'); ?></label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" id="ai-gen-intro" checked>
                                <label class="form-check-label" for="ai-gen-intro"><?php echo Text::_('JBS_CMN_AI_GEN_INTRO'); ?></label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" id="ai-gen-text" checked>
                                <label class="form-check-label" for="ai-gen-text"><?php echo Text::_('JBS_CMN_AI_GEN_TEXT'); ?></label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" id="ai-gen-chapters" checked>
                                <label class="form-check-label" for="ai-gen-chapters"><?php echo Text::_('JBS_CMN_AI_GEN_CHAPTERS'); ?></label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="col-lg-7">
                    <div>
                        <?php echo $this->form->renderField('studyintro'); ?>
                        <div class="scripture-stacked mb-3">
                            <?php echo $this->form->renderField('scriptures'); ?>
                            <?php echo $this->form->renderField('secondary_reference'); ?>
                        </div>
                    </div>
                </div>
                <?php
            } ?>
            <div class="col-lg-5">
                <div class="mb-3">
                    <label id="jform_hits-lbl" for="jform_hits" class="form-label">
                        <?php echo Text::_('JBS_STY_HITS'); ?>
                    </label>
                    <input type="text" id="jform_hits" value="<?php echo $this->item->hits; ?>"
                           class="form-control" size="10" readonly aria-invalid="false">
                </div>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('studydate'); ?>
                <?php echo $this->form->renderField('image', null, $imageDefault); ?>
                <?php echo $this->form->renderField('nooverlaysimplemode', 'params'); ?>
                <?php echo $this->form->renderField('teacher_id', null, $teacher_id); ?>
                <?php echo $this->form->renderField('teachers'); ?>

                <?php echo $this->form->renderField('series_id', null, $series_id); ?>

            </div>
        </div>
        <?php if (!$this->simple->mode) { ?>
        <!-- Study Text — full width below the editor/sidebar columns -->
        <div class="row mt-3">
            <div class="col-12">
                <?php echo $this->form->renderField('studytext'); ?>
            </div>
        </div>
        <?php } ?>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>
        <?php
        if (!$this->simple->mode) { ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'info', Text::_('JBS_CMN_INFO')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php echo $this->form->renderField('location_id', null, $location_id); ?>
                    <?php echo $this->form->renderField('studynumber'); ?>
                    <?php echo $this->form->renderField('comments'); ?>
                    <?php echo $this->form->renderField('access'); ?>
                    <?php echo $this->form->renderField('language'); ?>
                    <?php echo $this->form->renderField('topics'); ?>
                    <!-- Suggest Topics button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-suggest-topics">
                            <span class="icon-lightbulb" aria-hidden="true"></span>
                            <?php echo Text::_('JBS_CMN_SUGGEST_TOPICS'); ?>
                        </button>
                    </div>
                    <!-- Suggestion results panel (hidden until populated) -->
                    <div id="topic-suggestions-panel" class="card mb-3" style="display:none;">
                        <div class="card-body">
                            <div id="topic-suggestions-loading" class="text-center py-2" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <?php echo Text::_('JBS_CMN_SUGGESTING_TOPICS'); ?>
                            </div>
                            <div id="topic-suggestions-results" style="display:none;">
                                <div id="matched-topics-section" style="display:none;">
                                    <h6><?php echo Text::_('JBS_CMN_MATCHED_TOPICS'); ?></h6>
                                    <div id="matched-topics-list" class="mb-2"></div>
                                    <button type="button" class="btn btn-success btn-sm mb-2" id="btn-add-matched">
                                        <?php echo Text::_('JBS_CMN_ADD_SELECTED'); ?>
                                    </button>
                                </div>
                                <div id="suggested-keywords-section" style="display:none;">
                                    <h6><?php echo Text::_('JBS_CMN_SUGGESTED_KEYWORDS'); ?></h6>
                                    <div id="suggested-keywords-list" class="mb-2"></div>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btn-add-keywords">
                                        <?php echo Text::_('JBS_CMN_ADD_AS_NEW_TOPICS'); ?>
                                    </button>
                                </div>
                                <div id="no-suggestions" class="text-muted" style="display:none;">
                                    <?php echo Text::_('JBS_CMN_NO_SUGGESTIONS'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo $this->form->renderField('messagetype', null, $messagetype); ?>
                    <?php echo $this->form->renderField('thumbnailm'); ?>
                </div>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        } ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'publish', Text::_('JBS_STY_PUBLISH')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php
                echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
            <div class="col-6">
                <?php echo $this->form->renderField('metakey', 'params'); ?>
                <?php echo $this->form->renderField('metadesc', 'params'); ?>
                <?php
                echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <?php
                echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <!-- Hidden fields -->
        <?php
        echo $this->form->getInput('thumbnailm'); ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="return" value="<?php
        echo $input->getBase64('return'); ?>">
        <input type="hidden" name="forcedLanguage" value="<?php
        echo $input->get('forcedLanguage', '', 'cmd'); ?>">
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>

    <?php if (CwmaiHelper::isConfigured()) : ?>
    <!-- AI Assist Modal -->
    <div class="modal fade" id="aiAssistModal" tabindex="-1" aria-labelledby="aiAssistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiAssistModalLabel">
                        <span class="icon-wand-magic-sparkles" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_CMN_AI_ASSIST'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
                </div>
                <div class="modal-body">
                    <div id="ai-loading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden"><?php echo Text::_('JBS_CMN_AI_GENERATING'); ?></span>
                        </div>
                        <h6 id="ai-progress-text" class="mb-3"><?php echo Text::_('JBS_CMN_AI_GENERATING'); ?></h6>
                        <div class="progress mx-auto mb-3" style="height: 10px; max-width: 400px;">
                            <div id="ai-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div id="ai-progress-steps" class="text-start mx-auto" style="max-width: 400px;">
                        </div>
                    </div>
                    <div id="ai-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="ai-results" style="display:none;">
                        <!-- AI Topics -->
                        <div class="mb-3" id="ai-topics-section">
                            <h6><?php echo Text::_('JBS_CMN_AI_TOPICS'); ?></h6>
                            <div id="ai-topics-list"></div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="btn-ai-add-topics">
                                <?php echo Text::_('JBS_CMN_ADD_SELECTED'); ?>
                            </button>
                        </div>
                        <!-- AI Description -->
                        <div class="mb-3" id="ai-intro-section">
                            <label class="form-label fw-bold"><?php echo Text::_('JBS_CMN_AI_DESCRIPTION'); ?></label>
                            <div class="alert alert-info small mb-1">
                                <?php echo Text::_('JBS_CMN_AI_ASSIST_DESC'); ?>
                            </div>
                            <textarea id="ai-studyintro" class="form-control" rows="3"></textarea>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="btn-ai-apply-intro">
                                <?php echo Text::_('JBS_CMN_AI_APPLY'); ?>
                                &rarr; <?php echo Text::_('JBS_CMN_DESCRIPTION'); ?>
                            </button>
                        </div>
                        <!-- AI Study Text -->
                        <div class="mb-3" id="ai-text-section">
                            <label class="form-label fw-bold"><?php echo Text::_('JBS_CMN_AI_STUDY_TEXT'); ?></label>
                            <textarea id="ai-studytext" class="form-control" rows="6"></textarea>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="btn-ai-apply-text">
                                <?php echo Text::_('JBS_CMN_AI_APPLY'); ?>
                                &rarr; <?php echo Text::_('JBS_STY_STUDY_TEXT'); ?>
                            </button>
                        </div>
                        <!-- Suggested YouTube Chapters -->
                        <div id="ai-chapters-section" class="mb-3" style="display:none;">
                            <label class="form-label fw-bold"><?php echo Text::_('JBS_CMN_AI_CHAPTERS'); ?></label>
                            <div class="alert alert-info small mb-1">
                                <?php echo Text::_('JBS_CMN_AI_CHAPTERS_DESC'); ?>
                            </div>
                            <textarea id="ai-chapters-text" class="form-control font-monospace" rows="6" readonly></textarea>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btn-apply-chapters">
                                    <span class="icon-save" aria-hidden="true"></span>
                                    <?php echo Text::_('JBS_CMN_AI_APPLY_CHAPTERS'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-copy-chapters">
                                    <span class="icon-copy" aria-hidden="true"></span>
                                    <?php echo Text::_('JBS_CMN_AI_COPY_CHAPTERS'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo Text::_('JCLOSE'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($hasYouTubeMedia && !empty($this->item->id)) : ?>
    <!-- YouTube Sync Modal -->
    <div class="modal fade" id="ytSyncModal" tabindex="-1" aria-labelledby="ytSyncModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ytSyncModalLabel">
                        <span class="icon-youtube" aria-hidden="true"></span>
                        <?php echo Text::_('JBS_CMN_YT_SYNC_MODAL_TITLE'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
                </div>
                <div class="modal-body">
                    <div id="yt-sync-loading" class="text-center py-4" style="display:none;">
                        <div class="spinner-border text-danger mb-3" role="status">
                            <span class="visually-hidden"><?php echo Text::_('JBS_CMN_YT_SYNC_LOADING'); ?></span>
                        </div>
                        <p><?php echo Text::_('JBS_CMN_YT_SYNC_LOADING'); ?></p>
                    </div>
                    <div id="yt-sync-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="yt-sync-results" style="display:none;">
                        <!-- Tags → Topics -->
                        <div class="mb-4" id="yt-tags-section">
                            <h6><?php echo Text::_('JBS_CMN_YT_SYNC_TAGS_HEADING'); ?></h6>
                            <p class="text-muted small"><?php echo Text::_('JBS_CMN_YT_SYNC_TAGS_DESC'); ?></p>
                            <div id="yt-matched-section" style="display:none;">
                                <label class="form-label fw-semibold"><?php echo Text::_('JBS_CMN_YT_SYNC_MATCHED_TOPICS'); ?></label>
                                <div id="yt-matched-list" class="mb-2"></div>
                            </div>
                            <div id="yt-new-section" style="display:none;">
                                <label class="form-label fw-semibold"><?php echo Text::_('JBS_CMN_YT_SYNC_NEW_TOPICS'); ?></label>
                                <p class="text-muted small mb-1"><?php echo Text::_('JBS_CMN_YT_SYNC_NEW_TOPICS_DESC'); ?></p>
                                <div id="yt-new-list" class="mb-2"></div>
                            </div>
                            <div id="yt-no-tags" class="text-muted" style="display:none;">
                                <?php echo Text::_('JBS_CMN_YT_SYNC_NO_TAGS'); ?>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="btn-yt-add-topics" style="display:none;">
                                <?php echo Text::_('JBS_CMN_ADD_SELECTED'); ?>
                            </button>
                        </div>
                        <!-- Description -->
                        <div class="mb-4" id="yt-desc-section" style="display:none;">
                            <h6><?php echo Text::_('JBS_CMN_YT_SYNC_DESC_HEADING'); ?></h6>
                            <p class="text-muted small"><?php echo Text::_('JBS_CMN_YT_SYNC_DESC_DESC'); ?></p>
                            <textarea id="yt-description-text" class="form-control" rows="4" readonly></textarea>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn-yt-apply-desc">
                                <?php echo Text::_('JBS_CMN_AI_APPLY'); ?>
                                &rarr; <?php echo Text::_('JBS_CMN_DESCRIPTION'); ?>
                            </button>
                        </div>
                        <!-- Chapters -->
                        <div class="mb-3" id="yt-chapters-section" style="display:none;">
                            <h6><?php echo Text::_('JBS_CMN_AI_CHAPTERS'); ?></h6>
                            <p class="text-muted small"><?php echo Text::_('JBS_CMN_YT_SYNC_CHAPTERS_DESC'); ?></p>
                            <textarea id="yt-chapters-text" class="form-control font-monospace" rows="4" readonly></textarea>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btn-yt-apply-chapters">
                                    <span class="icon-save" aria-hidden="true"></span>
                                    <?php echo Text::_('JBS_CMN_AI_APPLY_CHAPTERS'); ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-yt-copy-chapters">
                                    <span class="icon-copy" aria-hidden="true"></span>
                                    <?php echo Text::_('JBS_CMN_AI_COPY_CHAPTERS'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo Text::_('JCLOSE'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</form>

<?php
// Build config data for the external topic-suggest / AI assist script
// Use the YouTube media file for chapter/sync operations; fall back to the first media file
$firstMediaId   = 0;
$youtubeMediaId = 0;

if (!empty($this->mediafiles)) {
    $firstMediaId = (int) $this->mediafiles[0]->id;

    foreach ($this->mediafiles as $mf) {
        if (strtolower(trim($mf->server_type ?? '')) === 'youtube') {
            $youtubeMediaId = (int) $mf->id;
            break;
        }
    }
}

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_proclaim.message-ai-assist');
?>
<div id="message-ai-config"
     data-token="<?php echo Session::getFormToken(); ?>"
     data-media-id="<?php echo $firstMediaId; ?>"
     data-youtube-media-id="<?php echo $youtubeMediaId; ?>"
     data-has-youtube="<?php echo $hasYouTubeMedia ? '1' : '0'; ?>"
     style="display:none;"></div>