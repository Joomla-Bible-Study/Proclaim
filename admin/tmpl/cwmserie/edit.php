<?php

/**
 * Part of Proclaim Package
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
use Joomla\CMS\Router\Route;

$app   = Factory::getApplication();
$input = $app->getInput();

// Set up defaults — use original image path, not thumbnail
if ($input->getInt('id')) {
    $imageDefault = !empty($this->item->image) ? $this->item->image : ($this->item->series_thumbnail ?? '');
} else {
    $imageDefault = $this->admin_params->get('default_series_image', '');
}

/** @var CWM\Component\Proclaim\Administrator\View\Cwmserie\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
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
                <?php echo $this->form->renderField('description'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('teacher'); ?>
                <?php echo $this->form->renderField('landing_show'); ?>
                <?php echo $this->form->renderField('pc_show'); ?>
                <?php echo $this->form->renderField('image', null, $imageDefault); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

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
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
            <fieldset id="fieldset-rules" class="options-form">
                <div>
                <?php
                echo $this->form->getInput('rules'); ?>
                </div>
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
