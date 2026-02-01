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
    ->addInlineScript(
        "
	jQuery.submitbutton3 = function () {
		jQuery('[name=tooltype]').val('players')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-admin').submit()
	}

	jQuery.submitbutton4 = function () {
		jQuery('[name=tooltype]').val('popups')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-admin').submit()
	}
	jQuery.submitbutton5 = function () {
		jQuery('[name=tooltype]').val('mediaimages')
		jQuery('[name=task]').val('cwmadmin.mediaimages')
		jQuery('#item-admin').submit()
	}
	jQuery.submitbutton6 = function () {
		jQuery('[name=tooltype]').val('playerbymediatype')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-admin').submit()
	}
	jQuery.submitbutton7 = function () {
		jQuery('[name=tooltype]').val('preachitconvert')
		jQuery('[name=task]').val('cwmadmin.convertPreachIt')
		jQuery('#item-admin').submit()
	}

	Joomla.submitbutton = function (task) {
		if (task === 'cwmadmin.cancel' || task === 'cwmadmin.resetHits' || task === 'cwmadmin.resetDownloads' || task ===
			'cwmadmin.resetPlays' || task === 'cwmadmin.aliasUpdate')
		{
			Joomla.submitform(task, document.getElementById('item-admin'))
		}
		else
		{
			if (document.formvalidator.isValid(document.getElementById('item-admin')))
			{
				if (task === 'cwmadmin.save' || task === 'cwmadmin.apply')
				{
					// Confirm thumbnail changes
					var thumbnail_changes = []
					var thumbnail_teacher_size_old = jQuery('#thumbnail_teacher_size_old')
					var thumbnail_series_size_old = jQuery('#thumbnail_series_size_old')
					var thumbnail_study_size_old = jQuery('#thumbnail_study_size_old')
					if (thumbnail_teacher_size_old.val() !== jQuery('#jform_params_thumbnail_teacher_size').val() &&
						thumbnail_teacher_size_old.val() !== '')
					{
						thumbnail_changes.push('teachers')
					}
					if (thumbnail_series_size_old.val() !== jQuery('#jform_params_thumbnail_series_size').val() &&
						thumbnail_series_size_old.val() !== '')
					{
						thumbnail_changes.push('series')
					}
					if (thumbnail_study_size_old.val() !== jQuery('#jform_params_thumbnail_study_size').val() &&
						thumbnail_study_size_old.val() !== '')
					{
						thumbnail_changes.push('studies')
					}
					if (thumbnail_changes.length > 0)
					{
						var resize_thumbnails = confirm('You modified the default thumbnail size(s).' +
							'Thumbnails will be recreated for: ' + thumbnail_changes.toString() + '. Click OK to continue.')
						if (resize_thumbnails)
						{
							jQuery.getJSON(
								'index.php?option=com_proclaim&task=cwmadmin.getThumbnailListXHR&" . Session::getFormToken(
        ) . "=1',
								{ images: thumbnail_changes }, function (response) {
									jQuery('#dialog_thumbnail_resize').modal({ backdrop: 'static', keyboard: false })
									var total_paths = response.total
									var counter = 0
									var progress = 0
									if (total_paths)
									{
										jQuery.each(response.paths, function () {
											var type = this[0].type
											if (this[0].images)
											{
												jQuery.each(this[0].images, function () {
														console.log(this)
														var new_size
														switch (type)
														{
															case 'teachers':
																new_size = jQuery('#jform_params_thumbnail_teacher_size').val()
																break
															case 'studies':
																new_size = jQuery('#jform_params_thumbnail_study_size').val()
																break
															case 'series':
																new_size = jQuery('#jform_params_thumbnail_series_size').val()
																break
															default:
																new_size = 100
																break
														}
														jQuery.getJSON(
															'index.php?option=com_proclaim&task=cwmadmin.createThumbnailXHR&" . Session::getFormToken(
        ) . "=1',
															{
																image_path: this,
																new_size: new_size,
															}, function (response) {
																counter++
																progress += 100 / total_paths
																jQuery('#dialog_thumbnail_resize .bar').width(progress + '%')
																if (counter === total_paths)
																{
																	// Continue and save the rest of the form now.
																	Joomla.submitform(task, document.getElementById('item-admin'))
																}
															})
													},
												)
											}
											else
											{
												Joomla.submitform(task, document.getElementById('item-admin'))
											}
										})
									}
									else
									{
										Joomla.submitform(task, document.getElementById('item-admin'))
									}
								},
							)
						}
					}
					else
					{
						Joomla.submitform(task, document.getElementById('item-admin'))
					}
				}
			}
			else
			{
				alert('" . Text::_('JGLOBAL_VALIDATION_FORM_FAILED') . "')

			}
		}
	}"
    );

$app   = Factory::getApplication();
$input = $app->input;

$this->useCoreUI = true;
?>
<div class="modal hide fade" id="dialog_thumbnail_resize">
    <div class="modal-header">
        <h3>Creating Thumbnails...</h3>
    </div>
    <div class="modal-body">
        <div class="progress">
            <div class="bar" style="width: 0;"></div>
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
    <div class="row-fluid">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'cpanl', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'cpanl', Text::_('JBS_ADM_ADMIN_CPANL')); ?>
        <!-- Begin Tabs -->
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="well well-small">
                    <div id="dashboard-icons" class="btn-group" style="white-space: normal;">
                        <a href="<?php
                        echo Route::_(
                            'index.php?option=com_proclaim&view=cwmassets&task=cwmassets.checkassets&' .
                            Session::getFormToken() . '=1'
                        ); ?>"
                           title="<?php
                            echo Text::_('JBS_ADM_ASSET_CHECK'); ?>" class="btn"> <i
                                    class="icon-big icon-list"> </i>
                            <span><br/> <?php
                                echo Text::_('JBS_ADM_ASSET_CHECK'); ?> </span></a>
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&view=cwmbackup'); ?>"
                           title="<?php
                            echo Text::_('JBS_ADM_BACKUP_RESTORE'); ?>" class="btn"> <i
                                    class="icon-big icon-database"></i>
                            <span><br/> <?php
                                echo Text::_('JBS_ADM_BACKUP_RESTORE'); ?> </span></a>
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&view=cwmarchive'); ?>"
                           title="<?php
                            echo Text::_('JBS_ADM_ARCHIVE'); ?>" class="btn"> <i
                                    class="icon-archive icon-big"></i>
                            <span><br/> <?php
                                echo Text::_('JBS_ADM_ARCHIVE'); ?> </span></a>
                        <a href="<?php
                        echo Route::_(
                            'index.php?option=com_proclaim&view=cwmadmin&task=cwmadmin.aliasUpdate&' .
                            Session::getFormToken() . '=1'
                        ) ?>"
                           title="<?php
                            echo Text::_('JBS_ADM_RESET_ALIAS'); ?>" class="btn"> <i
                                    class="icon-big icon-tree-2"></i>
                            <span><br/> <?php
                                echo Text::_('JBS_ADM_RESET_ALIAS'); ?> </span></a>
                        <a href="<?php
                        echo Route::_('index.php?option=com_installer&view=database'); ?>"
                           title="<?php
                            echo Text::_('JBS_ADM_DATABASE'); ?>" class="btn"> <i
                                    class="icon-database icon-big"></i>
                            <span><br/> <?php
                                echo Text::_('JBS_ADM_DATABASE'); ?> </span></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'administrator', Text::_('JBS_ADM_ADMIN_PARAMS')); ?>
        <div class="row">
            <h3 class="tab-description"><?php
                echo Text::_('JBS_ADM_COMPONENT_SETTINGS'); ?></h3>
            <div class="col-12 col-lg-6">
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('simple_mode', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('simple_mode', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('simple_mode_template', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('simple_mode_template', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('simplegridtextoverlay', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('simplegridtextoverlay', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('simple_mode_display', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('simple_mode_display', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('users', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('users', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('metakey', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('metakey', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('metadesc', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('metadesc', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('compat_mode', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('compat_mode', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('drop_tables'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('drop_tables'); ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">

                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('filestokeep'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('filestokeep'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('studylistlimit', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('studylistlimit', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('uploadtype', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('uploadtype', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('show_location_media', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('show_location_media', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('popular_limit', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('popular_limit', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('character_filter', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('character_filter', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('format_popular', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('format_popular', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('debug'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('debug'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'defaults', Text::_('JBS_ADM_SYSTEM_DEFAULTS')); ?>
        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h3>

                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('location_id', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('location_id', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('teacher_id', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('teacher_id', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('series_id', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('series_id', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('booknumber', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('booknumber', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('messagetype', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('messagetype', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('main_image_icon_or_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('main_image_icon_or_image', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_study_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_study_image', 'params'); ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h3>

                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('target', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('target', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('server', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('server', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('podcast', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('podcast', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('uploadpath', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('uploadpath', 'params'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_CMN_DEFAULT_IMAGES'); ?></h3>

                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_main_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_main_image', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_series_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_series_image', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_teacher_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_teacher_image', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_show', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_show', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_use_button_icon', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_use_button_icon', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_download_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_download_image', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_button_text', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_button_text', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_button_type', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_button_type', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_button_color', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_button_color', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_icon_type', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_icon_type', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_custom_icon', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_custom_icon', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('download_icon_text_size', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('download_icon_text_size', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('default_showHide_image', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('default_showHide_image', 'params'); ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_CMN_DEFAULT_IMAGES_SIZES'); ?></h3>

                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('thumbnail_teacher_size', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('thumbnail_teacher_size', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('thumbnail_series_size', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('thumbnail_series_size', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('thumbnail_study_size', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('thumbnail_study_size', 'params'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'playersettings', Text::_('JBS_ADM_PLAYER_SETTINGS')); ?>
        <div class="row" id="playersettings">
            <div class="col-4" style="border: ridge; padding: 10px">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_CMN_MEDIA_FILES'); ?></h3>

                <div class="control-group">
                    <?php
                    echo Text::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?>
                    <div class="controls">
                        <?php
                        echo $this->playerstats; ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('from', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('from', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('to', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('to', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <button type="button" class="btn btn-primary" onclick="jQuery.submitbutton3(task)">
                        <i class="icon-user icon-white"></i> <?php
                        echo Text::_('JBS_CMN_SUBMIT'); ?>
                    </button>
                </div>
            </div>
            <div class="col-4" style="border: ridge; padding: 10px">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_ADM_POPUP_OPTIONS'); ?></h3>

                <div class="control-group">
                    <?php
                    echo Text::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?>
                    <div class="controls">
                        <?php
                        echo $this->popups; ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('pFrom', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('pFrom', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('pTo', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('pTo', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <button type="button" class="btn btn-primary" onclick="jQuery.submitbutton4(task)">
                        <i class="icon-user icon-white"></i> <?php
                        echo Text::_('JBS_CMN_SUBMIT'); ?>
                    </button>
                </div>
            </div>
            <div class="col-4" style="border: ridge; padding: 10px">
                <h3 class="tab-description"><?php
                    echo Text::_('JBS_ADM_MEDIATYPES_OPTIONS'); ?></h3>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('mtFrom', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('mtFrom', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <?php
                    echo $this->form->getLabel('mtTo', 'params'); ?>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('mtTo', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <button type="button" class="btn btn-primary" onclick="jQuery.submitbutton6(task)">
                        <i class="icon-user icon-white"></i> <?php
                        echo Text::_('JBS_CMN_SUBMIT'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'convert', Text::_('JBS_IBM_CONVERT')); ?>
        <div class="row" id="convert">
            <h4><?php
                echo Text::_('JBS_IBM_CONVERT'); ?></h4>
            <a href="<?php
            echo Route::_(
                'index.php?option=com_proclaim&view=assets&task=cwmadmin.convertSermonSpeaker&' .
                Session::getFormToken() . '=1'
            ); ?>"
               title="<?php
                echo Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER'); ?>" class="btn"> <i
                        class="icon-big icon-book"> </i>
                <span><br/> <?php
                    echo Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER'); ?> </span></a>
            <a href="<?php
            echo Route::_(
                'index.php?option=com_proclaim&view=assets&task=cwmadmin.convertPreachIt&' .
                Session::getFormToken() . '=1'
            ); ?>"
               title="<?php
                echo Text::_('JBS_ADM_PREACHIT'); ?>" class="btn"> <i
                        class="icon-big icon-list"> </i>
                <span><br/> <?php
                    echo Text::_('JBS_ADM_PREACHIT'); ?> </span></a>
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
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            <?php echo Text::_('JBS_ADM_LOADING'); ?>
                        </div>
                        <div id="migration-progress" class="mb-3" style="display:none;">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="mt-2" id="migration-status"></div>
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-start-migration" disabled>
                            <i class="icon-refresh"></i> <?php echo Text::_('JBS_ADM_START_MIGRATION'); ?>
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
                                <i class="icon-search"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>
                            </button>
                        </div>
                        <div id="orphan-results" class="mb-3" style="display:none;">
                            <div class="alert alert-info" id="orphan-summary"></div>
                            <div id="orphan-list" class="table-responsive" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>
                        <button type="button" class="btn btn-danger" id="btn-delete-orphans" style="display:none;">
                            <i class="icon-trash"></i> <?php echo Text::_('JBS_ADM_DELETE_SELECTED'); ?>
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
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> <?php echo Text::_('JBS_ADM_SCANNING'); ?>';

                fetch('index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&' + token + '=1')
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="icon-search"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>';

                        document.getElementById('orphan-results').style.display = 'block';
                        document.getElementById('orphan-summary').innerHTML =
                            '<?php echo Text::_('JBS_ADM_FOUND'); ?> <strong>' + data.totals.folders + '</strong> <?php echo Text::_('JBS_ADM_ORPHAN_FOLDERS'); ?> (' + data.totals.size_formatted + ')';

                        if (data.totals.folders > 0) {
                            var tableHtml = '<table class="table table-sm table-striped">' +
                                '<thead><tr><th><input type="checkbox" id="select-all-orphans"></th><th><?php echo Text::_('JBS_ADM_FOLDER'); ?></th><th><?php echo Text::_('JBS_ADM_FILES'); ?></th><th><?php echo Text::_('JBS_ADM_SIZE'); ?></th></tr></thead><tbody>';

                            ['studies', 'teachers', 'series'].forEach(function(type) {
                                if (data.orphans[type] && data.orphans[type].length > 0) {
                                    data.orphans[type].forEach(function(orphan) {
                                        tableHtml += '<tr>' +
                                            '<td><input type="checkbox" class="orphan-checkbox" value="' + orphan.path + '"></td>' +
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
                        btn.innerHTML = '<i class="icon-search"></i> <?php echo Text::_('JBS_ADM_SCAN_ORPHANS'); ?>';
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