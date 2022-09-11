<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

// Create shortcut to parameters.
$app   = Factory::getApplication();
$input = $app->input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->useStyle('com_proclaim.cwmcore');
?>
<form action="<?php echo JRoute::_('index.php?option=com_proclaim&view=cwmserver&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="server-form" class="form-validate">
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
		<div class="row">
			<div class="col-lg-7">
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
			</div>

			<div class="col-lg-5 form-vertical">
				<h4>
					<?php
					if (isset($this->item->id) && isset($this->itemaddon))
					{
						echo $this->escape($this->item->addon->name);
					}
					?>
				</h4>

				<p>
					<?php
					if (isset($this->item->id) && isset($this->itemaddon))
					{
						echo $this->escape($this->item->addon->description);
					}
					?>
				</p>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php foreach ($this->server_form->getFieldsets('params') as $fieldsets): ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', strtolower(Text::_($fieldsets->label)), Text::_($fieldsets->label)); ?>
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
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'media_settings', Text::_('JBS_SVR_MEDIA_SETTINGS')); ?>
		<div class="row">
			<div class="accordion" id="accordionlist">
				<?php foreach ($this->server_form->getFieldsets('media') as $name => $fieldset): ?>
					<div class="accordion-item">
						<h2 class="accordion-heading" id="<?php echo Text::_($name) ?>">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
							        data-bs-target="#collapse<?php echo Text::_($name) ?>" aria-expanded="false"
							        aria-controls="collapse<?php echo Text::_($name) ?>">
								<?php echo Text::_($fieldset->label); ?>
							</button>
						</h2>
						<div id="collapse<?php echo Text::_($name) ?>" class="accordion-collapse collapse"
						     aria-labelledby="heading<?php echo $name; ?>"
						     data-bs-parent="#accordionlist">
							<div class="accordion-body">
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
				<?php endforeach; ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php if ($this->canDo->get('core.admin')): ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
			<div class="row-fluid">
				<?php echo $this->form->getInput('rules'); ?>
			</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endif; ?>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
