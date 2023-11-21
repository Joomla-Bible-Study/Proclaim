<?php

/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task) {
		if (task == "cwmtopic.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"))
		}
		else
		{
			alert("' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '")
		}
	}
'
    );

// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$input  = new Input();
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
                    <div class="control-group">
                        <div class="control-label">
                            <label id="topic_text-lbl" for="topic_text">Translated:</label>
                        </div>
                        <div class="controls">
                            <?php
                            echo '<input type="text" name="topic_text" id="topic_text" value="' . Text::_($string) .
                                '" class="readonly form-control valid form-control-success" size="75" aria-describedby="topic_text-desc" readonly aria-invalid="false">';
                            echo "<br/>"; ?>
                        </div>
                    </div>
                    <?php
                } ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('topic_text'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('topic_text'); ?>
                    </div>
                </div>
                <?php
                foreach ($this->form->getFieldset('params') as $field) : ?>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $field->label; ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $field->input; ?>
                        </div>
                    </div>
                    <?php
                endforeach; ?>
            </div>

            <div class="col-lg-3">
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('published'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('published'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('language'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('language'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row-fluid">
                <?php
                echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>
        <?php
        echo $this->form->getInput('id'); ?>
        <?php
        echo $this->form->getInput('asset_id'); ?>
        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>