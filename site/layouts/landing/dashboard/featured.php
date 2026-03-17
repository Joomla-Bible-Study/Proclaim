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
$params      = $displayData['params'] ?? null;

// Use the "before break" limit from section settings, default to 2
$teacherLimit = $params ? (int) $params->get('landingteacherslimit', 2) : 2;
$seriesLimit  = $params ? (int) $params->get('landingserieslimit', 2) : 2;

$featuredTeachers = [];
$featuredSeries   = [];

if (!empty($allSections['teachers']['items'])) {
    $featuredTeachers = \array_slice($allSections['teachers']['items'], 0, $teacherLimit ?: 2);
}

if (!empty($allSections['series']['items'])) {
    $featuredSeries = \array_slice($allSections['series']['items'], 0, $seriesLimit ?: 2);
}

if (empty($featuredTeachers) && empty($featuredSeries)) {
    return;
}

$iconMap = [
    'teachers' => 'fas fa-user-tie',
    'series'   => 'fas fa-layer-group',
];

$tagMap = [
    'teachers' => Text::_('JBS_CMN_FEATURED_TEACHER'),
    'series'   => Text::_('JBS_CMN_LATEST_SERIES'),
];
?>
<div class="proclaim-landing-featured">
    <div class="proclaim-landing-featured__label"><?php echo Text::_('JBS_CMN_FEATURED'); ?></div>

    <?php foreach (['series' => $featuredSeries, 'teachers' => $featuredTeachers] as $type => $items) : ?>
        <?php if (!empty($items)) : ?>
            <div class="row g-3 mb-3">
                <?php foreach ($items as $item) : ?>
                    <div class="col-md-6">
                        <a href="<?php echo $item['url']; ?>" class="proclaim-landing-featured-card">
                            <?php if ($item['image'] && !empty($item['image']->path)) : ?>
                                <div class="proclaim-landing-featured-card__img"
                                     style="background-image: url('<?php echo Uri::root() . htmlspecialchars($item['image']->path, ENT_QUOTES, 'UTF-8'); ?>');"></div>
                            <?php else : ?>
                                <div class="proclaim-landing-featured-card__img proclaim-landing-featured-card__img--placeholder">
                                    <i class="<?php echo $iconMap[$type]; ?>"></i>
                                </div>
                            <?php endif; ?>
                            <div class="proclaim-landing-featured-card__body">
                                <div class="proclaim-landing-featured-card__tag"><?php echo $tagMap[$type]; ?></div>
                                <p class="proclaim-landing-featured-card__title"><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php if (!empty($item['meta'])) : ?>
                                    <p class="proclaim-landing-featured-card__meta"><?php echo htmlspecialchars($item['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
