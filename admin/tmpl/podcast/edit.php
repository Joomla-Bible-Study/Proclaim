<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 - 2012 CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task == 'podcast.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
            Joomla.submitform(task, document.getElementById('item-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
<div class="row-fluid">
<!-- Begin Content -->
<div class="span9 form-horizontal">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('JBS_CMN_DETAILS'); ?></a></li>
        <li><a href="#images" data-toggle="tab"><?php echo JText::_('JBS_PDC_PODCAST_IMAGES'); ?></a></li>

		<?php if ($this->canDo->get('core.administrator')): ?>
        <li><a href="#permissions" data-toggle="tab"><?php echo JText::_('JBS_CMN_FIELDSET_RULES'); ?></a></li>
		<?php endif ?>
    </ul>
    <div class="tab-content">
        <!-- Begin Tabs -->
        <div class="tab-pane active" id="general">
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
					<?php echo $this->form->getLabel('description'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('description'); ?>
                </div>
            </div>
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
			        <?php echo $this->form->getLabel('podcastlink'); ?>
		        </div>
		        <div class="controls">
			        <?php echo $this->form->getInput('podcastlink'); ?>
		        </div>
	        </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('author'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('author'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('editor_name'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('editor_name'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('editor_email'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('editor_email'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('podcastsearch'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcastsearch'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('podcastlanguage'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcastlanguage'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('detailstemplateid'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('detailstemplateid'); ?>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="images">
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
					<?php echo $this->form->getLabel('podcastimage'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcastimage'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('podcast_image_subscribe'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcast_image_subscribe'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('podcast_subscribe_desc'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcast_subscribe_desc'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('linktype'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('linktype'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('alternatelink'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('alternatelink'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('alternateimage'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('alternateimage'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('podcast_subscribe_show'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('podcast_subscribe_show'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('alternatewords'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('alternatewords'); ?>
                </div>
            </div>
        </div>

		<?php if ($this->canDo->get('core.administrator')): ?>
        <div class="tab-pane" id="permissions">

			<?php echo $this->form->getInput('rules'); ?>

        </div>
		<?php endif; ?>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
    <!-- End Content -->
</div>
<!-- Begin Sidebar -->
<div class="span3 form-vertical">
    <h4><?php echo JText::_('JDETAILS');?></h4>
    <hr/>
    <div class="tab-pane" id="publishing">
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
				<?php echo $this->form->getLabel('filename'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('filename'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('podcastlimit'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('podcastlimit'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('episodetitle'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('episodetitle'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('custom'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('custom'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('episodesubtitle'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('episodesubtitle'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('customsubtitle'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('customsubtitle'); ?>
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
<!-- End Sidebar -->
</div>
</form>
