<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'location.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<fieldset>
				<ul class="nav nav-tabs">
					<li class="active"><a href="#general"
					                      data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
					</li>
					<?php if ($this->canDo->get('core.admin')): ?>
						<li><a href="#permissions"
						       data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a>
						</li>
					<?php endif ?>
				</ul>
				<div class="tab-content">
					<!-- Begin Tabs -->
					<div class="tab-pane active" id="general">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('topic_text'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('topic_text'); ?>
							</div>
						</div>

						<?php foreach ($this->form->getFieldset('params') as $field): ?>
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
					<?php if ($this->canDo->get('core.admin')): ?>
						<div class="tab-pane" id="permissions">
							<?php echo $this->form->getInput('rules'); ?>
						</div>
					<?php endif; ?>
				</div>
			</fieldset>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- Begin Sidebar -->
		<div class="span2 form-vertical">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
			<hr/>
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
		</div>
		<!-- End Sidebar -->
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('asset_id'); ?>
</form>
