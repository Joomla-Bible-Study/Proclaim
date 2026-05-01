<?php

/**
 * Dashboard style — accordion panel with list items.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - section      (array)    Standardized section data from getSectionData()
 *   - sectionLabel (string)   Display label for this section
 *   - sectionIndex (int)      Position index (1-based), first section starts expanded
 *   - params       (Registry) Template params
 *   - accordionId  (string)   Parent accordion ID for Bootstrap collapse
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlanding;
use Joomla\CMS\Layout\LayoutHelper;

$section      = $displayData['section'];
$sectionLabel = $displayData['sectionLabel'] ?? '';
$sectionIndex = $displayData['sectionIndex'] ?? 1;
$accordionId  = $displayData['accordionId'] ?? 'proclaimLandingAccordion';
$items        = $section['items'];
$limit        = $section['limit'];
$useLimit     = $section['useLimit'];
$divId        = $section['divId'];
$collapseId   = 'collapse-' . $section['sectionType'];
$expanded     = $sectionIndex === 1;

if (empty($items)) {
    return;
}

[$visibleItems, $hiddenItems] = Cwmlanding::splitItems($items, $limit, $useLimit);

$iconClass = match ($section['sectionType']) {
    'teachers'     => 'fa-solid fa-user-tie',
    'series'       => 'fa-solid fa-layer-group',
    'topics'       => 'fa-solid fa-tags',
    'books'        => 'fa-solid fa-bible',
    'locations'    => 'fa-solid fa-location-dot',
    'messagetypes' => 'fa-solid fa-comment-dots',
    'years'        => 'fa-solid fa-calendar-days',
    default        => 'fa-solid fa-list',
};

$itemIcon = match ($section['sectionType']) {
    'teachers'     => 'fa-solid fa-user',
    'series'       => 'fa-solid fa-book-open',
    'topics'       => 'fa-solid fa-tag',
    'books'        => 'fa-solid fa-book',
    'locations'    => 'fa-solid fa-church',
    'messagetypes' => 'fa-solid fa-comment',
    'years'        => 'fa-solid fa-calendar',
    default        => 'fa-solid fa-circle',
};
?>
<div class="accordion-item proclaim-landing-accordion__item">
    <h2 class="accordion-header">
        <button class="accordion-button <?php echo $expanded ? '' : 'collapsed'; ?>"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#<?php echo $collapseId; ?>"
                aria-expanded="<?php echo $expanded ? 'true' : 'false'; ?>">
            <i class="<?php echo $iconClass; ?> me-2 text-primary"></i>
            <?php echo htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8'); ?>
            <span class="proclaim-landing-accordion__count"><?php echo \count($items); ?></span>
        </button>
    </h2>
    <div id="<?php echo $collapseId; ?>"
         class="accordion-collapse collapse <?php echo $expanded ? 'show' : ''; ?>"
         data-bs-parent="#<?php echo $accordionId; ?>">
        <div class="accordion-body">
            <?php foreach ($visibleItems as $item) : ?>
                <a href="<?php echo $item['url']; ?>" class="proclaim-landing-list-item">
                    <div class="proclaim-landing-list-item__icon"><i class="<?php echo $itemIcon; ?>"></i></div>
                    <span class="proclaim-landing-list-item__name"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>

            <?php foreach ($hiddenItems as $item) : ?>
                <a href="<?php echo $item['url']; ?>"
                   class="proclaim-landing-list-item proclaim-landing--hidden"
                   data-proclaim-section="<?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?>"
                   data-proclaim-hidden>
                    <div class="proclaim-landing-list-item__icon"><i class="<?php echo $itemIcon; ?>"></i></div>
                    <span class="proclaim-landing-list-item__name"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>

            <?php if (!empty($hiddenItems)) : ?>
                <?php echo LayoutHelper::render('landing.showhide', [
                    'divId'       => $divId,
                    'label'       => $sectionLabel,
                    'hiddenCount' => \count($hiddenItems),
                ], JPATH_COMPONENT_SITE . '/layouts'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
