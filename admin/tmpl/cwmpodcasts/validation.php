<?php

/**
 * Podcast Validation Report
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmpodcasts\HtmlView $this */

$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$validationResults = $app->getUserState('com_proclaim.podcasts.validation', []);
$metadataFixResults = $app->getUserState('com_proclaim.podcasts.metadata_fix', null);

// Clear the metadata fix results after reading (one-time display)
if ($metadataFixResults !== null) {
    $app->setUserState('com_proclaim.podcasts.metadata_fix', null);
}

// Generate the report
$podcast = new Cwmpodcast();
$reportHtml = $podcast->getValidationReport($validationResults);

// Add JavaScript for metadata fix progress
$wa->useScript('com_proclaim.cwmadmin-metadata-fix');

// Pass translations to JavaScript
$translations = [
    'JBS_PDC_FIX_METADATA_PROGRESS_TITLE' => Text::_('JBS_PDC_FIX_METADATA_PROGRESS_TITLE'),
    'JBS_PDC_FIX_METADATA_PROGRESS' => Text::_('JBS_PDC_FIX_METADATA_PROGRESS'),
    'JBS_PDC_FIX_METADATA_COMPLETE_PROGRESS' => Text::_('JBS_PDC_FIX_METADATA_COMPLETE_PROGRESS'),
    'JBS_PDC_FIX_METADATA_CANCELLED' => Text::_('JBS_PDC_FIX_METADATA_CANCELLED'),
    'JBS_PDC_FIX_METADATA_CANCEL' => Text::_('JBS_PDC_FIX_METADATA_CANCEL'),
    'JBS_PDC_FIX_METADATA_CLOSE' => Text::_('JBS_PDC_FIX_METADATA_CLOSE'),
    'JBS_PDC_FIX_METADATA_NO_FILES' => Text::_('JBS_PDC_FIX_METADATA_NO_FILES'),
    'JBS_PDC_FIX_METADATA_FILES_FOUND' => Text::_('JBS_PDC_FIX_METADATA_FILES_FOUND'),
    'JBS_PDC_FIX_METADATA_FIXED' => Text::_('JBS_PDC_FIX_METADATA_FIXED'),
    'JBS_PDC_FIX_METADATA_FAILED' => Text::_('JBS_PDC_FIX_METADATA_FAILED'),
    'JBS_PDC_FIX_METADATA_SKIPPED' => Text::_('JBS_PDC_FIX_METADATA_SKIPPED'),
];
$token = $app->getSession()->getFormToken();

$translationsJson = json_encode($translations, JSON_THROW_ON_ERROR);
$inlineJs = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var fixProblemsBtn = document.getElementById('fixProblemsBtn');
    if (fixProblemsBtn) {
        fixProblemsBtn.addEventListener('click', function() {
            var progress = new MetadataFixProgress({
                baseUrl: 'index.php',
                token: '{$token}',
                translations: {$translationsJson}
            });
            progress.init();
        });
    }
});
JS;
$wa->addInlineScript($inlineJs);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h1><?php echo Text::_('JBS_PDC_VALIDATION_REPORT'); ?></h1>
        </div>
        <div class="col-auto">
            <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmpodcasts'); ?>" class="btn btn-secondary">
                <span class="icon-arrow-left" aria-hidden="true"></span>
                <?php echo Text::_('JBS_PDC_BACK_TO_LIST'); ?>
            </a>
            <button type="button" class="btn btn-warning" id="fixProblemsBtn">
                <span class="icon-wrench" aria-hidden="true"></span>
                <?php echo Text::_('JBS_PDC_FIX_PROBLEMS'); ?>
            </button>
            <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmpodcasts.writeXMLFile&' . $app->getSession()->getFormToken() . '=1'); ?>" class="btn btn-success">
                <span class="icon-upload" aria-hidden="true"></span>
                <?php echo Text::_('JBS_PDC_WRITE_XML_FILES'); ?>
            </a>
        </div>
    </div>

    <?php
    // Show available metadata detection methods
    $methods = $podcast->getAvailableDurationMethods();
    $formats = $podcast->getSupportedDurationFormats();
    ?>
    <div class="card mb-3">
        <div class="card-header">
            <strong><?php echo Text::_('JBS_PDC_METADATA_DETECTION'); ?></strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong><?php echo Text::_('JBS_PDC_METADATA_TYPES'); ?>:</strong>
                    <ul class="mb-2">
                        <li><span class="text-success">✓</span> <?php echo Text::_('JBS_PDC_METADATA_FILE_SIZE'); ?></li>
                        <li><span class="text-success">✓</span> <?php echo Text::_('JBS_PDC_METADATA_MIME_TYPE'); ?></li>
                        <li><span class="text-success">✓</span> <?php echo Text::_('JBS_PDC_METADATA_DURATION'); ?></li>
                    </ul>
                    <strong><?php echo Text::_('JBS_PDC_AVAILABLE_METHODS'); ?>:</strong>
                    <ul class="mb-0">
                        <li>
                            <?php echo $methods['ffprobe'] ? '<span class="text-success">✓</span>' : '<span class="text-muted">✗</span>'; ?>
                            FFprobe <?php echo $methods['ffprobe'] ? '<span class="badge bg-success">' . Text::_('JBS_PDC_ALL_FORMATS') . '</span>' : '<span class="badge bg-secondary">' . Text::_('JBS_PDC_NOT_INSTALLED') . '</span>'; ?>
                        </li>
                        <li>
                            <span class="text-success">✓</span>
                            <?php echo Text::_('JBS_PDC_NATIVE_PARSERS'); ?> (M4A, WAV, OGG, MP3)
                        </li>
                        <li>
                            <?php echo $methods['getid3'] ? '<span class="text-success">✓</span>' : '<span class="text-muted">✗</span>'; ?>
                            getID3 <?php echo $methods['getid3'] ? '' : '<span class="badge bg-secondary">' . Text::_('JBS_PDC_NOT_INSTALLED') . '</span>'; ?>
                        </li>
                        <li>
                            <?php echo $methods['youtube_api'] ? '<span class="text-success">✓</span>' : '<span class="text-muted">✗</span>'; ?>
                            <?php echo Text::_('JBS_PDC_YOUTUBE_API'); ?> <?php echo $methods['youtube_api'] ? '<span class="badge bg-info">' . Text::_('JBS_PDC_YOUTUBE_VIDEOS') . '</span>' : '<span class="badge bg-secondary">' . Text::_('JBS_PDC_NO_API_KEY') . '</span>'; ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <strong><?php echo Text::_('JBS_PDC_SUPPORTED_FORMATS'); ?>:</strong>
                    <p class="mb-0"><?php echo implode(', ', array_map('strtoupper', $formats)); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($metadataFixResults !== null) : ?>
    <div class="card mb-3">
        <div class="card-header <?php echo $metadataFixResults['failed'] > 0 ? 'bg-warning' : 'bg-success text-white'; ?>">
            <strong><?php echo Text::_('JBS_PDC_FIX_PROBLEMS_RESULTS'); ?></strong>
            <span class="badge bg-light text-dark ms-2">
                <?php echo Text::sprintf(
                    'JBS_PDC_FIX_METADATA_SUMMARY',
                    $metadataFixResults['fixed'],
                    $metadataFixResults['failed'],
                    $metadataFixResults['skipped']
                ); ?>
            </span>
        </div>
        <?php if (!empty($metadataFixResults['fixedItems']) || !empty($metadataFixResults['errors'])) : ?>
        <div class="card-body">
            <?php if (!empty($metadataFixResults['fixedItems'])) : ?>
            <p class="mb-2"><strong><?php echo Text::_('JBS_PDC_FIX_PROBLEMS_FIXED'); ?>:</strong></p>
            <ul class="list-unstyled mb-3">
                <?php foreach ($metadataFixResults['fixedItems'] as $item) : ?>
                    <li class="mb-1">
                        <span class="icon-check-circle text-success" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($item); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if (!empty($metadataFixResults['errors'])) : ?>
            <p class="mb-2"><strong><?php echo Text::_('JBS_PDC_FIX_PROBLEMS_ERRORS'); ?>:</strong></p>
            <ul class="list-unstyled mb-0">
                <?php foreach ($metadataFixResults['errors'] as $error) : ?>
                    <li class="mb-1">
                        <span class="icon-times-circle text-danger" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($error); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($validationResults)) : ?>
        <div class="alert alert-info">
            <?php echo Text::_('JBS_CMN_NO_PODCASTS_FOUND'); ?>
        </div>
    <?php else : ?>
        <?php echo $reportHtml; ?>
    <?php endif; ?>
</div>
