<?php
/**
 * Edit
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
$input = Factory::getApplication()->input;

// Set up defaults
if ($input->getInt('id'))
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

$new = ($this->item->id === '0' || empty($this->item->id));

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task, server_id) {
		if (task == "cwmmediafile.setServer") {
			document.getElementById("media-form").elements["jform[server_id]"].value = server_id;
			Joomla.submitform(task, document.getElementById("media-form"));
		} else if (task == "cwmmediafile.cancel"|| document.formvalidator.isValid(document.getElementById("media-form"))) {
			Joomla.submitform(task, document.getElementById("media-form"));
		} else {
			alert("' . $this->escape(Text::_("JGLOBAL_VALIDATION_FORM_FAILED")) . '");
		}
	}
');

$this->useCoreUI = true;
?>
<form action="<?php echo 'index.php?option=com_proclaim&view=cwmmediafile&layout=edit&id=' . (int) $this->item->id; ?>"
      method="post"
      name="adminForm"
      id="media-form"
      class="form-validate">
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<!-- Begin Content -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
		<div class="row">
			<div class="col-lg-7">
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

				<?php echo $this->addon->renderGeneral($this->media_form, $new); ?>

			</div>
			<div class="col-lg-5 form-vertical">
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
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo $this->addon->render($this->media_form, $new); ?>

		<?php if ($this->canDo->get('core.admin')): ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
			<div class="row-fluid">
				<?php echo $this->form->getInput('rules'); ?>
			</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<?php // Load the batch processing form. ?>
		<?php echo HTMLHelper::_(
			'bootstrap.renderModal',
			'collapseModal',
			array(
				'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
				'footer' => $this->loadTemplate('converter_footer')
			),
			$this->loadTemplate('converter_body')
		); ?>
	</div>
	<?php echo $this->form->getInput('asset_id'); ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
