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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

// Load the tooltip behavior.
HTMLHelper::_('formbehavior.chosen', 'select');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task) {
		if (task == "cwmtopic.cancel" || document.formvalidator.isValid(document.id("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"))
		}
		else
		{
			alert(' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . ')
		}
	}
');

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>

		<ul class="nav nav-tabs">
			<li class="active"><a href="#general"
			                      data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
			</li>
			<?php if ($this->canDo->get('core.cwmadmin')): ?>
				<li><a href="#permissions"
				       data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a>
				</li>
			<?php endif ?>
		</ul>
		<div class="row">
			<div class="col-lg-9">
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

			<div class="col-lg-2 form-vertical">
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
		</div>
		<?php if ($this->canDo->get('core.cwmadmin')): ?>
			<div class="tab-pane" id="permissions">
				<?php echo $this->form->getInput('rules'); ?>
			</div>
		<?php endif; ?>
		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	<?php echo $this->form->getInput('id'); ?>
	<?php echo $this->form->getInput('asset_id'); ?>
</form>
