<?php

/**
 * Form
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
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task) {
		if (task == "cwmlocation.cancel" || document.formvalidator.isValid(document.id("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"))
		}
		else
		{
			alert(' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . ')
		}
	}
'
    );
// Create shortcut to parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;
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
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('location_text'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('location_text'); ?>
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

            </div>
            <div class="col-lg-3">
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
        echo HTMLHelper::_('uitab.endTabSet'); ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getCmd('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
