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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmcomment\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
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
$input = $app->getInput();
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
                <?php echo $this->form->renderField('comment_text'); ?>
                <?php echo $this->form->renderField('study_id'); ?>
                <?php echo $this->form->renderField('comment_date'); ?>
                <?php echo $this->form->renderField('full_name'); ?>
                <?php echo $this->form->renderField('user_email'); ?>
            </div>

            <!-- Begin Sidebar -->
            <div class="col-lg-3">
                <h4><?php echo Text::_('JDETAILS'); ?></h4>
                <hr/>
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
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
