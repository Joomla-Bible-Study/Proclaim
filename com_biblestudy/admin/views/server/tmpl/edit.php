<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

// Create shortcut to parameters.
$app   = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task, type) {
		if (task == 'server.setType') {
			document.id('server-form').elements['jform[type]'].value = type;
			Joomla.submitform(task, document.id('server-form'));
		} else if (task == 'server.cancel') {
			Joomla.submitform(task, document.getElementById('server-form'));
		} else if (task == 'server.apply' || document.formvalidator.isValid(document.id('server-form'))) {
			Joomla.submitform(task, document.getElementById('server-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="server-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span8 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
				</li>
				<?php foreach ($this->server_form->getFieldsets('params') as $fieldsets): ?>
					<li>
						<a href="#<?php echo $fieldsets->name; ?>" data-toggle="tab">
							<?php echo JText::_($fieldsets->label); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if (count($this->server_form->getFieldsets('media')) > 0): ?>
					<li>
						<a href="#media_settings" data-toggle="tab">
							<?php echo JText::_("JBS_SVR_MEDIA_SETTINGS"); ?>
						</a>
					</li>
				<?php endif; ?>
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
							<?php echo $this->form->getLabel('server_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('server_name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('type'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('type'); ?>
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
				</div>
				<?php foreach ($this->server_form->getFieldsets('params') as $fieldset): ?>
					<div class="tab-pane" id="<?php echo $fieldset->name; ?>">
						<?php foreach ($this->server_form->getFieldset($fieldset->name) as $field): ?>
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
				<div class="tab-pane" id="media_settings">
					<div class="accordion" id="accordion">
						<?php $first = true; ?>
						<?php foreach ($this->server_form->getFieldsets('media') as $name => $fieldset): ?>
							<div class="accordion-group">
								<div class="accordion-heading">
									<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
									   href="#<?php echo $name; ?>">
										<?php echo JText::_($fieldset->label); ?>
									</a>
								</div>
								<div id="<?php echo $name; ?>"
								     class="accordion-body collapse <?php echo $first ? "in" : ""; ?>">
									<div class="accordion-inner">
										<?php foreach ($this->server_form->getFieldset($name) as $field): ?>
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
							</div>
							<?php $first = false; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="permissions">
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				<?php endif; ?>
			</div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span3 form-vertical">
			<h4>
				<?php
                if (!is_null($this->item->id)) {echo $this->escape($this->item->addon->name); }
				?>
			</h4>

			<p>
				<?php
                if (!is_null($this->item->id)) {echo $this->escape($this->item->addon->description);}
				?>
			</p>
		</div>
		<!-- End Sidebar -->
	</div>
</form>
