<?php
/**
 * Form
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
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
        if (task == 'podcast.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
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
    <li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></a></li>
    <li><a href="#images" data-toggle="tab"><?php echo JText::_('JBS_PDC_PODCAST_IMAGES'); ?></a></li>

    <?php if ($this->canDo->get('core.admin')): ?>
    <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
    <?php endif ?>
</ul>
<div class="tab-content">
<!-- Begin Tabs -->
<div class="tab-pane" id="publishing">
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
            <?php echo $this->form->getLabel('filename'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('filename'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcastlimit'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcastlimit'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('episodetitle'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('episodetitle'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('custom'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('custom'); ?></li>
        </div>
    </div>
</div>
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
            <?php echo $this->form->getLabel('title'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('title'); ?></li>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('description'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('description'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('website'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('website'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('author'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('author'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('editor_name'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('editor_name'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('editor_email'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('editor_email'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcastsearch'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcastsearch'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('language'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('language'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcastlanguage'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcastlanguage'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('detailstemplateid'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('detailstemplateid'); ?></li>
        </div>
    </div>

</div>
<div class="tab-pane" id="images">
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('image'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('image'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcastimage'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcastimage'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcast_image_subscribe'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcast_image_subscribe'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcast_subscribe_desc'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcast_subscribe_desc'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('alternatelink'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('alternatelink'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('alternateimage'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('alternateimage'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('podcast_subscribe_show'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('podcast_subscribe_show'); ?></li>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('alternatewords'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('alternatewords'); ?></li>
        </div>
    </div>
</div>

<?php if ($this->canDo->get('core.admin')): ?>
<div class="tab-pane" id="permissions">

    <?php echo $this->form->getInput('rules'); ?>

</div>
    <?php endif; ?>
</div>


<input type="hidden" name="task" value="" />
<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
<?php echo JHtml::_('form.token'); ?>

<!-- End Content -->

</fieldset>
</div>
</form>