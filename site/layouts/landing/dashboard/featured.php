<?php

/**
 * Dashboard style — featured items row (latest series + recent teacher).
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - allSections (array) All section data arrays keyed by sectionType
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$allSections = $displayData['allSections'] ?? [];

// Pick the first teacher and first series as featured
$featuredTeacher = null;
$featuredSeries  = null;

if (!empty($allSections['teachers']['items'])) {
    $featuredTeacher = $allSections['teachers']['items'][0];
}

if (!empty($allSections['series']['items'])) {
    $featuredSeries = $allSections['series']['items'][0];
}

if (!$featuredTeacher && !$featuredSeries) {
    return;
}
?>
<div class="proclaim-landing-featured">
    <div class="proclaim-landing-featured__label"><?php echo Text::_('JBS_CMN_FEATURED'); ?></div>
    <div class="row g-3">
        <?php if ($featuredSeries) : ?>
            <div class="col-md-6">
                <a href="<?php echo $featuredSeries['url']; ?>" class="proclaim-landing-featured-card">
                    <?php if ($featuredSeries['image'] && !empty($featuredSeries['image']->path)) : ?>
                        <div class="proclaim-landing-featured-card__img"
                             style="background-image: url('<?php echo Uri::root() . htmlspecialchars($featuredSeries['image']->path, ENT_QUOTES, 'UTF-8'); ?>');"></div>
                    <?php else : ?>
                        <div class="proclaim-landing-featured-card__img proclaim-landing-featured-card__img--placeholder">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    <?php endif; ?>
                    <div class="proclaim-landing-featured-card__body">
                        <div class="proclaim-landing-featured-card__tag"><?php echo Text::_('JBS_CMN_LATEST_SERIES'); ?></div>
                        <p class="proclaim-landing-featured-card__title"><?php echo htmlspecialchars($featuredSeries['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if (!empty($featuredSeries['meta'])) : ?>
                            <p class="proclaim-landing-featured-card__meta"><?php echo htmlspecialchars($featuredSeries['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($featuredTeacher) : ?>
            <div class="col-md-6">
                <a href="<?php echo $featuredTeacher['url']; ?>" class="proclaim-landing-featured-card">
                    <?php if ($featuredTeacher['image'] && !empty($featuredTeacher['image']->path)) : ?>
                        <div class="proclaim-landing-featured-card__img"
                             style="background-image: url('<?php echo Uri::root() . htmlspecialchars($featuredTeacher['image']->path, ENT_QUOTES, 'UTF-8'); ?>');"></div>
                    <?php else : ?>
                        <div class="proclaim-landing-featured-card__img proclaim-landing-featured-card__img--placeholder">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    <?php endif; ?>
                    <div class="proclaim-landing-featured-card__body">
                        <div class="proclaim-landing-featured-card__tag"><?php echo Text::_('JBS_CMN_FEATURED_TEACHER'); ?></div>
                        <p class="proclaim-landing-featured-card__title"><?php echo htmlspecialchars($featuredTeacher['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if (!empty($featuredTeacher['meta'])) : ?>
                            <p class="proclaim-landing-featured-card__meta"><?php echo htmlspecialchars($featuredTeacher['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
