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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

//JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$app   = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "mediafile.cancel" || document.formvalidator.isValid(document.getElementById("mediafile-modal")))
		{
			if (window.opener && (task == "mediafile.save" || task == "mediafile.cancel"))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout("window.document.closeEditWindow.close()", 1000);
			}

		Joomla.submitform(task, document.getElementById("mediafile-modal"));
		}
	};
');
?>
<div class="container-popup">

	<div class="pull-right">
		<button class="btn btn-primary" type="button"
		        onclick="Joomla.submitbutton('mediafile.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
		<button class="btn btn-primary" type="button"
		        onclick="Joomla.submitbutton('mediafile.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
		<button class="btn" type="button"
		        onclick="Joomla.submitbutton('mediafile.cancel');"><?php echo JText::_('JCANCEL') ?></button>
	</div>

	<div class="clearfix"></div>
	<hr class="hr-condensed"/>
	<form
		action="<?php echo 'index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . (int) $this->item->id; ?>"
		method="post" name="adminForm" id="mediafile-modal" class="form-validate form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'mediafiles', array('active' => 'modalgeneral')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'mediafiles', 'modalgeneral', JText::_("JBS_CMN_GENERAL", true)); ?>
		<div class="row-fluid">
			<div class="span12">
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
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'mediafiles', 'modalpub', JText::_("JBS_CMN_GENERAL", true)); ?>
		<div class="row-fluid">
			<div class="span12">
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
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php foreach ($this->media_form->getFieldsets('params') as $name => $fieldset): ?>
			<?php echo JHtml::_('bootstrap.addTab', 'mediafiles', 'modal' . $name, JText::_($fieldset->label, true)); ?>
			<div class="row-fluid">
				<div class="span12">
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
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endforeach; ?>
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'mediafiles', 'modelpermissions', JText::_('COM_CONTENT_FIELDSET_RULES', true)); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<?php echo $this->form->getInput('asset_id'); ?>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
