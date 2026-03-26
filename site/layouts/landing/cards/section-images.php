<?php

/**
 * Card Grid style — image cards for teachers/series.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - section      (array)    Standardized section data from getSectionData()
 *   - sectionLabel (string)   Display label for this section
 *   - sectionIndex (int)      Position index (1-based)
 *   - params       (Registry) Template params
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlanding;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

$section      = $displayData['section'];
$sectionLabel = $displayData['sectionLabel'] ?? '';
$items        = $section['items'];
$limit        = $section['limit'];
$useLimit     = $section['useLimit'];
$divId        = $section['divId'];

if (empty($items)) {
    return;
}

// Determine which items are visible vs hidden
[$visibleItems, $hiddenItems] = Cwmlanding::splitItems($items, $limit, $useLimit);

$iconClass = match ($section['sectionType']) {
    'teachers' => 'fa-solid fa-user-tie',
    'series'   => 'fa-solid fa-layer-group',
    default    => 'fa-solid fa-folder',
};
?>
<div class="proclaim-landing__section" data-section="<?php echo $section['sectionType']; ?>">
    <?php if ($sectionLabel) : ?>
        <h2 class="proclaim-landing__section-title">
            <i class="<?php echo $iconClass; ?> me-2"></i><?php echo htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8'); ?>
        </h2>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($visibleItems as $item) : ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?php echo $item['url']; ?>" class="proclaim-landing-card">
                    <div class="proclaim-landing-card__img">
                        <?php if ($item['image'] && !empty($item['image']->path)) : ?>
                            <img src="<?php echo Uri::root() . $item['image']->path; ?>"
                                 alt="<?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy" />
                        <?php else : ?>
                            <div class="proclaim-landing-card__placeholder">
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="proclaim-landing-card__body">
                        <p class="proclaim-landing-card__name"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if (!empty($item['meta'])) : ?>
                            <p class="proclaim-landing-card__meta"><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

        <?php foreach ($hiddenItems as $item) : ?>
            <div class="col-6 col-md-4 col-lg-3 proclaim-landing--hidden"
                 data-proclaim-section="<?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?>"
                 data-proclaim-hidden>
                <a href="<?php echo $item['url']; ?>" class="proclaim-landing-card">
                    <div class="proclaim-landing-card__img">
                        <?php if ($item['image'] && !empty($item['image']->path)) : ?>
                            <img src="<?php echo Uri::root() . $item['image']->path; ?>"
                                 alt="<?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy" />
                        <?php else : ?>
                            <div class="proclaim-landing-card__placeholder">
                                <i class="<?php echo $iconClass; ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="proclaim-landing-card__body">
                        <p class="proclaim-landing-card__name"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if (!empty($item['meta'])) : ?>
                            <p class="proclaim-landing-card__meta"><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
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
