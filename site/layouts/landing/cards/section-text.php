<?php

/**
 * Card Grid style — pill badges for text-only sections.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData: same as section-images.php
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlanding;
use Joomla\CMS\Layout\LayoutHelper;

$section      = $displayData['section'];
$sectionLabel = $displayData['sectionLabel'] ?? '';
$items        = $section['items'];
$limit        = $section['limit'];
$useLimit     = $section['useLimit'];
$divId        = $section['divId'];

if (empty($items)) {
    return;
}

[$visibleItems, $hiddenItems] = Cwmlanding::splitItems($items, $limit, $useLimit);

$iconClass = match ($section['sectionType']) {
    'topics'       => 'fas fa-tags',
    'books'        => 'fas fa-bible',
    'locations'    => 'fas fa-map-marker-alt',
    'messagetypes' => 'fas fa-comment-alt',
    'years'        => 'fas fa-calendar-alt',
    default        => 'fas fa-list',
};
?>
<div class="proclaim-landing__section" data-section="<?php echo $section['sectionType']; ?>">
    <?php if ($sectionLabel) : ?>
        <h2 class="proclaim-landing__section-title">
            <i class="<?php echo $iconClass; ?> me-2"></i><?php echo htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8'); ?>
        </h2>
    <?php endif; ?>

    <div class="proclaim-landing-pills">
        <?php foreach ($visibleItems as $item) : ?>
            <a href="<?php echo $item['url']; ?>" class="proclaim-landing-pill">
                <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>

        <?php foreach ($hiddenItems as $item) : ?>
            <a href="<?php echo $item['url']; ?>"
               class="proclaim-landing-pill proclaim-landing--hidden"
               data-proclaim-section="<?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?>"
               data-proclaim-hidden>
                <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($hiddenItems)) : ?>
        <?php echo LayoutHelper::render('landing.showhide', [
            'divId'       => $divId,
            'label'       => $sectionLabel,
            'hiddenCount' => \count($hiddenItems),
        ], JPATH_COMPONENT_SITE . '/layouts'); ?>
    <?php endif; ?>
</div>
