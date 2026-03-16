<?php

/**
 * Hero Sections style — tag links for text-only sections.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData: same structure as cards/section-text.php
 *   Additional key:
 *   - bandStyle (string) 'light', 'dark', or 'accent'
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Layout\LayoutHelper;

$section      = $displayData['section'];
$sectionLabel = $displayData['sectionLabel'] ?? '';
$bandStyle    = $displayData['bandStyle'] ?? 'light';
$items        = $section['items'];
$limit        = $section['limit'];
$useLimit     = $section['useLimit'];
$divId        = $section['divId'];

if (empty($items)) {
    return;
}

$visibleItems = [];
$hiddenItems  = [];

foreach ($items as $index => $item) {
    if ($useLimit === 1) {
        if ($item['landing_show'] === 2) {
            $hiddenItems[] = $item;
        } else {
            $visibleItems[] = $item;
        }
    } else {
        if ($index >= $limit && $limit < 10000) {
            $hiddenItems[] = $item;
        } else {
            $visibleItems[] = $item;
        }
    }
}

$bandClass = match ($bandStyle) {
    'dark'   => 'proclaim-landing-band--dark',
    'accent' => 'proclaim-landing-band--accent',
    default  => 'proclaim-landing-band--light',
};

$tagClass = $bandStyle === 'accent' ? 'proclaim-landing-tag--dark' : 'proclaim-landing-tag--light';
?>
<div class="proclaim-landing-band <?php echo $bandClass; ?>">
    <div class="container">
        <?php if ($sectionLabel) : ?>
            <h2 class="proclaim-landing-band__title"><?php echo htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php endif; ?>

        <div class="proclaim-landing-tags">
            <?php foreach ($visibleItems as $item) : ?>
                <a href="<?php echo $item['url']; ?>" class="proclaim-landing-tag <?php echo $tagClass; ?>">
                    <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>

            <?php foreach ($hiddenItems as $item) : ?>
                <a href="<?php echo $item['url']; ?>"
                   class="proclaim-landing-tag <?php echo $tagClass; ?> proclaim-landing--hidden"
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
</div>
