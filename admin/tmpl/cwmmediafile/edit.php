<?php

/**
 * Edit
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
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmediafile\HtmlView $this */

$input = Factory::getApplication()->getInput();

// Set up defaults
if ($input->getInt('id')) {
    $study_id   = $this->item->study_id;
    $createdate = $this->item->createdate;
    $podcast_id = $this->item->podcast_id;
} else {
    $study_id   = $this->options->study_id;
    $createdate = $this->options->createdate;
    $podcast_id = $this->admin_params->get('podcast');
}

$new = ($this->item->id === '0' || empty($this->item->id));

// Determine if we should show the server picker modal
$showServerPicker = $new && $this->addon === null;

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_proclaim.mediafile-edit');

// Pass config to JavaScript
$this->getDocument()->addScriptOptions('com_proclaim.mediafile', [
    'token'            => Session::getFormToken(),
    'isNew'            => $new,
    'showServerPicker' => $showServerPicker,
    'validationFailed' => Text::_('JGLOBAL_VALIDATION_FORM_FAILED'),
    'switchWarning'    => Text::_('JBS_MED_SERVER_TYPE_CHANGE_WARNING'),
    'loadingAddon'     => Text::_('JBS_MED_LOADING_ADDON'),
    'switchLoading'    => Text::_('JBS_MED_SERVER_SWITCH_LOADING'),
    'selectServerTitle' => Text::_('JBS_MED_SELECT_SERVER_TITLE'),
    'selectServerDesc'  => Text::_('JBS_MED_SELECT_SERVER_DESC'),
    'serverTypeLocalDesc'  => Text::_('JBS_MED_SERVER_TYPE_LOCAL_DESC'),
    'serverTypeDirectDesc' => Text::_('JBS_MED_SERVER_TYPE_DIRECT_DESC'),
    'serverTypeYoutubeDesc' => Text::_('JBS_MED_SERVER_TYPE_YOUTUBE_DESC'),
    'serverTypeLegacyDesc'  => Text::_('JBS_MED_SERVER_TYPE_LEGACY_DESC'),
    'selectLabel'           => Text::_('JSELECT'),
    'clearLabel'            => Text::_('JCLEAR'),
]);

$this->useCoreUI = true;

// YouTube-specific integration for Chapters & Tracks tab
$serverType = $this->state ? $this->state->get('type', '') : '';
if (strtolower($serverType) === 'youtube' && !$new) {
    $wa->useScript('com_proclaim.cwm-youtube-tracks');

    // Check if OAuth is connected
    $sParams = $this->state->get('s_params', []);
    $oauthConnected = !empty($sParams['access_token']);

    $this->getDocument()->addScriptOptions('com_proclaim.youtubeTracks', [
        'isYouTube'         => true,
        'mediaId'           => (int) $this->item->id,
        'oauthConnected'    => $oauthConnected,
        'baseUrl'           => \Joomla\CMS\Uri\Uri::base(),
        'token'             => Session::getFormToken(),
        'toolbarTitle'      => Text::_('JBS_MED_YOUTUBE_INTEGRATION'),
        'importChaptersBtn' => Text::_('JBS_MED_IMPORT_CHAPTERS_YOUTUBE'),
        'listCaptionsBtn'   => Text::_('JBS_MED_DOWNLOAD_CAPTIONS_YOUTUBE'),
        'importing'         => Text::_('JBS_MED_IMPORTING_CHAPTERS'),
        'importSuccess'     => Text::_('JBS_MED_IMPORT_CHAPTERS_SUCCESS'),
        'importFailed'      => Text::_('JBS_MED_IMPORT_CHAPTERS_NONE'),
        'importError'       => Text::_('JBS_MED_IMPORT_CHAPTERS_ERROR'),
        'loadingCaptions'   => Text::_('JBS_MED_LOADING_CAPTIONS'),
        'noCaptions'        => Text::_('JBS_MED_NO_CAPTIONS_FOUND'),
        'captionError'      => Text::_('JBS_MED_CAPTION_ERROR'),
        'downloadBtn'       => Text::_('JBS_MED_DOWNLOAD_VTT'),
        'downloaded'        => Text::_('JBS_MED_CAPTION_ADDED'),
        'oauthRequired'     => Text::_('JBS_MED_OAUTH_REQUIRED_CAPTIONS'),
    ]);
}
?>
<form action="<?php
echo 'index.php?option=com_proclaim&view=cwmmediafile&layout=edit&id=' . (int)$this->item->id; ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate"
      <?php if ($showServerPicker) : ?>data-show-server-picker="true"<?php endif; ?>>
    <div>
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <!-- Begin Content -->
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-7">
                <?php echo $this->form->renderField('study_id', null, $study_id); ?>
                <?php echo $this->form->renderField('createdate', null, $createdate); ?>
                <?php echo $this->form->renderField('server_id', null, $this->item->server_id); ?>
                <?php echo $this->form->renderField('podcast_id', null, $podcast_id); ?>

                <div id="addon-general-container">
                    <?php if ($this->addon !== null) : ?>
                        <?php echo $this->addon->renderGeneral($this->media_form, $new); ?>
                    <?php elseif (!$showServerPicker) : ?>
                        <div class="alert alert-info">
                            <?php echo Text::_('JBS_MED_SELECT_SERVER_FIRST'); ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <div class="col-lg-5">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('access'); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo $this->form->renderField('comment'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL')); ?>
        <div id="addon-options-content">
            <?php if ($this->addon !== null) : ?>
                <?php echo $this->addon->renderOptionsFields($this->media_form, $new); ?>
            <?php endif; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'tracks', Text::_('JBS_MED_CHAPTERS_AND_TRACKS')); ?>
        <div id="tracks-content">
            <?php if ($this->tracks_form !== null) : ?>
                <?php foreach ($this->tracks_form->getFieldsets('params') as $name => $fieldset) : ?>
                    <h3><?php echo Text::_($fieldset->label); ?></h3>
                    <?php foreach ($this->tracks_form->getFieldset($name) as $field) : ?>
                        <?php echo $field->renderField(); ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

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
        if ($this->canDo->get('core.admin')) : ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row">
                <?php echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
            <?php
        endif; ?>

        <?php
        echo HTMLHelper::_('uitab.endTabSet'); ?>

        <?php
        // Load the batch processing form.?>
        <?php
        echo HTMLHelper::_(
            'bootstrap.renderModal',
            'collapseModal',
            [
                'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
                'footer' => $this->loadTemplate('converter_footer'),
            ],
            $this->loadTemplate('converter_body')
        ); ?>
    </div>
    <?php
    echo $this->form->getInput('asset_id'); ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php
    echo $input->getBase64('return'); ?>"/>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
