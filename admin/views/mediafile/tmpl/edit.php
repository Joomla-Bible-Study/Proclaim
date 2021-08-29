<?php
/**
 * Edit
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

// Set up defaults
if (JFactory::getApplication()->input->getInt('id'))
{
	$study_id   = $this->item->study_id;
	$createdate = $this->item->createdate;
	$podcast_id = $this->item->podcast_id;
}
else
{
	$study_id   = $this->options->study_id;
	$createdate = $this->options->createdate;
	$podcast_id = $this->admin_params->get('podcast');
}

$new = ($this->item->id === '0' || empty($this->item->id));

/** @type BiblestudyViewMediafile $this */

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function (task, server_id) {
		if (task == "mediafile.setServer") {
			document.id("media-form").elements["jform[server_id]"].value = server_id;
			Joomla.submitform(task, document.id("media-form"));
		} else if (task == "mediafile.cancel"|| document.formvalidator.isValid(document.id("media-form"))) {
			Joomla.submitform(task, document.getElementById("media-form"));
		} else {
			alert("' . $this->escape(JText::_("JGLOBAL_VALIDATION_FORM_FAILED")) . '");
		}
	}
');
?>
<form action="<?php echo 'index.php?option=com_biblestudy&view=mediafile&layout=edit&id=' . (int) $this->item->id; ?>"
      method="post"
      name="adminForm"
      id="media-form"
      class="form-validate">
    <div class="form-horizontal">
        <div class="form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

            <!-- Begin Content -->
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('JBS_CMN_GENERAL')); ?>
            <div class="row-fluid">
                <div class="span9">
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('study_id'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('study_id', null, $study_id); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('createdate'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('createdate', null, $createdate); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('server_id'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('server_id', null, $this->item->server_id); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('podcast_id'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('podcast_id', null, $podcast_id); ?>
                        </div>
                    </div>

                    <?php echo $this->addon->renderGeneral($this->media_form, $new); ?>

                </div>
                <div class="span3 form-vertical">
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
							<?php echo $this->form->getLabel('language'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('language'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('comment'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('comment'); ?>
                        </div>
                    </div>
                </div>
            </div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo $this->addon->render($this->media_form, $new); ?>

			<?php if ($this->canDo->get('core.admin')): ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
                <div class="row-fluid">
					<?php echo $this->form->getInput('rules'); ?>
                </div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<?php // Load the batch processing form. ?>
			<?php echo JHtml::_(
				'bootstrap.renderModal',
				'collapseModal',
				array(
					'title'  => JText::_('JBS_CMN_BATCH_OPTIONS'),
					'footer' => $this->loadTemplate('converter_footer')
				),
				$this->loadTemplate('converter_body')
			); ?>
        </div>
    </div>
	<?php echo $this->form->getInput('asset_id'); ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
