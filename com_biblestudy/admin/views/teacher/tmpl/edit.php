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

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('formbehavior.chosen', 'select');
}

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();
$app = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'teacher.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>
<script type="text/javascript">
	function jInsertFieldValue(value, id) {
		var old_id = document.id(id).value;
		if (old_id != id) {
			var elem = document.id(id);
			elem.value = value;
			elem.fireEvent("change");
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
<div class="row-fluid">
<!-- Begin Content -->
<div class="span10 form-horizontal">
<fieldset>
<ul class="nav nav-tabs">
	<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a></li>
	<li><a href="#information" data-toggle="tab"><?php echo JText::_('JBS_TCH_INFO'); ?></a></li>
	<li><a href="#images" data-toggle="tab"><?php echo JText::_('JBS_TCH_IMAGES'); ?></a></li>
	<li><a href="#links" data-toggle="tab"><?php echo JText::_('JBS_TCH_LINKS'); ?></a></li>
	<?php if ($this->canDo->get('core.admin')): ?>
		<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
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
			<?php if ($this->item->contact)
			{
				?>
				<div class="button2-left">
					<div class="blank">
						<a onclick=" document.id('jform_contact_id').value=''; document.id('jform_contact_id').fireEvent('change'); Joomla.submitbutton('teacher.apply'); "
						   title="Clear">
							<?php echo JText::_('JBS_CMN_CLEAR'); ?>
						</a>
					</div>
				</div>
				<div class="button2-left">
					<div class="blank">
						<a href="index.php?option=com_contact&task=contact.edit&id=<?php echo (int) $this->item->contact; ?>"
						   target="blank">'<?php echo JText::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
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
<div class="tab-pane" id="images">
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('teacher_image'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('teacher_image'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('teacher_thumbnail'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('teacher_thumbnail'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('image'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('image'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('thumb'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('thumb'); ?>
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

<?php if ($this->canDo->get('core.admin')): ?>
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
<div class="span2 form-vertical">
	<h4><?php echo JText::_('JDETAILS'); ?></h4>
	<hr/>
	<div class="control-group">
		<div class="control-group">
			<div class="controls">
				<?php echo $this->form->getValue('teachername'); ?>
			</div>
		</div>
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

</form>
