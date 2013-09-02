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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
if (BIBLESTUDY_CHECKREL)
	JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'mediaimage.cancel' || document.formvalidator.isValid(document.id('mediaimage-form'))) {
			Joomla.submitform(task, document.getElementById('mediaimage-form'));
		}
	}
	window.addEvent('domready', function () {
		document.id('jform_type0').addEvent('click', function (e) {
			document.id('image').setStyle('display', 'block');
			document.id('url').setStyle('display', 'block');
			document.id('custom').setStyle('display', 'none');
		});
		document.id('jform_type1').addEvent('click', function (e) {
			document.id('image').setStyle('display', 'none');
			document.id('url').setStyle('display', 'block');
			document.id('custom').setStyle('display', 'block');
		});
		if (document.id('jform_type0').checked == true) {
			document.id('jform_type0').fireEvent('click');
		} else {
			document.id('jform_type1').fireEvent('click');
		}
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="mediaimage-form" class="form-validate">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#details"
				                      data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
				</li>
				<li><a href="#fieldsetrules" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a>
				</li>

			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="details">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_image_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_image_name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_text'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_text'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('path2'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('path2'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_image_path'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_image_path'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('media_alttext'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('media_alttext'); ?>
						</div>
					</div>
				</div>
				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="fieldsetrules">
						<?php echo $this->form->getInput('rules'); ?>
					</div>

				<?php endif; ?>
				<input type="hidden" name="task" value=""/>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
		<!-- Begin Sidebar -->
		<div class="span2 form-vertical">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
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
