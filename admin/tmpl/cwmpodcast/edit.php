<?php

/**
 * Podcast Edit Form
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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmpodcast\HtmlView $this */

$app   = Factory::getApplication();
$input = $app->input;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        "Joomla.submitbutton = function (task) {
            if (task === 'cwmpodcast.cancel' || document.formvalidator.isValid(document.getElementById('podcast-form'))) {
                Joomla.submitform(task, document.getElementById('podcast-form'));
            } else {
                alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
            }
        }"
    );
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmpodcast&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="podcast-form"
      aria-label="<?php echo Text::_('JBS_CMN_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
      class="form-validate">

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_STY_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('title'); ?>
                <?php echo $this->form->renderField('description'); ?>
                <?php echo $this->form->renderField('subtitle'); ?>
                <?php echo $this->form->renderField('website'); ?>
                <?php echo $this->form->renderField('podcastlink'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo $this->form->renderField('detailstemplateid'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'author', Text::_('JBS_PDC_PODCAST_AUTHOR')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('author'); ?>
                <?php echo $this->form->renderField('editor_name'); ?>
                <?php echo $this->form->renderField('editor_email'); ?>
                <?php echo $this->form->renderField('podcastsearch'); ?>
                <?php echo $this->form->renderField('podcastlanguage'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('JBS_STY_IMAGES')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('image'); ?>
                <?php echo $this->form->renderField('podcastimage'); ?>
                <?php echo $this->form->renderField('podcast_image_subscribe'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'subscription', Text::_('JBS_PDC_PODCAST_SUBSCRIPTION')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('podcast_subscribe_show'); ?>
                <?php echo $this->form->renderField('podcast_subscribe_desc'); ?>
                <?php echo $this->form->renderField('alternatelink'); ?>
                <?php echo $this->form->renderField('alternateimage'); ?>
                <?php echo $this->form->renderField('alternatewords'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'episode', Text::_('JBS_PDC_EPISODE_OPTIONS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('filename'); ?>
                <?php echo $this->form->renderField('podcastlimit'); ?>
                <?php echo $this->form->renderField('linktype'); ?>
                <?php echo $this->form->renderField('episodetitle'); ?>
                <?php echo $this->form->renderField('custom'); ?>
                <?php echo $this->form->renderField('episodesubtitle'); ?>
                <?php echo $this->form->renderField('customsubtitle'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publish', Text::_('JBS_STY_PUBLISH')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($this->canDo->get('core.admin')) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php echo $this->form->getInput('rules'); ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
