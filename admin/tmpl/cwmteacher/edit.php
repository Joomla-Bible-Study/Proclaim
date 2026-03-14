<?php

/**
 * Teacher edit form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmteacher\HtmlView $this */

$app   = Factory::getApplication();
$input = $app->getInput();

// Set up defaults — use original image path, not thumbnail
if ($input->getInt('id')) {
    $imageDefault = !empty($this->item->image) ? $this->item->image : ($this->item->teacher_thumbnail ?? '');
} else {
    $imageDefault = $this->admin_params->get('default_teacher_image', '');
}

$wa = $this->getDocument()->getWebAssetManager();
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmteacher.cancel', 'formId' => 'teacher-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit')
    ->useScript('com_proclaim.phone-input')
    ->useStyle('com_proclaim.intl-tel-input-css')
    ->useStyle('com_proclaim.phone-input-css');

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="teacher-form" aria-label="<?php echo Text::_('JBS_CMN_TEACHER'); ?>"
      class="form-validate" enctype="multipart/form-data">

    <?php echo LayoutHelper::render('edit.teachertitle_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php // ===== Details Tab =====?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('phone'); ?>
                <?php echo $this->form->renderField('email'); ?>
                <?php echo $this->form->renderField('address'); ?>
                <?php echo $this->form->renderField('contact'); ?>
                <?php if ($this->form->getValue('contact')) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_contact&task=contact.edit&id=' . (int) $this->form->getValue('contact')); ?>"
                       target="_blank" class="btn btn-sm btn-secondary mb-3">
                        <?php echo Text::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('image', null, $imageDefault); ?>
                <hr/>
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('list_show'); ?>
                <?php echo $this->form->renderField('landing_show'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // ===== Biography Tab =====?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'biography', Text::_('JBS_TCH_BIOGRAPHY')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->form->renderField('short'); ?>
                <?php echo $this->form->renderField('information'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // ===== Links Tab =====?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'links', Text::_('JBS_TCH_LINKS')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php // ---- Website Field ----?>
                <?php echo $this->form->renderField('website'); ?>

                <?php // ---- Social Links Subform ----?>
                <div class="mt-4">
                    <h4 class="mb-2">
                        <i class="fa-solid fa-share-nodes"></i>
                        <?php echo Text::_('JBS_TCH_SOCIAL_LINKS'); ?>
                    </h4>
                    <p class="text-muted small mb-3"><?php echo Text::_('JBS_TCH_SOCIAL_LINKS_DESC'); ?></p>
                    <?php echo $this->form->renderField('social_links'); ?>
                </div>

                <?php // ---- Legacy Links (if any exist) ----?>
                <?php
                $hasLegacyLinks = !empty($this->item->facebooklink)
                    || !empty($this->item->twitterlink)
                    || !empty($this->item->bloglink)
                    || !empty($this->item->link1)
                    || !empty($this->item->link2)
                    || !empty($this->item->link3);
?>
                <?php if ($hasLegacyLinks) : ?>
                <div class="accordion mt-5" id="legacyLinksAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="legacyLinksHeading">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#legacyLinksCollapse"
                                    aria-expanded="false" aria-controls="legacyLinksCollapse">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i>
                                <?php echo Text::_('JBS_TCH_LEGACY_LINKS'); ?>
                            </button>
                        </h2>
                        <div id="legacyLinksCollapse" class="accordion-collapse collapse"
                             aria-labelledby="legacyLinksHeading" data-bs-parent="#legacyLinksAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($this->item->facebooklink)) : ?>
                                    <?php echo $this->form->renderField('facebooklink'); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->item->twitterlink)) : ?>
                                    <?php echo $this->form->renderField('twitterlink'); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->item->bloglink)) : ?>
                                    <?php echo $this->form->renderField('bloglink'); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->item->link1)) : ?>
                                    <?php echo $this->form->renderField('link1'); ?>
                                    <?php echo $this->form->renderField('linklabel1'); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->item->link2)) : ?>
                                    <?php echo $this->form->renderField('link2'); ?>
                                    <?php echo $this->form->renderField('linklabel2'); ?>
                                <?php endif; ?>
                                <?php if (!empty($this->item->link3)) : ?>
                                    <?php echo $this->form->renderField('link3'); ?>
                                    <?php echo $this->form->renderField('linklabel3'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // ===== Messages Tab (existing records only) =====?>
        <?php if (!empty($this->item->id) && $this->item->id > 0) : ?>
        <?php
        $msgCount = \count($this->messages);
            echo HTMLHelper::_(
                'uitab.addTab',
                'myTab',
                'messages',
                Text::sprintf('JBS_TCH_MESSAGES_COUNT', $msgCount)
            ); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php if ($msgCount > 0) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-5 text-center"><?php echo Text::_('JSTATUS'); ?></th>
                            <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
                            <th class="w-15"><?php echo Text::_('JBS_CMN_DATE'); ?></th>
                            <th class="w-15"><?php echo Text::_('JBS_CMN_SERIES'); ?></th>
                            <th class="w-15"><?php echo Text::_('JBS_CMN_LOCATION'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->messages as $i => $msg) : ?>
                        <tr>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $msg->published, $i, '', false); ?>
                            </td>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int) $msg->id); ?>">
                                    <?php echo $this->escape($msg->studytitle); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo HTMLHelper::_('date', $msg->studydate, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td>
                                <?php echo $this->escape($msg->series_text ?? ''); ?>
                            </td>
                            <td>
                                <?php echo $this->escape($msg->location_text ?? ''); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="mt-2">
                    <a class="btn btn-secondary btn-sm"
                       href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessages&filter[teacher]=' . (int) $this->item->id); ?>">
                        <?php echo Text::_('JBS_TCH_VIEW_ALL_MESSAGES'); ?>
                    </a>
                </div>
                <?php else : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('JBS_TCH_NO_MESSAGES'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php // ===== Publishing Tab =====?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publish', Text::_('JBS_STY_PUBLISH')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php // ===== Permissions Tab =====?>
        <?php if ($this->canDo->get('core.admin')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
        <fieldset id="fieldset-rules" class="options-form">
            <div>
                <?php echo $this->form->getInput('rules'); ?>
            </div>
        </fieldset>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>"/>
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>"/>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo $this->form->getInput('teacher_image'); ?>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
