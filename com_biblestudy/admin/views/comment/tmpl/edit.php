<?php
/**
 * Form
 *
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
if (BIBLESTUDY_CHECKREL)
	JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();

$app = JFactory::getApplication();
$input = $app->input;
?>

<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'comment.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
            Joomla.submitform(task, document.getElementById('item-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="row-fluid">
        <!-- Begin Content -->
        <div class="span10 form-horizontal">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a>
                </li>
                
				<?php if ($this->canDo->get('core.admin')): ?>
                <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
				<?php endif ?>
            </ul>
            <div class="tab-content">
                <!-- Begin Tabs -->
                <div class="tab-pane active" id="general">
                    <fieldset class="adminform">
                        <div class="control-group  form-inline">
							<?php echo $this->form->getLabel('id'); ?> <?php echo $this->form->getInput('id'); ?>
                        </div>
						<?php echo $this->form->getInput('comment_text'); ?>
                    </fieldset>
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="control-group">
								<?php echo $this->form->getLabel('study_id'); ?>
                                <div class="controls">
									<?php echo $this->form->getInput('study_id'); ?></div>
                            </div>
                            <div class="control-group">
								<?php echo $this->form->getLabel('comment_date'); ?>
                                <div class="controls">
									<?php echo $this->form->getInput('comment_date'); ?></div>
                            </div>
                            <div class="control-group">
								<?php echo $this->form->getLabel('full_name'); ?>
                                <div class="controls">
									<?php echo $this->form->getInput('full_name'); ?></div>
                            </div>
                            <div class="control-group">
								<?php echo $this->form->getLabel('user_email'); ?>
                                <div class="controls">
									<?php echo $this->form->getInput('user_email'); ?></div>
                            </div>
                            <div class="control-group">
								<?php echo $this->form->getLabel('id'); ?>
                                <div class="controls">
									<?php echo $this->form->getInput('id'); ?></div>
                            </div>
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
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
			<?php echo JHtml::_('form.token'); ?>
        </div>
        <!-- End Content -->
        <!-- Begin Sidebar -->
        <div class="span2">
            <h4><?php echo JText::_('JDETAILS'); ?></h4>
            <hr/>
            <fieldset class="form-vertical">
                <div class="control-group">
                    <div class="controls">
						<?php echo $this->form->getValue('series_text'); ?>
                    </div>
                </div>

                <div class="control-group">
					<?php echo $this->form->getLabel('published'); ?>
                    <div class="controls">
						<?php echo $this->form->getInput('published'); ?>
                    </div>
                </div>

                <div class="control-group">
					<?php echo $this->form->getLabel('access'); ?>
                    <div class="controls">
						<?php echo $this->form->getInput('access'); ?>
                    </div>
                </div>

                <div class="control-group">
					<?php echo $this->form->getLabel('language'); ?>
                    <div class="controls">
						<?php echo $this->form->getInput('language'); ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <!-- End Sidebar -->
    </div>
</form>