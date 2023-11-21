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
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_proclaim');
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.submitbutton = function (task) {
		if (task == "cwmcomment.cancel" || document.formvalidator.isValid(document.getElementById("message-form")))
		{
			Joomla.submitform(task, document.getElementById("message-form"));
		}
		else
		{
			alert(' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . ')
		}
	}
'
    );

$app   = Factory::getApplication();
$input = $app->input;
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
                        echo $this->form->getLabel('comment_text'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('comment_text'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('study_id'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('study_id'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('comment_date'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('comment_date'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('full_name'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('full_name'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('user_email'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('user_email'); ?>
                    </div>
                </div>
            </div>

            <!-- Begin Sidebar -->
            <div class="col-lg-3">
                <h4><?php
                    echo Text::_('JDETAILS'); ?></h4>
                <hr/>
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
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
            <div class="row">
                <fieldset>
                    <?php
                    echo $this->form->getInput('rules'); ?>
                </fieldset>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getCmd('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
