<?php
/**
 * Edit
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

//JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "mediafiles.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			' . $this->form->getField('articletext')->save() . '
			if (window.opener && (task == "mediafiels.save" || task == "mediafiles.cancel"))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout("window.document.closeEditWindow.close()", 1000);
			}

		Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');

?>

<div class="container-popup">

	<div class="pull-right">
		<button class="btn btn-primary" type="button"
		        onclick="Joomla.submitbutton('article.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
		<button class="btn btn-primary" type="button"
		        onclick="Joomla.submitbutton('article.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
		<button class="btn" type="button"
		        onclick="Joomla.submitbutton('article.cancel');"><?php echo JText::_('JCANCEL') ?></button>
	</div>

	<div class="clearfix"></div>
	<hr class="hr-condensed"/>
	<form
		action="<?php echo 'index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . (int) $this->item->id; ?>"
		method="post"
		name="adminForm"
		id="item-form"
		class="form-validate form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
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
										<?php echo $this->form->getInput('study_id'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('createdate'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('createdate'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('server_id'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('server_id'); ?>
									</div>
								</div>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('podcast_id'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('podcast_id'); ?>
									</div>
								</div>

							</div>
							<div class="span3 form-vertical">
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
					<?php if ($this->canDo->get('core.admin')) : ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_CONTENT_FIELDSET_RULES', true)); ?>
						<?php echo $this->form->getInput('rules'); ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php echo $this->form->getInput('asset_id'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
