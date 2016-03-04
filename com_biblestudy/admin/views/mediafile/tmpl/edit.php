<?php
/**
 * Edit
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

//JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Set up defaults
if (JFactory::getApplication()->input->getInt('id'))
{
	$study_id   = $this->item->study_id;
	$createdate = $this->item->createdate;
	$podcast_id = $this->item->podcast_id;
}
else
{
	$study_id   = $this->options->study_id;
	$createdate = $this->options->createdate;
	$podcast_id = $this->admin_params->get('podcast');
}

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function (task, server_id) {
		if (task == "mediafile.setServer") {
			document.id("media-form").elements["jform[server_id]"].value = server_id;
			Joomla.submitform(task, document.id("media-form"));
		} else if (task == "mediafile.cancel"|| document.formvalidator.isValid(document.id("media-form"))) {
			Joomla.submitform(task, document.getElementById("media-form"));
		} else {
			alert("' . $this->escape(JText::_("JGLOBAL_VALIDATION_FORM_FAILED")) . '");
		}
	}
');
?>
<form action="<?php echo 'index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . (int) $this->item->id; ?>"
      method="post"
      name="adminForm"
      id="media-form"
      class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span12">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#general" data-toggle="tab">
						<?php echo JText::_("JBS_CMN_GENERAL"); ?>
					</a>
				</li>
				<?php foreach ($this->media_form->getFieldsets('params') as $name => $fieldset): ?>
					<li>
						<a href="#<?php echo $name; ?>" data-toggle="tab">
							<?php echo JText::_($fieldset->label); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<a href="#rules" data-toggle="tab">
						<?php echo JText::_("JBS_ADM_ADMIN_PERMISSIONS"); ?>
					</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="general">
					<div class="row-fluid">
						<div class="span9">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('study_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('study_id', null, $study_id); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('createdate'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('createdate', null, $createdate); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('server_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('server_id', null, $this->item->server_id); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('podcast_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('podcast_id', null, $podcast_id); ?>
								</div>
							</div>

						</div>
						<div class="span3 form-vertical">
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('id'); ?>
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
									<?php echo $this->form->getLabel('language'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('language'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('comment'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('comment'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php foreach ($this->media_form->getFieldsets('params') as $name => $fieldset): ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php foreach ($this->media_form->getFieldset($name) as $field): ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<div class="tab-pane" id="rules">
					<?php echo $this->form->getInput('rules'); ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->form->getInput('asset_id'); ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
