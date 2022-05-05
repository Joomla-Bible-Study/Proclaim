<?php
/**
 * Edit
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HtmlHelper::_('behavior.tabstate');
HtmlHelper::_('behavior.keepalive');
HtmlHelper::_('behavior.formvalidator');
HtmlHelper::_('formbehavior.chosen', 'select');

// Set up defaults
if (Factory::getApplication()->input->getInt('id'))
{
	$study_id   = $this->item->study_id;
	$createdate = $this->item->createdate;
	$podcast_id = $this->item->podcast_id;
}
else
{
	$study_id   = $this->options->study_id;
	$createdate = $this->options->createdate;
	$podcast_id = $this->params->get('podcast');
}

$new = ($this->item->id == '0' || $this->item->id == false);


Factory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function (task, server_id) {
		if (task == 'mediafileform.setServer') {
			document.id('media-form').elements['jform[server_id]'].value = server_id;
			Joomla.submitform(task, document.id('media-form'));
		} else if (task == 'mediafileform.cancel') {
			Joomla.submitform(task, document.getElementById('media-form'));
		} else if (task == 'mediafileform.apply' || document.formvalidator.isValid(document.id('media-form'))) {
			Joomla.submitform(task, document.getElementById('media-form'));
		} else {
			alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
		}
	};
");
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
        </div>
	<?php endif; ?>
    <form action="<?php echo 'index.php?option=com_proclaim&view=mediafileform&layout=edit&id=' . (int) $this->item->id; ?>"
          method="post"
          name="adminForm"
          id="media-form"
          class="form-validate">
        <div class="form-horizontal">
            <div class="form-horizontal">
				<?php echo HtmlHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

                <!-- Begin Content -->
				<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
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
				<?php echo HtmlHelper::_('bootstrap.endTab'); ?>

				<?php echo $this->addon->render($this->media_form, $new); ?>

				<?php if ($this->canDo->get('core.administrator')): ?>
					<?php echo HtmlHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
                    <div class="row-fluid">
						<?php echo $this->form->getInput('rules'); ?>
                    </div>
					<?php echo HtmlHelper::_('bootstrap.endTab'); ?>
				<?php endif; ?>

				<?php echo HtmlHelper::_('bootstrap.endTabSet'); ?>

				<?php // Load the batch processing form. ?>
				<?php echo HtmlHelper::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('converter_footer')
					),
					$this->loadTemplate('converter_body')
				); ?>
            </div>
        </div>
		<?php echo $this->form->getInput('asset_id'); ?>
		<?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return"
               value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
		<?php echo HtmlHelper::_('form.token'); ?>
    </form>
</div>
