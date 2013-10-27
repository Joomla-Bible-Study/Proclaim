<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHtml::_('behavior.tooltip');
	JHtml::stylesheet('media/com_biblestudy/jui/css/bootstrap.css');
	JHtml::script('media/com_biblestudy/jui/js/jquery.js');
	JHtml::script('media/com_biblestudy/jui/js/jquery-noconflict.js');
	JHtml::script('media/com_biblestudy/jui/js/jquery.ui.core.min.js');
	JHtml::script('media/com_biblestudy/jui/js/bootstrap.js');
	JHTML::stylesheet('media/com_biblestudy/jui/css/chosen.css');
	JHTML::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
}

JHtml::script(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
JHtml::script(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$app = JFactory::getApplication();
$input = $app->input;
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<form
		action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=commentlist&a_id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="submitbutton('commentform.save');  ">
					<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="submitbutton('commentform.cancel');  ">
					<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
				</li>
				<li><a href="#parameters" data-toggle="tab"><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></a>
				</li>
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

				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="permissions">
						<div class="control-group">
							<div class="control-label">
								<?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?>
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
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
