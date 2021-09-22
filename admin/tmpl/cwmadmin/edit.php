<?php
/**
 * Admin Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as JHtml;

// Load the tooltip behavior.
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$app   = Factory::getApplication();
$input = $app->input;

$this->useCoreUI = true;
?>
<script type="text/javascript">
	jQuery.submitbutton3 = function () {
		jQuery('[name=tooltype]').val('players')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-administrator').submit()
	}

	jQuery.submitbutton4 = function () {
		jQuery('[name=tooltype]').val('popups')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-administrator').submit()
	}
	jQuery.submitbutton5 = function () {
		jQuery('[name=tooltype]').val('mediaimages')
		jQuery('[name=task]').val('cwmadmin.mediaimages')
		jQuery('#item-administrator').submit()
	}
	jQuery.submitbutton6 = function () {
		jQuery('[name=tooltype]').val('playerbymediatype')
		jQuery('[name=task]').val('cwmadmin.tools')
		jQuery('#item-administrator').submit()
	}
	jQuery.submitbutton7 = function () {
		jQuery('[name=tooltype]').val('preachitconvert')
		jQuery('[name=task]').val('cwmadmin.convertPreachIt')
		jQuery('#item-administrator').submit()
	}

	Joomla.submitbutton = function (task) {
		if (task === 'cwmadmin.cancel' || task === 'cwmadmin.resetHits' || task === 'cwmadmin.resetDownloads' || task ===
			'cwmadmin.resetPlays' || task === 'cwmadmin.aliasUpdate')
		{
			Joomla.submitform(task, document.getElementById('item-administrator'))
		}
		else
		{
			if (document.formvalidator.isValid(document.id('item-administrator')))
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
								'index.php?option=com_proclaim&task=cwmadmin.getThumbnailListXHR&<?php echo JSession::getFormToken(); ?>=1',
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
															'index.php?option=com_proclaim&task=cwmadmin.createThumbnailXHR&<?php echo JSession::getFormToken(); ?>=1',
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
																	Joomla.submitform(task, document.getElementById('item-administrator'))
																}
															})
													},
												)
											}
											else
											{
												Joomla.submitform(task, document.getElementById('item-administrator'))
											}
										})
									}
									else
									{
										Joomla.submitform(task, document.getElementById('item-administrator'))
									}
								},
							)
						}
					}
					else
					{
						Joomla.submitform(task, document.getElementById('item-administrator'))
					}
				}
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('
				JGLOBAL_VALIDATION_FORM_FAILED
				'));?>',
				)

			}
		}
	}
</script>
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

<form
		action="<?php echo JRoute::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="item-admin"
		aria-label="<?php echo Text::_('COM_CONTENT_FORM_TITLE_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
		class="form-validate">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="row-fluid">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'cpanl', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'cpanl', Text::_('JBS_ADM_ADMIN_CPANL')); ?>
		<!-- Begin Tabs -->
		<div class="row">
			<div class="col-12 col-lg-12">
				<div class="well well-small">
					<div id="dashboard-icons" class="btn-group" style="white-space: normal;">
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=assets&task=assets.checkassets&' .
							JSession::getFormToken() . '=1'); ?>"
						   title="<?php echo JText::_('JBS_ADM_ASSET_CHECK'); ?>" class="btn"> <i
									class="icon-big icon-list"> </i>
							<span><br/> <?php echo JText::_('JBS_ADM_ASSET_CHECK'); ?> </span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=migrate'); ?>"
						   title="<?php echo JText::_('JBS_ADM_MIGRATE'); ?>" class="btn"> <i
									class="icon-big icon-share-alt"></i>
							<span><br/> <?php echo JText::_('JBS_ADM_MIGRATE'); ?> </span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=backup'); ?>"
						   title="<?php echo JText::_('JBS_ADM_BACKUP_RESTORE'); ?>" class="btn"> <i
									class="icon-big icon-database"></i>
							<span><br/> <?php echo JText::_('JBS_ADM_BACKUP_RESTORE'); ?> </span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=archive'); ?>"
						   title="<?php echo JText::_('JBS_ADM_ARCHIVE'); ?>" class="btn"> <i
									class="icon-archive icon-big"></i>
							<span><br/> <?php echo JText::_('JBS_ADM_ARCHIVE'); ?> </span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=assets&task=administration.aliasUpdate&' .
							JSession::getFormToken() . '=1') ?>"
						   title="<?php echo JText::_('JBS_ADM_RESET_ALIAS'); ?>" class="btn"> <i
									class="icon-big icon-tree-2"></i>
							<span><br/> <?php echo JText::_('JBS_ADM_RESET_ALIAS'); ?> </span></a>
						<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=database'); ?>"
						   title="<?php echo JText::_('JBS_ADM_DATABASE'); ?>" class="btn"> <i
									class="icon-database icon-big"></i>
							<span><br/> <?php echo JText::_('JBS_ADM_DATABASE'); ?> </span></a>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'administrator', Text::_('JBS_ADM_ADMIN_PARAMS')); ?>
		<div class="row">
			<h3 class="tab-description"><?php echo JText::_('JBS_ADM_COMPONENT_SETTINGS'); ?></h3>
			<div class="control-group">
				<div class="control-group">
					<?php echo $this->form->getLabel('simple_mode', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('simple_mode', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-group">
						<?php echo $this->form->getLabel('simple_mode_display', 'params'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('simple_mode_display', 'params'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('users', 'params'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('users', 'params'); ?>
						</div>
					</div>
					<?php echo $this->form->getLabel('metakey', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('metakey', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('metadesc', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('metadesc', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('compat_mode', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('compat_mode', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('drop_tables'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('drop_tables'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('filestokeep'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('filestokeep'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('studylistlimit', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('studylistlimit', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('uploadtype', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('uploadtype', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('show_location_media', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('show_location_media', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('popular_limit', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('popular_limit', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('character_filter', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('character_filter', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('format_popular', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('format_popular', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('debug'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('debug'); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'defaults', Text::_('JBS_ADM_SYSTEM_DEFAULTS')); ?>
		<div class="row">
			<div class="col-12 col-lg-6">
				<h3 class="tab-description"><?php echo JText::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h3>

				<div class="control-group">
					<?php echo $this->form->getLabel('location_id', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('location_id', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('teacher_id', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('teacher_id', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('series_id', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('series_id', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('booknumber', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('booknumber', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('messagetype', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('messagetype', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('default_study_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_study_image', 'params'); ?>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-6">
				<h3 class="tab-description"><?php echo JText::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h3>

				<div class="control-group">
					<?php echo $this->form->getLabel('download', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('target', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('target', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('server', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('server', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('podcast', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('podcast', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('uploadpath', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('uploadpath', 'params'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-12 col-lg-6">
				<h3 class="tab-description"><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></h3>

				<div class="control-group">
					<?php echo $this->form->getLabel('default_main_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_main_image', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('default_series_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_series_image', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('default_teacher_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_teacher_image', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_show', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_show', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_use_button_icon', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_use_button_icon', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('default_download_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_download_image', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_button_text', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_button_text', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_button_type', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_button_type', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_button_color', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_button_color', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_icon_type', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_icon_type', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_custom_icon', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_custom_icon', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('download_icon_text_size', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('download_icon_text_size', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('default_showHide_image', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('default_showHide_image', 'params'); ?>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-6">
				<h3 class="tab-description"><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES_SIZES'); ?></h3>

				<div class="control-group">
					<?php echo $this->form->getLabel('thumbnail_teacher_size', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('thumbnail_teacher_size', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('thumbnail_series_size', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('thumbnail_series_size', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('thumbnail_study_size', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('thumbnail_study_size', 'params'); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'playersettings', Text::_('JBS_ADM_PLAYER_SETTINGS')); ?>
		<div class="row" id="playersettings">
			<div class="span4" style="border: ridge; padding: 3px">
				<h3 class="tab-description"><?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?></h3>

				<div class="control-group">
					<?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?>
					<div class="controls">
						<?php echo $this->playerstats; ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('from', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('from', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('to', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('to', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<button type="button" class="btn btn-primary" onclick="jQuery.submitbutton3(task)">
						<i class="icon-user icon-white"></i> <?php echo JText::_('JBS_CMN_SUBMIT'); ?>
					</button>
				</div>
			</div>
			<div class="span4" style="border: ridge; padding: 3px">
				<h3 class="tab-description"><?php echo JText::_('JBS_ADM_POPUP_OPTIONS'); ?></h3>

				<div class="control-group">
					<?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?>
					<div class="controls">
						<?php echo $this->popups; ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('pFrom', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('pFrom', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('pTo', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('pTo', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<button type="button" class="btn btn-primary" onclick="jQuery.submitbutton4(task)">
						<i class="icon-user icon-white"></i> <?php echo JText::_('JBS_CMN_SUBMIT'); ?>
					</button>
				</div>
			</div>
			<div class="span4" style="border: ridge; padding: 3px">
				<h3 class="tab-description"><?php echo JText::_('JBS_ADM_MEDIATYPES_OPTIONS'); ?></h3>
				<div class="control-group">
					<?php echo $this->form->getLabel('mtFrom', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('mtFrom', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('mtTo', 'params'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('mtTo', 'params'); ?>
					</div>
				</div>
				<div class="control-group">
					<button type="button" class="btn btn-primary" onclick="jQuery.submitbutton6(task)">
						<i class="icon-user icon-white"></i> <?php echo JText::_('JBS_CMN_SUBMIT'); ?>
					</button>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'jwplayer', Text::_('JBS_ADM_JWPLAYER')); ?>
		<div class="row" id="jwplayer">
			<?php echo $this->loadTemplate('jwplayer'); ?>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'convert', Text::_('JBS_IBM_CONVERT')); ?>
		<div class="row" id="convert">
			<h4><?php echo JText::_('JBS_IBM_CONVERT'); ?></h4>
			<h3><?php echo JText::_('CONVERSION_NOT_AVAILABLE_IN_900'); ?></h3>
			<a href="<?php echo JRoute::_('index.php?option=com_proclaim&view=assets&task=cwmadmin.convertPreachIt&' .
				JSession::getFormToken() . '=1'); ?>"
			   title="<?php echo JText::_('JBS_ADM_PREACHIT'); ?>" class="btn"> <i
						class="icon-big icon-list"> </i>
				<span><br/> <?php echo JText::_('JBS_ADM_PREACHIT'); ?> </span></a>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('JBS_IBM_IMAGES')); ?>
		<div class="row" id="images">
			<div class="span12"><h3><?php echo JText::_('JBS_IBM_IMAGES'); ?></h3></div>
			<div class="span6">
				<h4 class="tab-description"><?php echo JText::_('JBS_IBM_OLD_IMAGES'); ?></h4>
				<div class="control-group">
					<?php echo $this->form->getLabel('mediaimage'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('mediaimage'); ?>
					</div>
				</div>
			</div>
			<div class="span6">
				<h4 class="tab-description"><?php echo JText::_('JBS_IBM_NEW_IMAGES'); ?></h4>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_use_button_icon'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_use_button_icon'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_button_text'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_button_text'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_button_type'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_button_type'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_button_color'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_button_color'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_icon_type'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_icon_type'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_custom_icon'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_custom_icon'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_icon_text_size'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_icon_text_size'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('media_image'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('media_image'); ?>
					</div>
				</div>
				<div class="control-group">
					<button type="button" class="btn btn-primary" onclick="jQuery.submitbutton5(task)">
						<i class="icon-user icon-white"></i> <?php echo JText::_('JBS_CMN_SUBMIT'); ?>
					</button>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!-- Track thumbnail sizes to fire event if they are changed -->
		<input type="hidden" id="thumbnail_teacher_size_old"
		       value="<?php echo @$this->item->params['thumbnail_teacher_size']; ?>"/>
		<input type="hidden" id="thumbnail_series_size_old"
		       value="<?php echo @$this->item->params['thumbnail_series_size']; ?>"/>
		<input type="hidden" id="thumbnail_study_size_old"
		       value="<?php echo @$this->item->params['thumbnail_study_size']; ?>"/>
		<input type="hidden" name="tooltype" value=""/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>