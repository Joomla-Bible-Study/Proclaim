<?php

/**
 * Edit
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmediafile\HtmlView $this */

$input = Factory::getApplication()->getInput();

// Set up defaults
if ($input->getInt('id')) {
    $study_id   = $this->item->study_id;
    $createdate = $this->item->createdate;
    $podcast_id = $this->item->podcast_id;
} else {
    $study_id   = $this->options->study_id;
    $createdate = $this->options->createdate;
    $podcast_id = $this->admin_params->get('podcast');
}

$new = ($this->item->id === '0' || empty($this->item->id));

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task) {
		if (task == "cwmmediafile.setServer") {
			Joomla.submitform(task, document.getElementById("adminForm"));
		} else if (task == "cwmmediafile.cancel"|| document.formvalidator.isValid(document.getElementById("adminForm"))) {
			Joomla.submitform(task, document.getElementById("adminForm"));
		} else {
			alert("' . $this->escape(Text::_("JGLOBAL_VALIDATION_FORM_FAILED")) . '");
		}
	}

	document.addEventListener("DOMContentLoaded", function() {
		var serverField = document.getElementById("jform_server_id");
		if (serverField) {
			var serverTypes = {};
			try { serverTypes = JSON.parse(serverField.dataset.serverTypes || "{}"); } catch(e) {}
			var previousValue = serverField.value;

			serverField.addEventListener("change", function() {
				var newValue = this.value;

				// No previous selection — just load the addon
				if (!previousValue || !serverTypes[previousValue]) {
					previousValue = newValue;
					Joomla.submitbutton("cwmmediafile.setServer");
					return;
				}

				var oldType = serverTypes[previousValue] || "";
				var newType = serverTypes[newValue] || "";

				if (oldType !== newType && newType !== "") {
					if (!confirm("' . $this->escape(Text::_("JBS_MED_SERVER_TYPE_CHANGE_WARNING")) . '")) {
						this.value = previousValue;
						return;
					}
				}

				previousValue = newValue;
				Joomla.submitbutton("cwmmediafile.setServer");
			});
		}
	});
'
    );

$this->useCoreUI = true;
?>
<form action="<?php
echo 'index.php?option=com_proclaim&view=cwmmediafile&layout=edit&id=' . (int)$this->item->id; ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate">
    <div>
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <!-- Begin Content -->
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-7">
                <?php echo $this->form->renderField('study_id', null, $study_id); ?>
                <?php echo $this->form->renderField('createdate', null, $createdate); ?>
                <?php echo $this->form->renderField('server_id', null, $this->item->server_id); ?>
                <?php echo $this->form->renderField('podcast_id', null, $podcast_id); ?>

                <?php if ($this->addon !== null) : ?>
                    <?php echo $this->addon->renderGeneral($this->media_form, $new); ?>
                <?php else : ?>
                    <div class="alert alert-info">
                        <?php echo Text::_('JBS_MED_SELECT_SERVER_FIRST'); ?>
                    </div>
                <?php endif; ?>

            </div>
            <div class="col-lg-5">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo $this->form->renderField('comment'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($this->addon !== null) : ?>
        <?php echo $this->addon->render($this->media_form, $new); ?>
        <?php endif; ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'publish', Text::_('JBS_STY_PUBLISH')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php
                echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <?php echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <?php
        echo HTMLHelper::_('uitab.endTabSet'); ?>

        <?php
        // Load the batch processing form.?>
        <?php
        echo HTMLHelper::_(
            'bootstrap.renderModal',
            'collapseModal',
            [
                'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
                'footer' => $this->loadTemplate('converter_footer'),
            ],
            $this->loadTemplate('converter_body')
        ); ?>
    </div>
    <?php
    echo $this->form->getInput('asset_id'); ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php
    echo $input->getBase64('return'); ?>"/>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
