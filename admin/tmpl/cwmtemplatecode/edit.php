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

$app   = Factory::getApplication();
$input = $app->input;

// Set up defaults
if ($input->getInt('a_id')) {
    $templatecode = $this->item->templatecode;
} else {
    $templatecode = $this->defaultcode;
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task)
	{
		if (task == "cwmtemplatecode.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
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
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <!-- Begin Content -->
        <div class="row">
            <div class="col-lg-9 form-horizontal">
                <!-- Begin Tabs -->
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('filename'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('filename'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('type'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        if ($this->item->id == 0) {
                            echo $this->form->getInput('type');
                        } else {
                            ?><label id="jform_type-lbl" for="jform_type"
                                     style="clear: both;"><?php
                                        echo $this->type ?></label>
                            <?php
                        } ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('templatecode'); ?>
                    </div>
                    <div class="clr"></div>
                    <hr/>
                    <div class="editor-border">
                        <?php
                        echo $this->form->getInput('templatecode', null, $templatecode); ?>
                    </div>
                </div>
            </div>

            <!-- Begin Sidebar -->
            <div class="col-lg-2 form-vertical">
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
            <!-- End Sidebar -->
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
