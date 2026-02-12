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

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmessage\HtmlView $this */

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
    $booknumber  = $this->item->booknumber;
    $thumbnailm  = $this->item->thumbnailm;
    $teacher_id  = $this->item->teacher_id;
    $location_id = $this->item->location_id;
    $series_id   = $this->item->series_id;
    $messagetype = $this->item->messagetype;
    $thumbnailm  = $this->item->thumbnailm;
    $user_id     = $this->item->user_id;
} else {
    $booknumber  = $this->admin_params->get('booknumber');
    $thumbnailm  = $this->admin_params->get('default_study_image');
    $teacher_id  = $this->admin_params->get('teacher_id');
    $location_id = $this->admin_params->get('location_id');
    $series_id   = $this->admin_params->get('series_id');
    $messagetype = $this->admin_params->get('messagetype');
    $thumbnailm  = $this->admin_params->get('default_study_image');
    $user_id     = $this->admin->user_id;
}

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task) {
		if (task == "cwmmessage.cancel" || document.formvalidator.isValid(document.getElementById("message-form")))
		{
			Joomla.submitform(task, document.getElementById("message-form"));
		}
		else
		{
			alert("' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '")
		}
	}
'
    );

// In the case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
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
    ?>
    <div class="card mb-3" id="media">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?>
                <?php if ($mediaCount > 0) : ?>
                    <span class="badge bg-secondary"><?php echo $mediaCount; ?></span>
                <?php endif; ?>
            </h4>
            <?php if (!empty($this->item->id)) : ?>
                <a class="btn btn-success btn-sm" href="<?php echo $addMediaLink; ?>">
                    <span class="icon-plus" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?>
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($this->item->id)) : ?>
                <div class="p-4 text-center text-muted">
                    <span class="icon-info-circle fs-1 d-block mb-2" aria-hidden="true"></span>
                    <p class="mb-2"><?php echo Text::_('JBS_STY_SAVE_FIRST'); ?></p>
                    <a class="btn btn-primary btn-sm" href="#"
                       onclick="Joomla.submitbutton('cwmmessage.apply'); return false;">
                        <span class="icon-save" aria-hidden="true"></span>
                        <?php echo Text::_('JAPPLY'); ?>
                    </a>
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
                <div class="col-lg-7">
                    <div>
                        <?php echo $this->form->renderField('studyintro'); ?>
                        <?php echo $this->form->renderField('studytext'); ?>
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
                <?php echo $this->form->renderField('image', null, $thumbnailm); ?>
                <?php echo $this->form->renderField('nooverlaysimplemode', 'params'); ?>
                <?php echo $this->form->renderField('teacher_id', null, $teacher_id); ?>

                <?php echo $this->form->renderField('series_id', null, $series_id); ?>

            </div>
        </div>
        <!-- Scripture References — full width below the editor/sidebar columns -->
        <div class="row mt-3">
            <div class="col-12">
                <?php echo $this->form->renderFieldset('scripture'); ?>
            </div>
        </div>
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
                    <?php echo $this->form->renderField('messagetype', null, $messagetype); ?>
                    <?php echo $this->form->renderField('thumbnailm', null, $thumbnailm); ?>
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
</form>