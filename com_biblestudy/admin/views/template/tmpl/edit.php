<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
/** @type \Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = JFactory::getApplication();
$input  = $app->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'template.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
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
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_GENERAL'); ?></a>
				</li>
				<li><a href="#media" data-toggle="tab"><?php echo JText::_('JBS_CMN_MEDIA'); ?></a></li>
				<li><a href="#landing" data-toggle="tab"><?php echo JText::_('JBS_TPL_LANDING_PAGE'); ?></a></li>
				<li><a href="#list" data-toggle="tab"><?php echo JText::_('JBS_TPL_STUDY_LIST_VIEW'); ?></a></li>
				<li><a href="#details" data-toggle="tab"><?php echo JText::_('JBS_TPL_STUDY_DETAILS_VIEW'); ?></a></li>
				<li><a href="#teacher" data-toggle="tab"><?php echo JText::_('JBS_TPL_TEACHER_VIEW'); ?></a></li>
				<li><a href="#teacherdetails" data-toggle="tab"><?php echo JText::_('JBS_TPL_TEACHER_DETAILS'); ?></a>
				</li>
				<li><a href="#series" data-toggle="tab"><?php echo JText::_('JBS_CMN_SERIES'); ?></a></li>
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
							<?php echo $this->form->getLabel('title'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('text'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('text'); ?>
						</div>
					</div>
					<?php foreach ($this->form->getFieldset('TEMPLATES') as $field): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach; ?>
					<?php foreach ($this->form->getFieldset('TERMS') as $field): ?>
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
				<div class="tab-pane" id="media">
					<?php foreach ($this->form->getFieldset('MEDIA') as $field): ?>
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
				<div class="tab-pane" id="landing">
					<?php foreach ($this->form->getFieldset('LANDINGPAGE') as $field): ?>
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
				<div class="tab-pane" id="list">
					<div id="list-sliders">
						<div class="span2">
							<ul class="nav nav-pills nav-stacked">
								<li class="active">
									<a href="#list-1" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_VERSES_DATES_CSS'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-2" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_LIST_ITEMS'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-3" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_FILTERS'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-10" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS1'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-11" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS2'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-12" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS3'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-13" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS4'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-14" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS5'); ?>
									</a>
								</li>
								<li class="">
									<a href="#list-15" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS6'); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="tab-content span10">
							<div class="tab-pane active" id="list-1">
								<?php foreach ($this->form->getFieldset('VERSES') as $field): ?>
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
							<div class="tab-pane" id="list-2">
								<?php foreach ($this->form->getFieldset('LISTITEMS') as $field): ?>
									<?php
									$thename = $field->label;
									if (substr_count($thename, 'jform_params_list_intro-lbl'))
									{
										echo '<div class="clr"></div>';
									}
									?>
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
							<div class="tab-pane" id="list-3">
								<?php foreach ($this->form->getFieldset('FILTERS') as $field): ?>
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
							<div class="tab-pane" id="list-4">
								<?php foreach ($this->form->getFieldset('TOOLTIP') as $field): ?>
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
							<div class="tab-pane" id="list-10">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS1') as $field): ?>
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
							<div class="tab-pane" id="list-11">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS2') as $field): ?>
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
							<div class="tab-pane" id="list-12">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS3') as $field): ?>
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
							<div class="tab-pane" id="list-13">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS4') as $field): ?>
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
							<div class="tab-pane" id="list-14">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS5') as $field): ?>
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
							<div class="tab-pane" id="list-15">
								<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS6') as $field): ?>
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
				</div>
				<div class="tab-pane" id="details">
					<div id="details-sliders">
						<div class="span2">
							<ul class="nav nav-pills nav-stacked">
								<li class="active">
									<a href="#details-1" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DETAILS_VIEW'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-2" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS1'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-3" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS2'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-4" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS3'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-5" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS4'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-6" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS5'); ?>
									</a>
								</li>
								<li class="">
									<a href="#details-7" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_DISPLAY_ITEMS6'); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="tab-content span10">
							<div class="tab-pane active" id="details-1">
								<?php foreach ($this->form->getFieldset('DETAILS') as $field): ?>
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
							<div class="tab-pane" id="details-2">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS1') as $field): ?>
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
							<div class="tab-pane" id="details-3">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS2') as $field): ?>
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
							<div class="tab-pane" id="details-4">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS3') as $field): ?>
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
							<div class="tab-pane" id="details-5">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS4') as $field): ?>
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
							<div class="tab-pane" id="details-6">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS5') as $field): ?>
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
							<div class="tab-pane" id="details-7">
								<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS6') as $field): ?>
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
				</div>
				<div class="tab-pane" id="teacher">
					<?php foreach ($this->form->getFieldset('TEACHER') as $field): ?>
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
				<div class="tab-pane" id="teacherdetails">
					<?php foreach ($this->form->getFieldset('TEACHERDETAILS') as $field): ?>
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
				<div class="tab-pane" id="series">
					<div id="details-sliders">
						<div class="span2">
							<ul class="nav nav-pills nav-stacked">
								<li class="active">
									<a href="#serieslist" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_SERIESLIST'); ?>
									</a>
								</li>
								<li class="">
									<a href="#seriesdetails" data-toggle="tab">
										<?php echo JText::_('JBS_TPL_SERIESDETAILS'); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="tab-content span10">
							<div class="tab-pane active" id="serieslist">
								<?php foreach ($this->form->getFieldset('SERIES') as $field): ?>
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
							<div class="tab-pane" id="seriesdetails">
								<?php foreach ($this->form->getFieldset('SERIESDETAIL') as $field): ?>
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
				</div>

				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="permissions">
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				<?php endif; ?>
			</div>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span2 form-vertical">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
			<hr/>
			<div class="control-group">
				<div class="controls">
					<?php echo $this->form->getValue('title'); ?>
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
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
	</div>
</form>
