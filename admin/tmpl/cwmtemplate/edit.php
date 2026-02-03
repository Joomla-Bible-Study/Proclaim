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

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

// Create shortcut to parameters.
/** @type \Joomla\Registry\Registry $params */
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
            <div class="col-lg-9 form-horizontal">
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('title'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('title'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('text'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('text'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 form-vertical">
                <h4><?php
                    echo Text::_('JDETAILS'); ?></h4>
                <hr/>
                <div class="control-group">
                    <div class="controls">
                        <?php
                        echo $this->form->getValue('title'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('id'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('id'); ?>
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
            </div>
        </div>
        <hr/>
        <div class="row">
            <?php
            $c     = 0;
$count             = CwmproclaimHelper::halfarray($this->form->getFieldset('TEMPLATES'));
foreach ($this->form->getFieldset('TEMPLATES') as $field) :
    if ($c === 0) {
        echo '<div class="col-12 col-lg-6">';
    } elseif ($c === (int)$count->half) {
        echo '</div><div class="col-12 col-lg-6">';
    }
    ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php
            echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php
            echo $field->input; ?>
                        <br /> <?php echo Text::_($field->description); ?>
                    </div>
                </div>
                <?php
    $c++;
    if ($c === (int)$count->count) {
        echo '</div>';
    }
endforeach; ?>
        </div>
        <hr/>
        <div class="col-12 col-lg-12">
            <?php
foreach ($this->form->getFieldset('TERMS') as $field) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php
            echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php
            echo $field->input; ?>
                        <br /> <?php echo Text::_($field->description); ?>
                    </div>
                </div>
                <?php
endforeach; ?>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'layout', Text::_('JBS_TPL_LAYOUT_EDITOR')); ?>
        <div class="row">
            <div class="col-12">
                <?php include __DIR__ . '/edit_layout.php'; ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'media', Text::_('JBS_CMN_MEDIA')); ?>
        <div class="row">
            <?php
$c     = 0;
$count = CwmproclaimHelper::halfarray($this->form->getFieldset('MEDIA'));
foreach ($this->form->getFieldset('MEDIA') as $field) :
    if ($c === 0) {
        echo '<div class="col-12 col-lg-6">';
    } elseif ($c === (int)$count->half) {
        echo '</div><div class="col-12 col-lg-6">';
    }
    ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php
            echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php
            echo $field->input; ?>
                        <br /> <?php echo Text::_($field->description); ?>
                    </div>
                </div>
                <?php
    (int)$c++;
    if ($c === (int)$count->count) {
        echo '</div>';
    }
endforeach; ?>
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

        <input type="hidden" name="task" value=""/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
