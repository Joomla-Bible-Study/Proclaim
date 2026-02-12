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
use Joomla\CMS\Uri\Uri;

$app   = Factory::getApplication();
$input = $app->getInput();

// Set up defaults
if ($input->getInt('id')) {
    $teacher_thumbnail = $this->item->teacher_thumbnail;
} else {
    $teacher_thumbnail = $this->admin->params->get('default_teacher_image');
}

/** @var CWM\Component\Proclaim\Administrator\View\Cwmteacher\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        "Joomla.submitbutton = function (task) {
			if (task === 'cwmteacher.cancel' || document.formvalidator.isValid(document.getElementById('teacher-form')))
			{
				Joomla.submitform(task, document.getElementById('teacher-form'))
			}
			else
			{
				alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "')
			}
		}

		function jInsertFieldValue (value, id)
		{
			var old_id = document.id(id).value
			if (old_id !== id)
			{
				var elem = document.id(id)
				elem.value = value
				elem.fireEvent('change')
			}
		}
"
    );

$this->useCoreUI = true;

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=' . $layout . $tmpl . '&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="teacher-form" class="form-validate" enctype="multipart/form-data">
    <div class="row">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]
        ); ?>
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <!-- Begin Content -->
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('teachername'); ?>
                <?php echo $this->form->renderField('alias'); ?>
                <?php echo $this->form->renderField('contact'); ?>
                <?php if ($this->form->getValue('contact')) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_contact&task=contact.edit&id=' . (int)$this->form->getValue('contact')); ?>"
                       target="_blank" class="btn btn-sm btn-secondary mb-3">
                        <?php echo Text::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
                    </a>
                <?php endif; ?>
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('address'); ?>
                <?php echo $this->form->renderField('phone'); ?>
                <?php echo $this->form->renderField('email'); ?>
            </div>
            <div class="col-lg-3">
                <?php if ($this->item->teacher_thumbnail) : ?>
                    <img src="<?php echo Uri::root() . $this->item->teacher_thumbnail; ?>"
                         alt="<?php echo $this->form->getValue('teachername'); ?>"
                         class="img-thumbnail mb-3 d-block"/>
                <?php endif; ?>
                <?php echo $this->form->renderField('image'); ?>
                <hr/>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('list_show'); ?>
                <?php echo $this->form->renderField('landing_show'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'information', Text::_('JBS_TCH_INFO')); ?>
        <div class="row">
            <?php echo $this->form->renderField('short'); ?>
            <?php echo $this->form->renderField('information'); ?>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'links', Text::_('JBS_TCH_LINKS')); ?>
        <div class="row">
            <?php echo $this->form->renderField('website'); ?>
            <?php echo $this->form->renderField('facebooklink'); ?>
            <?php echo $this->form->renderField('twitterlink'); ?>
            <?php echo $this->form->renderField('bloglink'); ?>
            <?php echo $this->form->renderField('link1'); ?>
            <?php echo $this->form->renderField('linklabel1'); ?>
            <?php echo $this->form->renderField('link2'); ?>
            <?php echo $this->form->renderField('linklabel2'); ?>
            <?php echo $this->form->renderField('link3'); ?>
            <?php echo $this->form->renderField('linklabel3'); ?>
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
        if ($this->canDo->get('core.admin')): ?>
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
    </div>
    <?php
    echo $this->form->getInput('id'); ?>
    <?php
    echo $this->form->getInput('teacher_image'); ?>
    <input type="hidden" name="task" value=""/>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
