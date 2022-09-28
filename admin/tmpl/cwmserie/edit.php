<?php
/**
 * Part of Proclaim Package
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
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$app   = Factory::getApplication();
$input = $app->input;

// Set up defaults
if ($input->getInt('id'))
{
	$series_thumbnail = $this->item->series_thumbnail;
}
else
{
	$series_thumbnail = $this->admin_params->get('default_series_image');
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task)
	{
		if (task == "cwmserie.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>

		<!-- Begin Content -->
		<div class="row">
			<!-- Begin Tabs -->
			<div class="col-lg-8" id="general">
				<fieldset class="adminform">
					<div class="control-group form-inline">
						<?php echo $this->form->getLabel('series_text'); ?><?php echo $this->form->getInput('series_text'); ?>
					</div>
					<?php echo $this->form->getInput('description'); ?>
				</fieldset>
			</div>
			<div class="col-lg-4" id="publishing">
				<div class="control-group">
					<?php echo $this->form->getLabel('alias'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('alias'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('teacher'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('teacher'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('landing_show'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('landing_show'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('pc_show'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('pc_show'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('image'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('image', null, $series_thumbnail); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<?php echo $this->form->getValue('series_text'); ?>
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
						<?php echo $this->form->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
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
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php if ($this->canDo->get('core.admin')): ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
			<div class="tab-pane" id="permissions">
				<fieldset>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	<!-- End Content -->
	<?php echo $this->form->getInput('series_thumbnail'); ?>
	<?php echo $this->form->getInput('id'); ?>
</form>
