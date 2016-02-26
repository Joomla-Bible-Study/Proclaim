<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.framework');

$params = $this->form->getFieldsets('params');
$app    = JFactory::getApplication();
$input  = $app->input;

$return  = base64_encode('index.php?option=com_biblestudy&task=message.edit&id=' . (int) $this->item->id);
$options = base64_encode('study_id=' . $this->item->id . '&createdate=' . $this->item->studydate);

// Set up defaults
if ($input->getInt('id'))
{
	$booknumber  = $this->item->booknumber;
	$thumbnailm  = $this->item->thumbnailm;
	$teacher_id  = $this->item->teacher_id;
	$location_id = $this->item->location_id;
	$series_id   = $this->item->series_id;
	$messagetype = $this->item->messagetype;
	$thumbnailm  = $this->item->thumbnailm;
	$user_id     = $this->item->user_id;
}
else
{
	$booknumber  = $this->admin_params->get('booknumber');
	$thumbnailm  = $this->admin_params->get('default_study_image');
	$teacher_id  = $this->admin_params->get('teacher_id');
	$location_id = $this->admin_params->get('location_id');
	$series_id   = $this->admin_params->get('series_id');
	$messagetype = $this->admin_params->get('messagetype');
	$thumbnailm  = $this->admin_params->get('default_study_image');
	$user_id     = $this->admin->user_id;
}

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function (task)
	{
		if (task == "message.cancel" || document.formvalidator.isValid(document.id("message-form")))
		{
			Joomla.submitform(task, document.getElementById("message-form"));
		}
	};
');

?>

<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=message&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="message-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_STY_DETAILS'); ?></a>
				</li>
				<li><a href="#scripture" data-toggle="tab"><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></a></li>
				<li><a href="#info" data-toggle="tab"><?php echo JText::_('JBS_CMN_INFO'); ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JBS_STY_METADATA'); ?></a></li>
				<li><a href="#media" data-toggle="tab"><?php echo JText::_('JBS_STY_MEDIA_THIS_STUDY'); ?></a></li>
				<li><a href="#publish" data-toggle="tab"><?php echo JText::_('JBS_STY_PUBLISH'); ?></a></li>
				<?php if ($this->canDo->get('core.admin')): ?>
					<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a>
					</li>
				<?php endif ?>
			</ul>
			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('studytitle'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('studytitle'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('studynumber'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('studynumber'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('studyintro'); ?>
						</div>
						<div class="clr"></div>
						<div class="controls">
							<?php echo $this->form->getInput('studyintro'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('studytext'); ?>
						</div>
						<div class="clr"></div>
						<div class="controls">
							<?php echo $this->form->getInput('studytext'); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="scripture">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('booknumber'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'booknumber',
								null,
								$booknumber
							); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('chapter_begin'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('chapter_begin'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('verse_begin'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('verse_begin'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('chapter_end'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('chapter_end'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('verse_end'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('verse_end'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('booknumber2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('booknumber2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('chapter_begin2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('chapter_begin2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('verse_begin2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('verse_begin2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('chapter_end2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('chapter_end2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('verse_end2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('verse_end2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('secondary_reference'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('secondary_reference'); ?>
						</div>
					</div>
				</div>

				<div class="tab-pane" id="info">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image', null, $thumbnailm); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_hours'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_hours'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_minutes'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_minutes'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_seconds'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_seconds'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('teacher_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'teacher_id',
								null,
								$teacher_id
							); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('location_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'location_id',
								null,
								$location_id
							); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('series_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'series_id',
								null,
								$series_id
							); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('topics'); ?>
						</div>
						<div class="clr"></div>
						<div class="controls">
							<?php echo $this->form->getInput('topics'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('messagetype'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'messagetype',
								null,
								$messagetype
							) ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('thumbnailm'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput(
								'thumbnailm',
								null,
								$thumbnailm
							); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="publish">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('user_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('user_id', null, $user_id) ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('user_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('user_name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('modified'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('modified'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('modified_by'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('modified_by'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_up'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_down'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					</div>
				</div>
				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="permissions">
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				<?php endif; ?>
				<?php
				foreach ($params as $name => $fieldSet): ?>
				<div class="tab-pane" id="metadata">
					<?php
					if (isset($fieldset->description) && trim($fieldSet->description)):
						?>
						<p class="tip">
							<?php echo $this->escape(JText::_($fieldSet->description)); ?>
						</p>
					<?php endif; ?>

					<?php foreach ($this->form->getFieldSet($name) as $field) : ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endforeach; ?>
				</div>
				<div class="tab-pane" id="media">
					<table class="adminlist table table-striped">
						<thead>
						<tr>
							<th class="center"><?php echo JText::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
							<th class="center"><?php echo JText::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
							<th class="center hidden-phone">Language</th>
							<th class="center hidden-phone">Access</th>
							<th class="center hidden-phone">ID</th>
						</tr>
						</thead>
						<tbody>

						<?php
						if (count($this->mediafiles) > 0) :
							foreach ($this->mediafiles as $i => $item) :
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td>
										<?php $link = 'index.php?option=com_biblestudy&amp;task=mediafile.edit&amp;id='
											. (int) $item->id . '&amp;return=' . $return . '&amp;options=' . $options; ?>
										<a class="btn btn-primary" href="<?php echo $link; ?>"
										   title="<?php echo $this->escape($item->params->get('filename')) ? $this->escape($item->params->get('filename')) : $this->escape($item->params->get('media_image_name')); ?>">
											<?php echo($this->escape($item->params->get('filename')) ? $this->escape($item->params->get('filename')) : $this->escape($item->params->get('media_image_name'))); ?>
										</a>
									</td>
									<td class="center">
										<?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
									</td>
									<td class="center hidden-phone">
										<?php echo $item->language; ?>
									</td>
									<td class="center hidden-phone">
										<?php echo $item->access_level; ?>
									</td>
									<td class="center hidden-phone">
										<?php echo $item->id; ?>
									</td>

								</tr>
							<?php
							endforeach;
						else:
							?>
							<tr>
								<td colspan="5" class="center"><?php echo JText::_('JBS_STY_NO_MEDIAFILES'); ?></td>
							</tr>
						<?php endif; ?>

						</tbody>
						<tfoot>
						<tr>
							<td colspan="5">
								<?php $link = 'index.php?option=com_biblestudy&amp;task=mediafile.edit&amp;sid='
									. $this->form->getValue('id') . '&amp;options=' . $options . '&amp;return=' . $return; ?>
								<?php
								if (empty($this->item->id))
								{
									?> <a onClick="Joomla.submitbutton('message.apply');"
									      href="#"> <?php echo JText::_('JBS_STY_SAVE_FIRST'); ?> </a> <?php
								}
								else
								{
									?>
									<a class="btn btn-primary" href="<?php echo $link; ?>" title="<?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?>">
										<?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?></a> <?php
								}
								?>
							</td>
						</tr>
						</tfoot>
					</table>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('download_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('download_id'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="span2 form-vertical">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
			<hr/>
			<div class="control-group">
				<div class="control-label">
					<?php echo JText::_('JBS_STY_HITS'); ?>
				</div>
				<div class="controls span12 small">
					<?php echo $this->item->hits; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('studydate'); ?>
				</div>
				<div class="controls span10 small">
					<?php echo $this->form->getInput('studydate'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('comments'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('comments'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('language'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('language'); ?>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<?php echo $this->form->getInput('thumbnailm'); ?>
		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
