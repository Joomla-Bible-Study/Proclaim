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

// Create shortcut to parameters.
$app = JFactory::getApplication();
$input = $app->input;

$this->config = $this->form->getFieldset('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task, type) {
        if(task == 'server.setType') {
            document.id('item-form').elements['jform[server_type]'].value = type;
            Joomla.submitform(task, document.id('item-form'));
        } else if (task == 'server.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
				</li>
                <?php if(count($this->config) > 0): ?>
                <li><a href="#server_config" data-toggle="tab"><?PHP echo JText::_('*SERVER CONFIGURATION*'); ?></a></li>
                <?php endif; ?>
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
                            <?php echo $this->form->getLabel('server_name'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->form->getInput('server_name'); ?>
                        </div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('server_type'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('server_type'); ?>
						</div>
					</div>
				</div>
                <?php if(count($this->config) > 0): ?>
                    <div class="tab-pane" id="server_config">
                        <?php echo $this->loadTemplate('configuration'); ?>
                    </div>
                <?php endif; ?>
				<?php if ($this->canDo->get('core.admin')): ?>
					<div class="tab-pane" id="permissions">
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				<?php endif; ?>

			</div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span2 form-vertical">
			<h4><?php echo JText::_('JDETAILS'); ?></h4>
			<hr/>
			<div class="control-group">
				<div class="controls">
					<?php echo $this->form->getValue('server_bane'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('published'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
	</div>
</form>
