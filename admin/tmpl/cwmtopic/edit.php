<?php

/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtopic\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$wa = $this->getDocument()->getWebAssetManager();
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmtopic.cancel', 'formId' => 'item-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit')
    ->addInlineScript(
        'document.addEventListener("DOMContentLoaded", function() {
		const translated = document.getElementById("topic_text_translated");
		if (translated && translated.value !== "") {
			const target = document.getElementById("jform_topic_text");
			if (target) {
				target.setAttribute("readonly", "readonly");
			}
		}
	});'
    );

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$input  = Factory::getApplication()->getInput();
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php
                $string = $this->form->getValue('topic_text');
if (str_starts_with($string, 'JBS')) { ?>
                    <div class="mb-3">
                        <label id="topic_text-lbl" for="topic_text" class="form-label">Translated:</label>
                        <?php
                        echo '<input type="text" id="topic_text_translated" name="topic_text_translated" value="'
                            . Text::_($string) . '" class="form-control" readonly data-alt-value="'
                            . Text::_($string) . '" autocomplete="off" aria-invalid="false">'; ?>
                    </div>
                    <?php
} ?>
                <?php echo $this->form->renderField('topic_text'); ?>
                <?php foreach ($this->form->getFieldset('params') as $field) :
                    echo $field->renderField();
                endforeach; ?>
            </div>

            <div class="col-lg-3">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo LayoutHelper::render('edit.publish_tab', $this); ?>

        <?php echo LayoutHelper::render('edit.permissions_tab', ['form' => $this->form, 'canDo' => $this->canDo, 'tabName' => 'myTab']); ?>
        <?php // id now rendered as a read-only field by the shared publish_tab layout.?>
        <?php
        echo $this->form->getInput('asset_id'); ?>
        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>