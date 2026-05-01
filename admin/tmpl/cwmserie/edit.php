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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

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
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmserie.cancel', 'formId' => 'item-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit');
?>

<?php $currentLayout = Factory::getApplication()->getInput()->get('layout', 'edit'); ?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=' . $currentLayout . '&id=' . (int)$this->item->id); ?>"
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
                <?php echo $this->form->renderField('location_id'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo LayoutHelper::render('edit.publish_tab', $this); ?>

        <?php if (!empty($this->item->id) && $this->item->id > 0) : ?>
        <?php
        $msgCount = \count($this->messages);
        echo HTMLHelper::_(
            'uitab.addTab',
            'myTab',
            'messages',
            Text::sprintf('JBS_SER_MESSAGES_COUNT', $msgCount)
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
                            <th class="w-15"><?php echo Text::_('JBS_CMN_TEACHER'); ?></th>
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
                                <?php echo $this->escape($msg->teachername ?? ''); ?>
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
                       href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessages&filter[series]=' . (int) $this->item->id); ?>">
                        <?php echo Text::_('JBS_SER_VIEW_ALL_MESSAGES'); ?>
                    </a>
                </div>
                <?php else : ?>
                <div class="alert alert-info">
                    <?php echo Text::_('JBS_SER_NO_MESSAGES_IN_SERIES'); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php // ===== Schema.org Tab =====?>
        <?php if ($this->form->getFieldset('schema')) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'schema', Text::_('JBS_CMN_SCHEMAORG_TAB')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php foreach ($this->form->getFieldset('schema') as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
                <?php if (!empty($this->item->id)) : ?>
                <div class="mt-3">
                    <a href="<?php echo Route::_(
                        'index.php?option=com_proclaim&task=cwmadmin.schemaForceRefresh'
                        . '&item_id=' . (int) $this->item->id
                        . '&schema_context=com_proclaim.serie'
                        . '&return=' . base64_encode(Uri::getInstance()->toString())
                        . '&' . Session::getFormToken() . '=1'
                    ); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="icon-refresh me-1" aria-hidden="true"></i>
                        <?php echo Text::_('JBS_CMN_SCHEMA_RESET'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo LayoutHelper::render('edit.permissions_tab', ['form' => $this->form, 'canDo' => $this->canDo, 'tabName' => 'myTab']); ?>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getBase64('return'); ?>">
        <input type="hidden" name="forcedLanguage" value="<?php
        echo $input->get('forcedLanguage', '', 'cmd'); ?>">
        <?php
        echo $this->form->getInput('series_thumbnail'); ?>
        <?php // id now rendered as a read-only field by the shared publish_tab layout.?>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
