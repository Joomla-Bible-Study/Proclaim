<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
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
$params = $this->state->get('params');
$params = $params->toArray();
$app    = JFactory::getApplication();
$input  = $app->input;
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'server.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
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
                <li><a href="#ftp" data-toggle="tab"><?php echo JText::_('JBS_CMN_FTP'); ?></a></li>
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
                        <div class="control-group form-inline">
							<?php echo $this->form->getLabel('server_name'); ?> <?php echo $this->form->getInput('server_name'); ?>
                        </div>

                        <div class="control-group">
                            <div class="control-label">
								<?php echo $this->form->getLabel('server_path'); ?>
                            </div>
                            <div class="controls">
								<?php echo $this->form->getInput('server_path'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
								<?php echo $this->form->getLabel('type'); ?>
                            </div>
                            <div class="controls">
								<?php echo $this->form->getInput('type'); ?>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="tab-pane" id="ftp">
                    <div class="row-fluid">
                        <div class="span6">
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('ftphost'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('ftphost'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('ftpuser'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('ftpuser'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('ftppassword'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('ftppassword'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('ftpport'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('ftpport'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('aws_key'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('aws_key'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $this->form->getLabel('aws_secret'); ?>
                                </div>
                                <div class="controls">
									<?php echo $this->form->getInput('aws_secret'); ?>
                                </div>
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
						<?php echo $this->form->getValue('server_bane'); ?>
                    </div>
                </div>

                <div class="control-group">
					<?php echo $this->form->getLabel('published'); ?>
                    <div class="controls">
						<?php echo $this->form->getInput('published'); ?>
                    </div>
                </div>


            </fieldset>
        </div>
        <!-- End Sidebar -->
    </div>
</form>