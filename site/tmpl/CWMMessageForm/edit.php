<?php
/**
 * Form
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
// Load the tooltip behavior.
HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.modal');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('formbehavior.chosen', 'select');
HtmlHelper::_('behavior.framework');

$return  = base64_encode('index.php?option=com_proclaim&task=messageform.edit&a_id=' . (int) $this->item->id);
$options = base64_encode('study_id=' . $this->item->id . '&createdate=' . $this->item->studydate);

$params = $this->form->getFieldsets('params');
$app    = Factory::getApplication();
$input  = $app->input;

// Set up defaults
if ($input->getInt('a_id'))
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
	$booknumber  = $this->state->params->get('booknumber');
	$thumbnailm  = $this->state->params->get('default_study_image');
	$teacher_id  = $this->state->params->get('teacher_id');
	$location_id = $this->state->params->get('location_id');
	$series_id   = $this->state->params->get('series_id');
	$messagetype = $this->state->params->get('messagetype');
	$thumbnailm  = $this->state->params->get('default_study_image');
	$user_id     = Factory::getUser()->id;
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'messageform.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<form
			action="<?php echo Route::_('index.php?option=com_proclaim&view=messageform&a_id=' . (int) $this->item->id); ?>"
			method="post" name="adminForm" id="adminForm" class="form-validate form-vertical"
			enctype="multipart/form-data">
		<div class="row-fluid">
			<div class="btn-toolbar">
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('messageform.save')">
						<i class="icon-ok"></i> <?php echo Text::_('JSAVE') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('messageform.cancel')">
						<i class="icon-cancel"></i> <?php echo Text::_('JCANCEL') ?>
					</button>
				</div>
			</div>
			<div class="span12 form-horizontal">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#general"
					                      data-toggle="tab"><?php echo Text::_('JBS_STY_DETAILS'); ?></a></li>
					<li><a href="#publishing"
					       data-toggle="tab"><?php echo Text::_('JBS_CMN_PUBLISHING_OPTIONS'); ?></a></li>
					<li><a href="#scripture" data-toggle="tab"><?php echo Text::_('JBS_CMN_SCRIPTURE'); ?></a></li>
					<li><a href="#info" data-toggle="tab"><?php echo Text::_('JBS_CMN_INFO'); ?></a></li>
					<li><a href="#metadata" data-toggle="tab"><?php echo Text::_('JBS_STY_METADATA'); ?></a></li>
					<li><a href="#media" data-toggle="tab"><?php echo Text::_('JBS_STY_MEDIA_THIS_STUDY'); ?></a></li>
					<?php if ($this->canDo->get('core.administrator')): ?>
						<li><a href="#permissions" data-toggle="tab"><?php echo Text::_('JBS_CMN_FIELDSET_RULES'); ?></a>
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
								<?php echo $this->form->getInput('booknumber', null, $booknumber); ?>
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
								<?php echo $this->form->getLabel('teacher_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('teacher_id', null, $teacher_id) ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('location_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('location_id', null, $location_id) ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('series_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('series_id', null, $series_id) ?>
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
								<?php echo $this->form->getInput('messagetype', null, $messagetype) ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('thumbnailm'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('thumbnailm', null, $thumbnailm) ?>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="publishing">
						<div class="control-group">
							<div class="control-label">
								<?php echo Text::_('JBS_STY_HITS'); ?>
							</div>
							<div class="controls">
								<?php echo $this->item->hits; ?>
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
							<div class="controls">
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
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('language'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('language'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>
					</div>
					<?php if ($this->canDo->get('core.administrator')): ?>
						<div class="tab-pane" id="permissions">

							<?php echo $this->form->getInput('rules'); ?>

						</div>
					<?php endif; ?>
					<div class="tab-pane" id="metadata">
						<?php
						foreach ($params as $name => $fieldset):
							if (isset($fieldset->description) && trim($fieldset->description)):
								?>
								<p class="tip">
									<?php echo $this->escape(Text::_($fieldset->description)); ?>
								</p>
							<?php endif; ?>
							<?php foreach ($this->form->getFieldset($name) as $field) : ?>
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
						<table class="table table-striped adminlist">
							<thead>
							<tr>
								<th class="center"><?php echo Text::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
								<th class="center"><?php echo Text::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php
							if (count($this->mediafiles) > 0) :
								foreach ($this->mediafiles as $i => $item) :
									?>
									<tr class="row<?php echo $i % 2; ?>">
										<td>
											<?php $link = 'index.php?option=com_proclaim&amp;task=mediafileform.edit&amp;a_id='
													. (int) $item->id . '&amp;return=' . $return . '&amp;options=' . $options; ?>
											<a class="btn btn-primary" href="<?php echo $link; ?>"
											   title="<?php echo $this->escape($item->params->get('filename')) ? $this->escape($item->params->get('filename')) : $this->escape($item->params->get('media_image_name')); ?>">
												<?php echo($this->escape($item->params->get('filename')) ? $this->escape($item->params->get('filename')) : $this->escape($item->params->get('media_image_name'))); ?>
											</a>
										</td>
										<td class="center">
											<?php echo HtmlHelper::_('date', $item->createdate, Text::_('DATE_FORMAT_LC4')); ?>
										</td>

									</tr>
								<?php
								endforeach;
							else:
								?>
								<tr>
									<td colspan="4" class="center"><?php echo Text::_('JBS_STY_NO_MEDIAFILES'); ?></td>
								</tr>
							<?php endif; ?>

							</tbody>
							<tfoot>
							<tr>
								<td colspan="4">
									<?php $link = 'index.php?option=com_proclaim&amp;task=mediafileform.edit&amp;sid='
											. $this->form->getValue('id') . '&amp;options=' . $options . '&amp;return=' . $return;  ?>
									<?php
									if (empty($this->item->id))
									{
										?> <a onClick="Joomla.submitbutton('messageform.apply');"
										      href="#"> <?php echo Text::_('JBS_STY_SAVE_FIRST'); ?> </a> <?php
									}
									else
									{
										?>
										<a class="btn btn-primary" href="<?php echo $link; ?>" title="<?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?>">
											<?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?></a> <?php
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
				<!-- End Sidebar -->
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
				<?php echo HtmlHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
