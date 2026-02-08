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
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

// Create shortcut to parameters.
/** @type Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

// Add lazy loading script and pass CSRF token for AJAX calls
$wa->useScript('com_proclaim.template-lazyload');
$this->getDocument()->addScriptOptions('csrf.token', \Joomla\CMS\Session\Session::getFormToken());

// Add layout editor assets
$wa->useScript('bootstrap.modal')
    ->useScript('com_proclaim.sortable')
    ->useScript('com_proclaim.layout-editor')
    ->useStyle('com_proclaim.layout-editor');
?>

<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('text'); ?>
            </div>
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo Text::_('JDETAILS'); ?></h5>
                        <?php echo $this->form->renderField('published'); ?>
                        <?php echo $this->form->renderField('id'); ?>
                    </div>
                </div>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col-12">
                <h4><?php echo Text::_('JBS_TPL_TEMPLATES'); ?></h4>
            </div>
            <div class="col-lg-6">
                <?php
                $fields = $this->form->getFieldset('TEMPLATES');
$fieldArray             = iterator_to_array($fields);
$half                   = (int) ceil(\count($fieldArray) / 2);
$i                      = 0;
foreach ($fieldArray as $field) :
    if ($i === $half) {
        echo '</div><div class="col-lg-6">';
    }
    echo $this->form->renderField($field->fieldname, 'params');
    $i++;
endforeach;
?>
            </div>
        </div>

        <hr />

        <div class="row">
            <div class="col-12">
                <h4><?php echo Text::_('JBS_CMN_TERMS_SETTINGS'); ?></h4>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label for="jform_params_terms" class="form-label mb-0 fw-bold">
                        <?php echo Text::_('JBS_CMN_TERMS'); ?>
                    </label>
                    <div class="d-flex align-items-center gap-2">
                        <label for="jform_params_useterms" class="form-label mb-0 small text-muted">
                            <?php echo Text::_('JBS_CMN_USE_TERMS'); ?>
                        </label>
                        <?php echo $this->form->getInput('useterms', 'params'); ?>
                    </div>
                </div>
                <?php echo $this->form->getInput('terms', 'params'); ?>
                <div class="form-text small text-muted"><?php echo Text::_('JBS_CMN_TERMS_DESC'); ?></div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'layout', Text::_('JBS_TPL_LAYOUT_EDITOR')); ?>
        <div class="row">
            <div class="col-12">
                <!-- Layout Editor loads via AJAX when tab is shown for faster initial page load -->
                <div id="layout-editor-ajax-container"
                     data-load-url="<?php echo Route::_('index.php?option=com_proclaim&task=cwmtemplate.loadLayoutEditor&format=raw&id=' . (int) $this->item->id); ?>">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
                        </div>
                        <p class="mt-3 text-muted"><?php echo Text::_('JBS_TPL_LAYOUT_LOADING'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'media', Text::_('JBS_CMN_MEDIA')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php
$fields     = $this->form->getFieldset('MEDIA');
$fieldArray = iterator_to_array($fields);
$half       = (int) ceil(\count($fieldArray) / 2);
$i          = 0;
foreach ($fieldArray as $field) :
    if ($i === $half) {
        echo '</div><div class="col-lg-6">';
    }
    echo $this->form->renderField($field->fieldname, 'params');
    $i++;
endforeach;
?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <?php
echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
