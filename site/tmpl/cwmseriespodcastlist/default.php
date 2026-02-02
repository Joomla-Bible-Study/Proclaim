<?php
/**
 * Default
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmimagelib;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Site\View\Cwmseriespodcastlist\HtmlView $this */
?>
<h2><?php echo Text::_('JBS_CMN_SERIES_PODCASTS'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseriespodcastlist'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="effect-1" class="effects" role="list" aria-label="<?php echo Text::_('JBS_CMN_SERIES_PODCASTS'); ?>">
        <?php foreach ($this->items as $item) :
            $originalFile = $item->series_thumbnail;
            $seriesTitle = stripslashes($item->series_text);

            if (!empty($originalFile) && file_exists(JPATH_ROOT . '/' . $originalFile)) {
                $img = Cwmimagelib::getSeriesPodcast($originalFile);
            } else {
                $img = Cwmimagelib::getSeriesPodcast($this->params->get('default_study_image', ''));
            }

            $podcastUrl = Route::_(
                'index.php?option=com_proclaim&view=podcastdisplay&id=' .
                $item->id . ':' . $item->alias
            );
            ?>
            <div class="jbsmimg" role="listitem">
                <?php echo HTMLHelper::image(
                    $img,
                    Text::sprintf('JBS_CMN_SERIES_IMAGE_ALT', $seriesTitle),
                    $this->attribs
                ); ?>
                <div class="overlay">
                    <a href="<?php echo $podcastUrl; ?>"
                       class="expand"
                       aria-label="<?php echo Text::sprintf('JBS_CMN_VIEW_PODCAST_SERIES', $seriesTitle); ?>">
                        <span aria-hidden="true">+</span>
                        <span class="visually-hidden"><?php echo Text::_('JBS_CMN_VIEW'); ?></span>
                    </a>
                    <p class="expand"><?php echo $seriesTitle; ?></p>
                    <a href="#"
                       class="jbsmclose-overlay hidden"
                       role="button"
                       aria-label="<?php echo Text::_('JBS_CMN_CLOSE_OVERLAY'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="clearfix"></div>
    <?php if ($this->params->get('show_pagination', 2)) : ?>
        <nav class="pagination" aria-label="<?php echo Text::_('JLIB_HTML_PAGINATION'); ?>">
            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter pt-2">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php endif; ?>
            <?php echo $this->pagination->getPagesLinks(); ?>
        </nav>
    <?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
