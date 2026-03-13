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

use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastPlatformHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmpodcast\HtmlView $this */

$app   = Factory::getApplication();
$input = $app->getInput();

// Podcast Index API availability
$cParams       = ComponentHelper::getParams('com_proclaim');
$hasIndexApi   = !empty($cParams->get('podcastindex_api_key'))
    && !empty($cParams->get('podcastindex_api_secret'));
$platforms     = CwmpodcastPlatformHelper::getPlatformDefinitions();

// Build JS maps: platform key → example URL, platform key → FA icon class
$urlHints      = [];
$platformIcons = [];
foreach ($platforms as $p) {
    $urlHints[$p['key']]      = $p['url_hint'];
    $platformIcons[$p['key']] = $p['icon'];
}

$this->getDocument()->addScriptOptions('com_proclaim.platformUrlHints', $urlHints);
$this->getDocument()->addScriptOptions('com_proclaim.platformIcons', $platformIcons);

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_proclaim.podcast');
$this->getDocument()->addScriptOptions('com_proclaim.formValidate', ['cancelTask' => 'cwmpodcast.cancel', 'formId' => 'podcast-form']);
Text::script('JGLOBAL_VALIDATION_FORM_FAILED');
$wa->useScript('keepalive')
    ->useScript('com_proclaim.form-validate-submit');

if ($hasIndexApi && (int) $this->item->id > 0) {
    $submitUrl = Route::_('index.php?option=com_proclaim&task=cwmpodcast.submitToIndex&format=json', false);
    $token     = \Joomla\CMS\Session\Session::getFormToken();
    $wa->addInlineScript(
        "document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('btn-submit-podcastindex');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var status = document.getElementById('podcastindex-status');
                btn.disabled = true;
                status.textContent = '" . $this->escape(Text::_('JBS_PDC_SUBMITTING')) . "';
                fetch('" . $submitUrl . "&id=' + btn.dataset.podcastId + '&" . $token . "=1', {
                    method: 'GET',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    status.textContent = data.message || '';
                    status.className = 'ms-2 text-' + (data.success ? 'success' : 'danger');
                    btn.disabled = false;
                })
                .catch(function(err) {
                    status.textContent = err.message;
                    status.className = 'ms-2 text-danger';
                    btn.disabled = false;
                });
            });
        });"
    );
}

// Platform URL hint + icon preview — updates when platform dropdown changes
$wa->addInlineScript(
    "document.addEventListener('DOMContentLoaded', function() {
        var hints = Joomla.getOptions('com_proclaim.platformUrlHints') || {};
        var icons = Joomla.getOptions('com_proclaim.platformIcons') || {};
        var form = document.getElementById('podcast-form');
        if (!form) return;

        function updateRow(select) {
            var row = select.closest('tr') || select.closest('.subform-repeatable-group');
            if (!row) return;

            // Update URL placeholder
            var urlField = row.querySelector('input[type=\"url\"], input[name*=\"[url]\"]');
            if (urlField) {
                urlField.placeholder = hints[select.value] || 'https://';
            }

            // Icon preview: place inside .controls div, right after the select
            var iconClass = icons[select.value] || 'fa-solid fa-headphones';
            var controls = select.closest('.controls');
            if (!controls) return;

            var preview = controls.querySelector('.platform-icon-preview');
            if (!preview) {
                controls.classList.add('platform-links-controls');
                preview = document.createElement('span');
                preview.className = 'platform-icon-preview';
                controls.appendChild(preview);
            }
            preview.innerHTML = '<i class=\"' + iconClass + '\" aria-hidden=\"true\" title=\"Default icon\"></i>';
        }

        // Handle changes via event delegation
        form.addEventListener('change', function(e) {
            if (e.target && e.target.name && e.target.name.indexOf('[platform]') !== -1) {
                updateRow(e.target);
            }
        });

        // Set on existing rows at load
        form.querySelectorAll('select[name*=\"[platform]\"]').forEach(updateRow);

        // Handle subform row additions
        form.addEventListener('subform-row-add', function(e) {
            var row = e.detail ? e.detail.row : (e.target || null);
            if (!row) return;
            var sel = row.querySelector('select[name*=\"[platform]\"]');
            if (sel) updateRow(sel);
        });
    });"
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
                <?php echo $this->form->renderField('website'); ?>
                <?php
                // Detect legacy URL in podcastlink (non-numeric = unmatched URL from pre-10.1)
                $legacyPodcastLink = '';
                $rawPodcastLink    = $this->item->podcastlink ?? '';
                if ($rawPodcastLink !== '' && !ctype_digit((string) $rawPodcastLink)) {
                    $legacyPodcastLink = $rawPodcastLink;
                }
                ?>
                <?php if ($legacyPodcastLink !== '') : ?>
                <div class="alert alert-warning">
                    <strong><?php echo Text::_('JBS_PDC_PODCAST_URL_LEGACY_LABEL'); ?></strong>
                    <mark class="bg-light text-dark px-1"><?php echo $this->escape($legacyPodcastLink); ?></mark>
                    <p class="mb-0 mt-1"><?php echo Text::_('JBS_PDC_PODCAST_URL_LEGACY_HELP'); ?></p>
                </div>
                <?php endif; ?>
                <?php echo $this->form->renderField('podcastlink'); ?>
                <?php echo $this->form->renderField('author'); ?>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderField('published'); ?>
                <?php echo $this->form->renderField('location_id'); ?>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo $this->form->renderField('detailstemplateid'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'author', Text::_('JBS_PDC_FEED_OWNER')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('editor_name'); ?>
                <?php echo $this->form->renderField('editor_email'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('JBS_PDC_PODCAST_IMAGES')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('podcastimage'); ?>
                <?php echo $this->form->renderField('podcast_image_subscribe'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rss', Text::_('JBS_PDC_RSS_SETTINGS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('filename'); ?>
                <?php echo $this->form->renderField('podcastlimit'); ?>
                <?php echo $this->form->renderField('itunes_category'); ?>
                <?php echo $this->form->renderField('itunes_subcategory'); ?>
                <?php echo $this->form->renderField('itunes_explicit'); ?>
                <?php echo $this->form->renderField('itunes_type'); ?>
                <?php echo $this->form->renderField('linktype'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'episode', Text::_('JBS_PDC_EPISODE_OPTIONS')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('episodetitle'); ?>
                <?php echo $this->form->renderField('custom'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'subscription', Text::_('JBS_PDC_PODCAST_SUBSCRIPTION')); ?>
        <div class="row">
            <div class="col-lg-12">
                <?php echo $this->form->renderField('podcast_subscribe_show'); ?>
                <?php echo $this->form->renderField('podcast_subscribe_desc'); ?>
                <hr>
                <h4><?php echo Text::_('JBS_PDC_PLATFORM_LINKS'); ?></h4>
                <p class="text-muted"><?php echo Text::_('JBS_PDC_PLATFORM_LINKS_DESC'); ?></p>
                <?php echo $this->form->renderField('platform_links'); ?>
            </div>
        </div>

        <?php if ((int) $this->item->id > 0) : ?>
        <hr>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo Text::_('JBS_PDC_DIRECTORY_SUBMISSION'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <?php echo Text::_('JBS_PDC_DIRECTORY_INFO'); ?>
                        </div>

                        <?php if ($hasIndexApi) : ?>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="btn-submit-podcastindex"
                                    data-podcast-id="<?php echo (int) $this->item->id; ?>">
                                <i class="fa-solid fa-podcast" aria-hidden="true"></i>
                                <?php echo Text::_('JBS_PDC_SUBMIT_TO_INDEX'); ?>
                            </button>
                            <span id="podcastindex-status" class="ms-2"></span>
                        </div>
                        <?php else : ?>
                        <p class="text-muted">
                            <?php echo Text::_('JBS_PDC_SUBMIT_ERROR_NO_API_KEYS'); ?>
                        </p>
                        <?php endif; ?>

                        <h6><?php echo Text::_('JBS_PDC_MANUAL_SUBMISSION'); ?></h6>
                        <ul class="list-unstyled">
                            <?php foreach ($platforms as $p) : ?>
                                <?php if (!empty($p['submit_url'])) : ?>
                                <li class="mb-1">
                                    <a href="<?php echo htmlspecialchars($p['submit_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                       target="_blank" rel="noopener noreferrer">
                                        <i class="<?php echo $p['icon']; ?>" aria-hidden="true"></i>
                                        <?php echo $p['label']; ?>
                                    </a>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
