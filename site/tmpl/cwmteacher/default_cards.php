<?php

/**
 * Teacher detail — card grid sub-template for messages
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmteacher\HtmlView $this */

$studies = $this->teacherstudies;

if (empty($studies)) {
    return;
}
?>
<div class="proclaim-teacher-cards row g-3">
    <?php foreach ($studies as $study) :
        $title     = $study->studytitle ?? '';
        $date      = $study->studydate ?? '';
        $scripture = $study->scripture1 ?? '';
        $intro     = $study->studyintro ?? '';
        $link      = $study->detailslink ?? '';
        $thumbnail = $study->study_thumbnail ?? $study->series_thumbnail ?? '';
        $series    = $study->series_text ?? '';

        // Truncate intro to ~120 characters
        if (\strlen(strip_tags($intro)) > 120) {
            $intro = rtrim(mb_substr(strip_tags($intro), 0, 120)) . '&hellip;';
        } else {
            $intro = strip_tags($intro);
        }
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 proclaim-teacher-card">
                <?php if ($thumbnail) : ?>
                    <a href="<?php echo $link; ?>" class="proclaim-teacher-card-img">
                        <?php echo $thumbnail; ?>
                    </a>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <?php if ($title) : ?>
                        <h5 class="card-title mb-1">
                            <a href="<?php echo $link; ?>"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></a>
                        </h5>
                    <?php endif; ?>
                    <?php if ($date || $scripture) : ?>
                        <p class="card-text text-body-secondary small mb-2">
                            <?php if ($date) : ?>
                                <span class="proclaim-teacher-card-date"><?php echo $date; ?></span>
                            <?php endif; ?>
                            <?php if ($date && $scripture) : ?>
                                <span class="mx-1">&middot;</span>
                            <?php endif; ?>
                            <?php if ($scripture) : ?>
                                <span class="proclaim-teacher-card-scripture"><?php echo $scripture; ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($series) : ?>
                        <p class="card-text small text-body-secondary mb-2">
                            <?php echo $series; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($intro) : ?>
                        <p class="card-text proclaim-teacher-card-intro mt-auto">
                            <?php echo $intro; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
