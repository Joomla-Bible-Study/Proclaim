<?php
/**
 * Form
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
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
        if (task == 'template.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
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
                <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_TPL_GENERAL'); ?></a></li>
                <li><a href="#media" data-toggle="tab"><?php echo JText::_('JBS_CMN_MEDIA'); ?></a></li>
                <li><a href="#landing" data-toggle="tab"><?php echo JText::_('JBS_TPL_LANDING_PAGE'); ?></a></li>
                <li><a href="#list" data-toggle="tab"><?php echo JText::_('JBS_TPL_STUDY_LIST_VIEW'); ?></a></li>
                <li><a href="#details" data-toggle="tab"><?php echo JText::_('JBS_TPL_STUDY_DETAILS_VIEW'); ?></a></li>
                <li><a href="#teacher" data-toggle="tab"><?php echo JText::_('JBS_TPL_TEACHER_VIEW'); ?></a></li>
                <li><a href="#series" data-toggle="tab"><?php echo JText::_('JBS_CMN_SERIES'); ?></a></li>
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
                        <?php echo $this->form->getLabel('title'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('title'); ?></li>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('text'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('text'); ?></li>
                    </div>
                </div>
                <?php foreach ($this->form->getFieldset('TEMPLATES') as $field): ?>
                     <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($this->form->getFieldset('TERMS') as $field): ?>
                     <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            </div>
            <div class="tab-pane" id="media">
                <?php foreach ($this->form->getFieldset('MEDIA') as $field): ?>
                     <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tab-pane" id="landing">
                <?php foreach ($this->form->getFieldset('LANDINGPAGE') as $field): ?>
                     <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tab-pane" id="list">
                <?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_VERSES_DATES_CSS'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_VERSES_DATES_CSS'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('VERSES') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_LIST_ITEMS'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_LIST_ITEMS'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('LISTITEMS') as $field): ?>
                            <?php
                            $thename = $field->label;
                            if (substr_count($thename, 'jform_params_list_intro-lbl')) {
                                echo '<div class="clr"></div>';
                            }
                            ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_FILTERS'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_FILTERS'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('FILTERS') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_TOOLTIP_ITEMS'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_TOOLTIP_ITEMS'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('TOOLTIP') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW1'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('ROW1') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW2'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW1'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('ROW2') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW3'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW3'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('ROW3') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_ROW4'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_ROW4'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('ROW4') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_STUDY_LIST_CUSTOM'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_STUDY_LIST_CUSTOM'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('STUDIESVIEW') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.end'); ?>
            </div>
            <div class="tab-pane" id="details">
                <?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_VIEW'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_DETAILS_VIEW'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('DETAILS') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW1'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW1'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('DETAILSROW1') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW2'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW2'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('DETAILSROW2') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW3'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW3'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('DETAILSROW3') as $field): ?>
                           <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_DETAILS_LIST_ROW4'), 'publishing-details'); ?>
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_TPL_DETAILS_LIST_ROW4'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset('DETAILSROW4') as $field): ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label;?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php echo JHtml::_('sliders.end'); ?>
            </div>
            <div class="tab-pane" id="teacher">
                <?php foreach ($this->form->getFieldset('TEACHER') as $field): ?>
                     <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tab-pane" id="series">
                <?php echo JHtml::_('sliders.start', 'content-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_TPL_SERIES_LIST'), 'publishing-details'); ?>
                <ul class="adminformlist">
                    <?php foreach ($this->form->getFieldset('SERIES') as $field): ?>
                        <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </ul>
                <?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_SERIES_DETAIL_VIEW'), 'publishing-details'); ?>
                <ul class="adminformlist">
                    <?php foreach ($this->form->getFieldset('SERIESDETAIL') as $field): ?>
                        <div class="control-group">
                        <div class="control-label">
                            <?php echo $field->label;?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </ul>
                <?php echo JHtml::_('sliders.end'); ?>
            </div>


                <?php if ($this->canDo->get('core.admin')): ?>
                    <div class="tab-pane" id="permissions">
                        
                            <?php echo $this->form->getInput('rules'); ?>
                        
                    </div>
                <?php endif; ?>
    <input type="hidden" name="task" value="" />
    </fieldset>
    </div>
   </div>
    <?php echo JHtml::_('form.token'); ?>
</form>