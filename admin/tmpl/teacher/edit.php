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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

/** @var BiblestudyViewTeacher $this */

$app   = Factory::getApplication();
$input = $app->input;

// Set up defaults
if ($input->getInt('id'))
{
	$teacher_thumbnail = $this->item->teacher_thumbnail;
}
else
{
	$teacher_thumbnail = $this->admin->params->get('default_teacher_image');
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$this->useCoreUI = true;

?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task === 'teacher.cancel' || document.formvalidator.isValid(document.getElementById('teacher-form')))
		{
			Joomla.submitform(task, document.getElementById('teacher-form'))
		}
		else
		{
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>')
		}
	}

	function jInsertFieldValue (value, id)
	{
		var old_id = document.id(id).value
		if (old_id !== id)
		{
			var elem = document.id(id)
			elem.value = value
			elem.fireEvent('change')
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="teacher-form" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span8 form-horizontal">
			<fieldset>
				<ul class="nav nav-tabs">
					<li class="active"><a href="#general"
					                      data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a></li>
					<li><a href="#information" data-toggle="tab"><?php echo JText::_('JBS_TCH_INFO'); ?></a></li>
					<li><a href="#links" data-toggle="tab"><?php echo JText::_('JBS_TCH_LINKS'); ?></a></li>
					<?php if ($this->canDo->get('core.administrator')): ?>
						<li><a href="#permissions"
						       data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
					<?php endif ?>
				</ul>
				<div class="tab-content">
					<!-- Begin Tabs -->
					<div class="tab-pane active" id="general">

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('teachername'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('teachername'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('alias'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('alias'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('contact'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('contact'); ?>
								<?php if ($this->form->getValue('contact'))
								{
									?>
									<div class="button2-left">
										<div class="blank">
											<a href="index.php?option=com_contact&task=contact.edit&id=<?php echo (int) $this->form->getValue('contact'); ?>"
											   target="blank"
											   class="btn modal"><?php echo JText::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
											</a>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
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
								<?php echo $this->form->getLabel('address'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('address'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('phone'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('phone'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('email'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('email'); ?>
							</div>
						</div>

					</div>
					<div class="tab-pane" id="information">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('short'); ?>
							</div>
							<div class="clr"></div>
							<div class="controls">
								<?php echo $this->form->getInput('short'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('information'); ?>
							</div>
							<div class="clr"></div>
							<div class="controls">
								<?php echo $this->form->getInput('information'); ?>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="links">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('website'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('website'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('facebooklink'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('facebooklink'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('twitterlink'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('twitterlink'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('bloglink'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('bloglink'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('link1'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('link1'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('linklabel1'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('linklabel1'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('link2'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('link2'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('linklabel2'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('linklabel2'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('link3'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('link3'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('linklabel3'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('linklabel3'); ?>
							</div>
						</div>
					</div>

					<?php if ($this->canDo->get('core.administrator')): ?>
						<div class="tab-pane" id="permissions">
							<fieldset>
								<?php echo $this->form->getInput('rules'); ?>
							</fieldset>
						</div>
					<?php endif; ?>

				</div>
			</fieldset>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>

		<!-- Begin Sidebar -->
		<div class="span4 form-vertical">
			<div class="control-group">
				<div class="control-label">
					<?php if ($this->item->teacher_thumbnail) : ?>
						<img src="<?php echo JUri::root() . $this->item->teacher_thumbnail; ?>"
						     alt="<?php echo $this->form->getValue('teachername'); ?>"
						     class="thumbnail center"/>
					<?php endif; ?>
					<h3 class="text-center">
						<?php echo $this->form->getValue('teachername'); ?>
					</h3>
					<?php echo $this->form->getLabel('image'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('image', null, $teacher_thumbnail); ?>
				</div>
			</div>
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
					<?php echo $this->form->getLabel('access'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('access'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('list_show'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('list_show'); ?>
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
					<?php echo $this->form->getLabel('language'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('language'); ?>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<?php echo $this->form->getInput('id'); ?>
		<?php echo $this->form->getInput('teacher_image'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
</form>
