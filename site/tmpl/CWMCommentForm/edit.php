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
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
HtmlHelper::_('formbehavior.chosen', 'select');
HtmlHelper::_('proclaim.framework');
HTMLHelper::_('proclaim.loadcss', $this->state->params);

HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$input = $app->input;
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<form
		action="<?php echo Route::_('index.php?option=com_proclaim&view=commentfrom&a_id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="submitbutton('commentform.save');  ">
					<i class="icon-ok"></i> <?php echo Text::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="submitbutton('commentform.cancel');  ">
					<i class="icon-cancel"></i> <?php echo Text::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo Text::_('JBS_CMN_DETAILS'); ?></a>
				</li>
				<li><a href="#parameters" data-toggle="tab"><?php echo Text::_('JBS_CMN_PARAMETERS'); ?></a>
				</li>
				<?php if ($this->canDo->get('core.administrator')): ?>
					<li><a href="#permissions" data-toggle="tab"><?php echo Text::_('JBS_CMN_FIELDSET_RULES'); ?></a>
					</li>
				<?php endif ?>
			</ul>
			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
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
							<?php echo $this->form->getLabel('comment_text'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('comment_text'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('study_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('study_id'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('comment_date'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('comment_date'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('full_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('full_name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('user_email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('user_email'); ?>
						</div>
					</div>
				</div>

				<?php if ($this->canDo->get('core.administrator')): ?>
					<div class="tab-pane" id="permissions">
						<div class="control-group">
							<div class="control-label">
								<?php echo Text::_('JBS_CMN_FIELDSET_RULES'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('rules'); ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<div class="tab-pane" id="parameters">
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
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>"/>
			<?php echo HtmlHelper::_('form.token'); ?>
		</fieldset>
	</form>
</div>
