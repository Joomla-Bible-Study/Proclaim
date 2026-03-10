<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

$wa = $this->getDocument()->getWebAssetManager();

$wa->useStyle('com_proclaim.sermons-simple2');

// Add template accent color for pagination
$accentColor = $this->params->get('backcolor', '#287585');
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2;
$trashed   = $this->state->get('filter.published') == -2;
$saveOrder = $listOrder === 'study.ordering';
// Use pre-calculated values from HtmlView
$listing      = $this->listing;
$defaultImage = $this->params->get('default_study_image', '');
?>

<div class="container cwm-simple2">
    <div class="row">
        <div class="col-sm-12 content">
            <div id="filters">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
?>
            </div>
            <section class="media__container">
                <div id="media__items__list"
                     class="su_macro_prototype list-unstyled media__view media__container list_contains_thumbnail"
>
                    <?php
    foreach ($this->items as $item) {
        $itemparams = new Registry();
        $params     = $itemparams->loadString($item->params);

        // Use study_image (original) for high quality; fall back to deriving from thumbnailm
        $studyImagePath = $item->study_image ?? '';

        if (!empty($studyImagePath)) {
            $imageObj = Cwmimages::getImagePath($studyImagePath);
        } elseif (!empty($item->thumbnailm)) {
            $imageObj = Cwmimages::getStudyOriginal($item->thumbnailm);
        } else {
            $imageObj = Cwmimages::getImagePath($defaultImage);
        }

        $overlaytext = '';

        if (
            $this->params->get('simplegridtextoverlay') == 1 || $params->get(
                'nooverlaysimplemode'
            ) === 'yes'
        ) {
            $overlaytext = '<h5 class="card-title text-uppercase overlay-text" style="text-shadow: 2px 2px #000000;">' . $item->studytitle . '</h5>';
        }
        if ($params->get('nooverlaysimplemode') === 'no') {
            $overlaytext = '';
        }
        ?>
                        <article class="media__item__wrapper">
                            <div class="thumbnail text-center">
                                <div class="card overflow-hidden border-0 rounded-0 text-center text-white">
                                    <?php
                    echo Cwmimages::renderPicture($imageObj, $item->studytitle, 'card-img rounded-0');
                    ?>
                                    <div class="card-img-overlay d-flex flex-column justify-content-center">
                                        <?php
                        echo $overlaytext; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="media__item__info">
                                <h4 class="title"><?php
                    echo $item->studytitle; ?></h4>
                                <small class="media__item__details">
                                    <span class="authordate" data-prefix="by"><?php
                        echo Text::_('CWM_BY');
        echo $item->teachername; ?></span>
                                    <span class="media__item__details__divider">|</span>
                                    <span class="authordate"><?php
        echo $item->studydate; ?></span>
                                </small>
                                <div class="mediafiles">
                                    <?php
                                    echo $item->media; ?>
                                </div>
                            </div>
                        </article>
                        <?php
                        //end foreach of $items
    } ?>
                </div>

            </section>
        </div>
    </div>
</div>
<div class="pagination-container pagelinks">
    <?php echo $this->pagination->getPageslinks(); ?>
</div>