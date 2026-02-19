<?php

/**
 * Default for sermons
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// Add template accent color for pagination
$accentColor = $this->params->get('backcolor', '#287585');
$wa          = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");

// Use pre-calculated values from HtmlView
$teachers        = $this->teachersFluid;
$listing         = $this->listing;
$classelement    = $this->classelement;
$itemid          = $this->itemid;
$paginationStyle = $this->params->get('pagination_style', 'pagination');

?>

<div class="container proclaim-main-content" id="proclaim-main-content" role="main">
    <?php
    $showArchived = $this->params->get('show_archived', '0');
if ($showArchived === '1') : ?>
        <div class="alert alert-info proclaim-archive-notice">
            <i class="fas fa-archive" aria-hidden="true"></i>
            <?php echo Text::_('JBS_CMN_VIEWING_ARCHIVED'); ?>
        </div>
    <?php elseif ($showArchived === '2') : ?>
        <div class="alert alert-info proclaim-archive-notice">
            <i class="fas fa-archive" aria-hidden="true"></i>
            <?php echo Text::_('JBS_CMN_VIEWING_ALL'); ?>
        </div>
    <?php endif; ?>
    <div id="bsheader" class="row">
        <?php
    if ($this->params->get('showpodcastsubscribelist') === '1') {
        echo $this->subscribe;
    }
?>
    </div>
    <?php
    if ($this->params->get('intro_show') > 0) {
        if ($this->params->get('listteachers') && $this->params->get('list_teacher_show') > 0) {
            ?>
            <div class="hero-unit pt-4 pb-3">
                <div class="row">

                    <?php
                    foreach ($teachers as $teacher) {
                        $teacherAlt = htmlspecialchars($teacher['name'], ENT_QUOTES, 'UTF-8');
                        echo '<div class="col">';
                        $teacherImg = Cwmimages::getImagePath($teacher['image']);
                        if ($this->params->get('teacherlink') > 0) {
                            echo '<a href="' . Route::_(
                                'index.php?option=com_proclaim&view=cwmteacher&id=' . $teacher['id'] . '&t=' . $teacher['t'] . '&Itemid=' . $itemid
                            ) . '">'
                            . Cwmimages::renderPicture($teacherImg, $teacherAlt, 'img-polaroid')
                            . '</a>';
                        } else {
                            echo Cwmimages::renderPicture($teacherImg, $teacherAlt, 'img-polaroid');
                        }
                        if ($this->params->get('teacherlink') > 0) {
                            echo '<div class="caption"><p><a href="' . Route::_(
                                'index.php?option=com_proclaim&view=cwmteacher&id=' .
                                    $teacher['id'] . '&t=' . $teacher['t']
                            ) . '">' . $teacher['name'] . '</a></p></div>';
                        } else {
                            echo '<div class="caption"><p>' . $teacher['name'] . '</p></div>';
                        }
                        echo '</div>';
                    }
            ?>

                </div>
            </div>
            <?php
        } ?>
        <?php
        if ($this->params->get('show_page_image') == 1 || $this->params->get('show_page_title') > 0) { ?>
            <div class="d-inline-flex align-items-center mb-3">
                <?php
                if ($this->params->get('show_page_image') > 0) {
                    if ((int) $this->params->get('main_image_icon_or_image') === 1 && $this->mainimage !== '') {
                        echo '<div class="me-3" style="max-height: 80px;">' . $this->mainimage . '</div>';
                    } else {
                        echo '<i class="fas fa-bible fa-3x me-3"></i>';
                    }
                }
            if ($this->params->get('show_page_title') > 0) {
                echo '<h2 class="mb-0">' . $this->params->get('list_page_title') . '</h2>';
            }
            ?>
            </div>
            <?php
        } ?>
        <div class="row">
            <div class="col-12">
                <?php

                if (!empty($this->params->get('list_intro'))) {
                    ?>
                    <div style="display: block;">
                        <?php
                        echo $this->params->get('list_intro'); ?>
                    </div>
                    <?php
                } ?>

            </div>
        </div>
        <?php
    } ?>
</div>

<div class="container">
    <div class="row">
        <div class="col-12">
            <?php
            // Search tools bar
            echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
?>
            <?php // Top pagination (standard mode only)?>
            <?php if ($paginationStyle === 'pagination') : ?>
            <div id="proclaim-pagination-top" class="proclaim-pagination">
            <?php if (!empty($this->items)) : ?>
                <?php
    if (
        ($this->pagination->pagesTotal > 1) &&
        ($this->params->def('show_pagination', 1) === '1' || ($this->params->get(
            'show_pagination'
        ) === '1'))
    ) : ?>
                    <div class="pagination pagination-centered">
                        <?php
            if ($this->params->def('show_pagination_results', 1)) : ?>
                            <p class="counter float-right">
                                <?php
                    echo $this->pagination->getPagesCounter(); ?>
                            </p>
                            <?php
            endif; ?>

                        <?php
            echo $this->pagination->getPagesLinks(); ?>
                    </div>
                    <?php
    endif; ?>
            <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php // Sermon listing?>
            <div id="proclaim-sermon-list" aria-live="polite">
            <?php
if ($this->items) {
    echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'sermons');
} else {
    echo "<h4>" . Text::_("JBS_CMN_STUDY_NOT_FOUND") . "</h4><br />";
}
?>
            </div>
            <?php // Bottom pagination (standard mode only)?>
            <?php if ($paginationStyle === 'pagination') : ?>
            <div id="proclaim-pagination-bottom" class="proclaim-pagination">
            <?php if (!empty($this->items)) : ?>
                <?php
    if (
        ($this->pagination->pagesTotal > 1) &&
        ($this->params->def('show_pagination', 2) === '2' || ($this->params->get(
            'show_pagination'
        ) === '2'))) : ?>
                    <nav class="pagination__wrapper" aria-label="Pagination">
                        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                            <div class="text-end me-3">
                                <?php
                echo $this->pagination->getPagesCounter(); ?>
                            </div>
                        <?php endif; ?>
                    </nav>
                    <div class="pagination pagination-centered">
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php // Load More button (loadmore and infinite modes — in infinite mode, shown after auto-load threshold)?>
            <?php if ($paginationStyle === 'loadmore' || $paginationStyle === 'infinite') : ?>
            <div class="proclaim-load-more" id="proclaim-load-more"<?php if ($paginationStyle === 'infinite') : ?> style="display:none"<?php endif; ?>>
                <button type="button" class="btn btn-outline-primary">
                    <?php echo Text::_('JBS_CMN_LOAD_MORE'); ?>
                </button>
            </div>
            <?php endif; ?>

            <?php // Item counter and scroll sentinel (loadmore and infinite modes)?>
            <?php if ($paginationStyle !== 'pagination') : ?>
            <div class="proclaim-item-counter" id="proclaim-item-counter"></div>
            <div class="proclaim-scroll-sentinel" id="proclaim-scroll-sentinel"></div>
            <?php endif; ?>
            <?php
            if ($this->params->get('showpodcastsubscribelist') === '2') {
                echo $this->subscribe;
            }
?>
        </div>
    </div>
</div>
