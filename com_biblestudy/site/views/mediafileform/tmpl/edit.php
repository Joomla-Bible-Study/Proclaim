<?php
/**
 * Edit
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');

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
	$podcast_id = $this->params->get('podcast');
}

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function (task, server_id) {
		if (task == 'mediafileform.setServer') {
			document.id('item-form').elements['jform[server_id]'].value = server_id;
			Joomla.submitform(task, document.id('item-form'));
		} else if (task == 'mediafileform.cancel') {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else if (task == 'mediafileform.apply' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('" . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
		}
	};
");
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>
	<form action="<?php echo 'index.php?option=com_biblestudy&view=mediafileform&layout=edit&id=' . (int) $this->item->id; ?>"
	      method="post"
	      name="adminForm"
	      id="item-form"
	      class="form-validate form-horizontal">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('mediafileform.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('mediafileform.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_("JBS_CMN_GENERAL"); ?></a>
				</li>
				<?php foreach ($this->media_form->getFieldsets('params') as $name => $fieldset): ?>
					<li><a href="#<?php echo $name; ?>" data-toggle="tab"><?php echo JText::_($fieldset->label); ?></a>
					</li>
				<?php endforeach; ?>
				<li><a href="#rules" data-toggle="tab"> <?php echo JText::_("JBS_ADM_ADMIN_PERMISSIONS"); ?></a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="general">
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
							<?php echo $this->form->getInput('server_id'); ?>
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
				<?php foreach ($this->media_form->getFieldsets('params') as $name => $fieldset): ?>
					<div class="tab-pane" id="<?php echo $name; ?>">
						<?php foreach ($this->media_form->getFieldset($name) as $field): ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php
									// Way to set defaults on new media
									if ($new)
									{
										$s_name = $field->fieldname;
										if (isset($this->media_form->s_params[$s_name]))
										{
											$field->setValue($this->media_form->s_params[$s_name]);
										}
									}
									?>
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
			<?php echo $this->form->getInput('asset_id'); ?>
			<?php echo $this->form->getInput('id'); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return"
			       value="<?php echo JFactory::getApplication()->input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>

		</fieldset>
	</form>
</div>
