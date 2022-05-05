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
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

// Load the tooltip behavior.
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

// Set up defaults
if ($input->getInt('a_id'))
{
	$templatecode = $this->item->templatecode;
}
else
{
	$templatecode = $this->defaultcode;
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task)
	{
		if (task == "cwmtemplatecode.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
	');
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo Text::_('JBS_CMN_DETAILS'); ?></a>
				</li>
				<?php if ($this->canDo->get('core.admin')): ?>
					<li><a href="#permissions" data-toggle="tab"><?php echo Text::_('JBS_CMN_FIELDSET_RULES'); ?></a>
					</li>
				<?php endif ?>
			</ul>
			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('filename'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('filename'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('type'); ?>
						</div>
						<div class="controls">
							<?php
							if ($this->item->id == 0)
							{
								echo $this->form->getInput('type');
							}
							else
							{
								?><label id="jform_type-lbl" for="jform_type"
								         style="clear: both;"><?php echo $this->type ?></label>
							<?php } ?>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('templatecode'); ?>
							</div>
							<div class="clr"></div>
							<hr/>
							<div class="editor-border">
								<?php echo $this->form->getInput('templatecode', null, $templatecode); ?>
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
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		<!-- Begin Sidebar -->
		<div class="span2 form-vertical">
			<h4><?php echo Text::_('JDETAILS'); ?></h4>
			<hr/>
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
