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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// Create shortcut to parameters.
$app = Factory::getApplication();
$input = $app->input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        "
	Joomla.submitbutton = function (task, type) {
		if (task == 'cwmserver.setType') {
			document.getElementById('item-form').elements['jform[type]'].value = type;
			Joomla.submitform(task, document.getElementById('item-form'));
		} else if (task == 'cwmserver.cancel') {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else if (task == 'cwmserver.apply' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
		}
	}"
    );
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmserver&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form"
      aria-label="<?php
        echo Text::_('JBS_CMN_' . ((int)$this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
      class="form-validate" enctype="multipart/form-data">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-9">
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('server_name'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('server_name'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('type'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('type'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <?php
                echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                <?php
                if (isset($this->item->id, $this->item->addon)) : ?>
                    <span style="font-weight:bold">
                        <?php
                        echo $this->escape($this->item->addon->name); ?>
                    </span>
                    <?php
                endif; ?>
                <?php
                if (isset($this->item->id, $this->item->addon)) : ?>
                    <p><?php
                        echo $this->escape($this->item->addon->description); ?></p>
                    <?php
                endif; ?>
                <?php
                echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>
        <?php
        if ($this->server_form !== "no-data-type") : ?>
            <?php
            if ($this->server_form->getFieldsets('params')) : ?>
                <?php
                foreach ($this->server_form->getFieldsets('params') as $fieldsets) : ?>
                    <?php
                    echo HTMLHelper::_(
                        'uitab.addTab',
                        'myTab',
                        strtolower(Text::_($fieldsets->label)),
                        Text::_($fieldsets->label)
                    ); ?>
                    <div class="row">
                        <div class="col-12 col-lg-12">
                            <?php
                            foreach ($this->server_form->getFieldset($fieldsets->name) as $field) : ?>
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
                    </div>
                    <?php
                    echo HTMLHelper::_('uitab.endTab'); ?>
                    <?php
                endforeach; ?>
                <?php
            endif; ?>
            <?php
            if ($this->server_form->getFieldsets('media')) : ?>
                <?php
                echo HTMLHelper::_('uitab.addTab', 'myTab', 'media_settings', Text::_('JBS_SVR_MEDIA_SETTINGS')); ?>
                <div class="row">
                    <div class="accordion" id="accordionlist">
                        <?php
                        $test = $this->server_form->getFieldsets('media');
                        foreach ($this->server_form->getFieldsets('media') as $name => $fieldset) : ?>
                            <div class="accordion-item">
                                <h2 class="accordion-heading" id="<?php
                                echo Text::_($name) ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?php
                                            echo Text::_($name) ?>" aria-expanded="false"
                                            aria-controls="collapse<?php
                                            echo Text::_($name) ?>">
                                        <?php
                                        echo Text::_($fieldset->label); ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php
                                echo Text::_($name) ?>" class="accordion-collapse collapse"
                                     aria-labelledby="heading<?php
                                        echo $name; ?>"
                                     data-bs-parent="#accordionlist">
                                    <div class="accordion-body">
                                        <?php
                                        foreach ($this->server_form->getFieldset($name) as $field) : ?>
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
                                </div>
                            </div>
                            <?php
                        endforeach; ?>
                    </div>
                </div>
                <?php
                echo HTMLHelper::_('uitab.endTab'); ?>
                <?php
            endif; ?>
            <?php
        endif; ?>
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
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getBase64('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
