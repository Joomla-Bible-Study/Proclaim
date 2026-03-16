<?php

/**
 * Hero Sections style — overlay image cards for teachers/series.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData: same structure as cards/section-images.php
 *   Additional key:
 *   - bandStyle (string) 'light' or 'dark' for alternating bands
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

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

$bandClass = $bandStyle === 'dark' ? 'proclaim-landing-band--dark' : 'proclaim-landing-band--light';
?>
<div class="proclaim-landing-band <?php echo $bandClass; ?>">
    <div class="container">
        <?php if ($sectionLabel) : ?>
            <h2 class="proclaim-landing-band__title"><?php echo htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($visibleItems as $item) : ?>
                <div class="col-md-6 col-lg-3">
                    <a href="<?php echo $item['url']; ?>" class="proclaim-landing-hero-card">
                        <?php if ($item['image'] && !empty($item['image']->path)) : ?>
                            <div class="proclaim-landing-hero-card__bg"
                                 style="background-image: url('<?php echo Uri::root() . htmlspecialchars($item['image']->path, ENT_QUOTES, 'UTF-8'); ?>');"></div>
                        <?php else : ?>
                            <div class="proclaim-landing-hero-card__bg proclaim-landing-hero-card__bg--placeholder"></div>
                        <?php endif; ?>
                        <div class="proclaim-landing-hero-card__overlay"></div>
                        <div class="proclaim-landing-hero-card__content">
                            <div class="proclaim-landing-hero-card__title"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php if (!empty($item['meta'])) : ?>
                                <div class="proclaim-landing-hero-card__sub"><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <?php foreach ($hiddenItems as $item) : ?>
                <div class="col-md-6 col-lg-3 proclaim-landing--hidden"
                     data-proclaim-section="<?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?>"
                     data-proclaim-hidden>
                    <a href="<?php echo $item['url']; ?>" class="proclaim-landing-hero-card">
                        <?php if ($item['image'] && !empty($item['image']->path)) : ?>
                            <div class="proclaim-landing-hero-card__bg"
                                 style="background-image: url('<?php echo Uri::root() . htmlspecialchars($item['image']->path, ENT_QUOTES, 'UTF-8'); ?>');"></div>
                        <?php else : ?>
                            <div class="proclaim-landing-hero-card__bg proclaim-landing-hero-card__bg--placeholder"></div>
                        <?php endif; ?>
                        <div class="proclaim-landing-hero-card__overlay"></div>
                        <div class="proclaim-landing-hero-card__content">
                            <div class="proclaim-landing-hero-card__title"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></div>
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
</div>
