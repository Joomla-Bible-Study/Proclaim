<?php
/**
 * Form
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

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
    Joomla.submitbutton = function(task) {
        if (task == 'folder.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
            Joomla.submitform(task, document.getElementById('item-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    
        <!-- Begin Content -->
        <div class="span10 form-horizontal">
        <fieldset>
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a></li>
                
                <?php if ($this->canDo->get('core.admin')): ?>
                    <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
                <?php endif ?>
            </ul>
            <div class="tab-content">
                <!-- Begin Tabs -->
                <div class="tab-pane active" id="general">
                    
                         <div class="control-group">
                            <div class="control-label">
                                <?php echo $this->form->getLabel('id'); ?> 
                            </div>
                            <div class="contols">
                                <?php echo $this->form->getInput('id'); ?>
                            </div>
                         </div>
                         
                         <div class="control-group">
                            <div class="control-label">
                                <?php echo $this->form->getLabel('foldername'); ?>
                            </div> 
                            <div class="contols">
                                <?php echo $this->form->getInput('foldername'); ?>
                            </div>
                        </div>
                       <div class="control-group">
                                <div class="control-label">
                                    <?php echo $this->form->getLabel('folderpath'); ?>
                                </div>
                                <div class="contols">
                                    <?php echo $this->form->getInput('folderpath'); ?>
                                </div>
                            </div>
                    
                </div>
                
                <?php if ($this->canDo->get('core.admin')): ?>
                    <div class="tab-pane" id="permissions">
                         <div class="control-group">
                            
                            <?php echo $this->form->getInput('rules'); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
            <?php echo JHtml::_('form.token'); ?>
            </fieldset>
        </div>
        <!-- End Content -->
        <!-- Begin Sidebar -->
        <div class="span2">
            <h4><?php echo JText::_('JDETAILS'); ?></h4>
            <hr />
            <fieldset class="form-vertical">
                
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('published'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('published'); ?>
                    </div>
                </div>


                
            </fieldset>
        </div>
        <!-- End Sidebar -->
    
</form>