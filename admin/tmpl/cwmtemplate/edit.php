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
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

// Create shortcut to parameters.
/** @type \Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useStyle('com_procalim.biblestudy');
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
		<div class="row">
			<div class="col-lg-9 form-horizontal">
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
			</div>
			<div class="col-lg-2 form-vertical">
				<h4><?php echo Text::_('JDETAILS'); ?></h4>
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
		</div>
		<div class="row">
			<?php
			$c     = 0;
			$count = CWMProclaimHelper::halfarray($this->form->getFieldset('TEMPLATES'));
			foreach ($this->form->getFieldset('TEMPLATES') as $field):

				if ($c === 0)
				{
					echo '<div class="col-md-5">';
				}
				elseif ($c === (int) $count->half)
				{
					echo '</div><div class="col-md-5">';
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
				<?php
				$c++;
				if ($c === (int) $count->count)
				{
					echo '</div>';
				}
			endforeach; ?>
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
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'media', Text::_('JBS_CMN_MEDIA')); ?>
		<div class="row">
			<?php
			$c     = 0;
			$count = CWMProclaimHelper::halfarray($this->form->getFieldset('MEDIA'));
			foreach ($this->form->getFieldset('MEDIA') as $field):
				if ($c === 0)
				{
					echo '<div class="col-md-5">';
				}
				elseif ($c === (int) $count->half)
				{
					echo '</div><div class="col-md-5">';
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
				<?php
				(int) $c++;
				if ($c === (int) $count->count)
				{
					echo '</div>';
				}
			endforeach; ?>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'landing', Text::_('JBS_TPL_LANDING_PAGE')); ?>
		<div class="row">
			<div class="col-lg-12">
				<?php
				$c     = 0;
				$count = CWMProclaimHelper::halfarray($this->form->getFieldset('LANDINGPAGE'));
				foreach ($this->form->getFieldset('LANDINGPAGE') as $field):

					if ($c == 0)
					{
						echo '<div class="col-md-5">';
					}
					elseif ($c == $count->half)
					{
						echo '</div><div class="col-md-5">';
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
					<?php
					$c++;
					if ($c == $count->count)
					{
						echo '</div>';
					}
				endforeach; ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'list', Text::_('JBS_TPL_STUDY_LIST_VIEW')); ?>
		<div class="row">
			<div class="accordion" id="accordionlist">
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading1">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
							<?php echo Text::_('JBS_TPL_VERSES_DATES_CSS'); ?>
						</button>
					</h2>
					<div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
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
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading2">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
							<?php echo Text::_('JBS_TPL_LIST_ITEMS'); ?>
						</button>
					</h2>
					<div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('LISTITEMS') as $field): ?>
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
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading3">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
							<?php echo Text::_('JBS_TPL_FILTERS'); ?>
						</button>
					</h2>
					<div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
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
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading4">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
							<?php echo Text::_('JBS_TPL_TOOLTIP'); ?>
						</button>
					</h2>
					<div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
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
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading5">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS1'); ?>
						</button>
					</h2>
					<div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS1') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading6">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS2'); ?>
						</button>
					</h2>
					<div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS2') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="accordion-item">
					<h2 class="accordion-header" id="heading7">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS3'); ?>
						</button>
					</h2>
					<div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS3') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="heading8">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS4'); ?>
						</button>
					</h2>
					<div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS4') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="accordion-item">
					<h2 class="accordion-header" id="heading9">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS5'); ?>
						</button>
					</h2>
					<div id="collapse9" class="accordion-collapse collapse" aria-labelledby="heading9"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS5') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="accordion-item">
					<h2 class="accordion-header" id="heading10">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
						        data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
							<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS6'); ?>
						</button>
					</h2>
					<div id="collapse10" class="accordion-collapse collapse" aria-labelledby="heading10"
					     data-bs-parent="#accordionlist">
						<div class="accordion-body">
							<?php foreach ($this->form->getFieldset('DISPLAYELEMENTS6') as $field): ?>
								<?php if ($field->type !== "Spacer"): ?>
									<div class="pull-left">
										<div>
											<?php echo $field->label; ?>
										</div>
										<div>
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php else: ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JBS_TPL_STUDY_DETAILS_VIEW')); ?>
		<div class="tab-pane" id="details">
			<div id="details-sliders">
				<div class="span2">
					<ul class="nav nav-pills nav-stacked">
						<li class="active">
							<a href="#details-1" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DETAILS_VIEW'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-2" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS1'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-3" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS2'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-4" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS3'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-5" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS4'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-6" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS5'); ?>
							</a>
						</li>
						<li class="">
							<a href="#details-7" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_DISPLAY_ITEMS6'); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content span10">
					<div class="tab-pane active" id="details-1">
						<div class="row-fluid">
							<?php
							$c     = 0;
							$count = CWMProclaimHelper::halfarray($this->form->getFieldset('DETAILS'));
							foreach ($this->form->getFieldset('DETAILS') as $field):

								if ($c == 0)
								{
									echo '<div class="col-md-5">';
								}
								elseif ($c == $count->half)
								{
									echo '</div><div class="col-md-5">';
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
								<?php
								$c++;
								if ($c == $count->count)
								{
									echo '</div>';
								}
							endforeach; ?>
						</div>
					</div>
					<div class="tab-pane" id="details-2">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS1') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tab-pane" id="details-3">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS2') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tab-pane" id="details-4">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS3') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tab-pane" id="details-5">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS4') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tab-pane" id="details-6">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS5') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="tab-pane" id="details-7">
						<?php foreach ($this->form->getFieldset('DDISPLAYELEMENTS6') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'teacher', Text::_('JBS_TPL_TEACHER_VIEW')); ?>
		<div class="tab-pane" id="teacher">
			<div id="details-sliders">
				<div class="span2">
					<ul class="nav nav-pills nav-stacked">
						<li class="active">
							<a href="#teacherlistdetails" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_TEACHERDETAILS'); ?>
							</a>
						</li>
						<li class="">
							<a href="#teacherlistdisplay" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_TEACHERDISPLAY'); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content span10">
					<div class="tab-pane active" id="teacherlistdetails">
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
					<div class="tab-pane" id="teacherlistdisplay">
						<?php foreach ($this->form->getFieldset('TEACHERDISPLAY') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'teacherdetails', Text::_('JBS_TPL_TEACHER_DETAILS')); ?>
		<div class="tab-pane" id="teacherdetails">
			<div id="details-sliders">
				<div class="span2">
					<ul class="nav nav-pills nav-stacked">
						<li class="active">
							<a href="#teacherviewdetails" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_TEACHERDETAILS'); ?>
							</a>
						</li>
						<li class="">
							<a href="#teacherviewdisplay" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_TEACHERDISPLAY'); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content span10">
					<div class="tab-pane active" id="teacherviewdetails">
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
					<div class="tab-pane" id="teacherviewdisplay">
						<?php foreach ($this->form->getFieldset('TEACHERDETAILSDISPALY') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'series', Text::_('JBS_CMN_SERIES')); ?>
		<div class="tab-pane" id="series">
			<div id="details-sliders">
				<div class="span2">
					<ul class="nav nav-pills nav-stacked">
						<li class="active">
							<a href="#serieslist" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_SERIESLIST'); ?>
							</a>
						</li>
						<li class="">
							<a href="#serieslistdisplay" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_SERIESLISTDISPLAY'); ?>
							</a>
						</li>
						<li class="">
							<a href="#seriesdetails" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_SERIESDETAILS'); ?>
							</a>
						</li>
						<li class="">
							<a href="#seriesdetailsdisplay" data-toggle="tab">
								<?php echo Text::_('JBS_TPL_SERIESDETAILSDISPLAY'); ?>
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content span10">
					<div class="tab-pane active" id="serieslist">
						<div class="row-fluid">
							<?php
							$c     = 0;
							$count = CWMProclaimHelper::halfarray($this->form->getFieldset('SERIES'));
							foreach ($this->form->getFieldset('SERIES') as $field):

								if ($c == 0)
								{
									echo '<div class="col-md-5">';
								}
								elseif ($c == $count->half)
								{
									echo '</div><div class="col-md-5">';
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
								<?php
								$c++;
								if ($c == $count->count)
								{
									echo '</div>';
								}
							endforeach; ?>
						</div>
					</div>
					<div class="tab-pane" id="serieslistdisplay">
						<?php foreach ($this->form->getFieldset('SERIESDISPALY') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
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
					<div class="tab-pane" id="seriesdetailsdisplay">
						<?php foreach ($this->form->getFieldset('SERIESDETAILDISPALY') as $field): ?>
							<?php if ($field->type != "Spacer"): ?>
								<div class="pull-left" style="margin:0;">
									<div>
										<?php echo $field->label; ?>
									</div>
									<div>
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php else: ?>
								<div class="clearfix"></div>
								<div class="control-group" style="margin:0;">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if ($this->canDo->get('core.admin')): ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('JBS_CMN_FIELDSET_RULES'); ?></legend>
				<div>
					<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
