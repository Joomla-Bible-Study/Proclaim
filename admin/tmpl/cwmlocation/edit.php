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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmlocation\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmlocation.cancel', 'formId' => 'item-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit');
// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->getInput();

$currentLayout = $input->get('layout', 'edit');
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=' . $currentLayout . '&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('location_text'); ?>
                <?php echo $this->form->renderField('landing_show'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo LayoutHelper::render('edit.publish_tab', $this); ?>

        <?php echo LayoutHelper::render('edit.permissions_tab', ['form' => $this->form, 'canDo' => $this->canDo, 'tabName' => 'myTab']); ?>

        <?php
        echo HTMLHelper::_('uitab.endTabSet'); ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getCmd('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
