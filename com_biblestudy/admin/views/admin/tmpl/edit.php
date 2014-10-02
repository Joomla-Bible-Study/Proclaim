<?php
/**
 * Admin Form
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('jquery.framework');

if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('formbehavior.chosen', 'select');
}

$app = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
	jQuery.submitbutton3 = function () {
		jQuery('[name=tooltype]').val('players');
		jQuery('[name=task]').val('admin.tools');
		jQuery("#item-admin").submit();
	};

	jQuery.submitbutton4 = function () {
		jQuery('[name=tooltype]').val('popups');
		jQuery('[name=task]').val('admin.tools');
		jQuery("#item-admin").submit();
	};

	Joomla.submitbutton = function (task) {
		if (task == 'admin.cancel' || document.formvalidator.isValid(document.id('item-admin'))) {
			Joomla.submitform(task, document.getElementById('item-admin'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="item-admin" class="form-validate">
<div class="row-fluid">
<!-- Begin Content -->
<div class="span12 form-horizontal">
<ul class="nav nav-tabs">
	<li class="active"><a href="#cpanl" data-toggle="tab"><?php echo JText::_('JBS_ADM_ADMIN_CPANL'); ?></a></li>
	<li><a href="#admin" data-toggle="tab"><?php echo JText::_('JBS_ADM_ADMIN_PARAMS'); ?></a></li>
	<li><a href="#defaults" data-toggle="tab"><?php echo JText::_('JBS_ADM_SYSTEM_DEFAULTS'); ?></a></li>
	<li><a href="#playersettings" data-toggle="tab"><?php echo JText::_('JBS_ADM_PLAYER_SETTINGS'); ?></a></li>
	<li><a href="#jwplayer" data-toggle="tab"><?php echo JText::_('JBS_ADM_JWPLAYER'); ?></a></li>
	<li><a href="#convert" data-toggle="tab"><?php echo JText::_('JBS_IBM_CONVERT'); ?></a></li>
</ul>

<div class="tab-content">

<!-- Begin Tabs -->
<div class="tab-pane active" id="cpanl">
	<div id="cpanel" style="padding-left: 20px">
		<div style="float:left;">
			<div class="icon"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=assets'); ?>"
			                     style="text-decoration:none;"
			                     title="Assets Check"> <img
						src="../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align="middle"
						border="0"/> <span> Assets Check </span></a></div>
		</div>
		<div style="float:left;">
			<div class="icon"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=database'); ?>"
			                     style="text-decoration:none;"
			                     title="Database Check"> <img
						src="../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align="middle"
						border="0"/> <span> Database Check </span></a></div>
		</div>
		<div style="float:left;">
			<div class="icon"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=migrate'); ?>"
			                     style="text-decoration:none;"
			                     title="Migrate"> <img
						src="../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align="middle"
						border="0"/> <span> Migrate </span></a></div>
		</div>
		<div style="float:left;">
			<div class="icon"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=backup'); ?>"
			                     style="text-decoration:none;"
			                     title="Backup / Restor"> <img
						src="../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align="middle"
						border="0"/> <span> Backup / Restor </span></a></div>
		</div>
	</div>
</div>
<div class="tab-pane" id="admin">
	<h4><?php echo JText::_('JBS_ADM_COMPONENT_SETTINGS'); ?></h4>

	<div class="control-group">
		<?php echo $this->form->getLabel('jbsmigrationshow', 'params'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('jbsmigrationshow', 'params'); ?>
		</div>
	</div>
	<div class="control-group">
		<label style="max-width: 100%; padding: 0 5px 0 0;">
			<!--suppress HtmlUnknownTarget -->
			<a href="index.php?option=com_biblestudy&view=admin&layout=edit&task=admin.aliasUpdate">
				<?php echo JText::_('JBS_ADM_RESET_ALIAS') ?>
			</a>
		</label>
	</div>
	<div class="control-group">
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
<div class="tab-pane" id="defaults">
	<div class="row-fluid">
		<div class="span6">
			<h4><?php echo JText::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h4>

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
		<div class="span6">
			<h4><?php echo JText::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h4>

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
				<?php echo $this->form->getLabel('path', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('path', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('podcast', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('podcast', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('media_image', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('media_image', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('mime', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('mime', 'params'); ?>
				</div>
			</div>
		</div>
		<div>
			<h4><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></h4>

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
				<?php echo $this->form->getLabel('default_download_image', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('default_download_image', 'params'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('default_showHide_image', 'params'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('default_showHide_image', 'params'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="tab-pane" id="playersettings">
	<div class="span6">
		<h4><?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?></h4>

		<div class="control-group">
			<?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?><br/>

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
				<i class="icon-user icon-white"></i> Submit
			</button>
		</div>
	</div>
	<div class="span6">
		<h4><?php echo JText::_('JBS_ADM_POPUP_OPTIONS'); ?></h4>

		<div class="control-group">
			<?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?><br/>

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
				<i class="icon-user icon-white"></i> Submit
			</button>
		</div>
	</div>
</div>
<div class="tab-pane" id="jwplayer">
	<?php echo $this->loadTemplate('jwplayer'); ?>
</div>
<div class="tab-pane" id="convert">
	<h4><?php echo JText::_('JBS_IBM_CONVERT'); ?></h4>

	<div> <?php echo $this->ss; ?> </div>
	<div> <?php echo $this->pi; ?> </div>
</div>
</div>
</div>
</div>
<div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
