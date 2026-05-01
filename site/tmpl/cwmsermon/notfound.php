<?php

declare(strict_types=1);

/**
 * Not Found layout for single sermon view
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app = \Joomla\CMS\Factory::getApplication();
$t   = $app->getInput()->getInt('t', 1);
?>
<div class="com-proclaim">
<div class="container-fluid proclaim-main-content" role="main">
    <div class="proclaim-notfound text-center py-5">
        <div class="mb-4">
            <span class="fa fa-search fa-3x text-muted" aria-hidden="true"></span>
        </div>
        <h1 class="mb-3"><?php echo Text::_('JBS_CMN_STUDY_NOT_FOUND'); ?></h1>
        <p class="lead text-muted mb-5"><?php echo Text::_('JBS_CMN_STUDY_NOT_FOUND_DESC'); ?></p>

        <?php if (!empty($this->recentItems)) : ?>
        <div class="proclaim-recent-messages mt-2 mb-5">
            <h3 class="mb-4"><?php echo Text::_('JBS_CMN_RECENT_MESSAGES'); ?></h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 justify-content-center mx-auto" style="max-width: 960px;">
                <?php foreach ($this->recentItems as $recent) :
                    $slug = $recent->alias ? $recent->id . ':' . $recent->alias : $recent->id;
                    $link = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $slug . '&t=' . $t);

                    // Determine thumbnail: study image → study thumbnail → teacher thumbnail → none
                    $thumb = '';
                    if (!empty($recent->image)) {
                        $thumb = $recent->image;
                    } elseif (!empty($recent->thumbnailm)) {
                        $thumb = $recent->thumbnailm;
                    } elseif (!empty($recent->teacher_thumbnail)) {
                        $thumb = $recent->teacher_thumbnail;
                    }

                    // Make path absolute if relative
                    if ($thumb && !str_starts_with($thumb, 'http')) {
                        $thumb = Uri::root() . ltrim($thumb, '/');
                    }
                    ?>
                    <div class="col">
                        <a href="<?php echo $link; ?>" class="text-decoration-none">
                            <div class="card h-100 shadow-sm border-0 proclaim-notfound-card">
                                <?php if ($thumb) : ?>
                                <img src="<?php echo $this->escape($thumb); ?>"
                                     class="card-img-top"
                                     alt="<?php echo $this->escape($recent->studytitle); ?>"
                                     style="height: 160px; object-fit: cover;">
                                <?php else : ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 160px;">
                                    <span class="fa fa-book-open fa-2x text-muted" aria-hidden="true"></span>
                                </div>
                                <?php endif; ?>
                                <div class="card-body text-start">
                                    <h5 class="card-title mb-1"><?php echo $this->escape($recent->studytitle); ?></h5>
                                    <?php if (!empty($recent->teachername)) : ?>
                                    <p class="card-text text-muted small mb-1">
                                        <span class="fa fa-user me-1" aria-hidden="true"></span><?php echo $this->escape($recent->teachername); ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="card-text text-muted small mb-0">
                                        <span class="fa fa-calendar me-1" aria-hidden="true"></span><?php echo HTMLHelper::_('date', $recent->studydate, Text::_('DATE_FORMAT_LC3')); ?>
                                    </p>
                                    <?php if (!empty($recent->series_text)) : ?>
                                    <p class="card-text text-muted small mb-0 mt-1">
                                        <span class="fa fa-layer-group me-1" aria-hidden="true"></span><?php echo $this->escape($recent->series_text); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $t); ?>" class="btn btn-primary btn-lg">
                <span class="fa fa-list me-2" aria-hidden="true"></span><?php echo Text::_('JBS_CMN_BROWSE_ALL_MESSAGES'); ?>
            </a>
        </div>
    </div>
</div>
</div>
