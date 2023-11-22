<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
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
use Joomla\CMS\Router\Route;

$app   = Factory::getApplication();
$input = $app->input;

// Set up defaults
if ($input->getInt('id')) {
    $series_thumbnail = $this->item->series_thumbnail;
} else {
    $series_thumbnail = $this->admin_params->get('default_series_image');
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task)
	{
		if (task == "cwmserie.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
'
    );
?>

<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <?php
    echo LayoutHelper::render('edit.seriestitle_alias', $this); ?>

    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <div>
                    <fieldset class="adminform">
                        <?php
                        echo $this->form->getLabel('description'); ?>
                        <?php
                        echo $this->form->getInput('description'); ?>
                    </fieldset>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('teacher'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('teacher'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('landing_show'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('landing_show'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('pc_show'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('pc_show'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('image'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('image', null, $series_thumbnail); ?>
                    </div>
                </div>
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
                        echo $this->form->getLabel('access'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('access'); ?>
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
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
            <fieldset>
                <?php
                echo $this->form->getInput('rules'); ?>
            </fieldset>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getBase64('return'); ?>">
        <input type="hidden" name="forcedLanguage" value="<?php
        echo $input->get('forcedLanguage', '', 'cmd'); ?>">
        <?php
        echo $this->form->getInput('series_thumbnail'); ?>
        <?php
        echo $this->form->getInput('id'); ?>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
