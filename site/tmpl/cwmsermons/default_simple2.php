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

$wa->addInlineStyle(
    "
.title {
    text-transform: uppercase;
    font-family: 'Fjalla One', sans-serif;
    font-size: 16px;
    text-align: left;
    margin-top: 10px;
    margin-bottom: 2px;
    padding: 0;
    color: #444;
    font-weight: 700;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.authordate {
    font-size: 12px;
    color: #777;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.media__item__wrapper {
    line-height: 1.5;
    overflow: hidden;
}
.media__item__info {
    overflow: hidden;
}
.media__item__details {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.media__container {
    display: flex;
    margin-top: 1em;
}
.media__view {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    width: 100%;
}
@media (max-width: 991px) {
    .media__view {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 575px) {
    .media__view {
        grid-template-columns: repeat(1, 1fr);
    }
}
.sf_hidden {
    display: none !important;
}
img, svg {
    vertical-align: middle;
}
.thumbnail {
    position: relative;
}
.caption {
    position: absolute;
    margin: 0;
    top: 50%;
    left: 50%;
    margin-right: -50%;
    transform: translate(-50%, -50%);
}
.overlay-div {
    height: 100%;
    width: 100%;
    position: absolute;
    background-color: #000;
    opacity: .7;
}
/* Clamp overlay text on image to 3 lines max */
.media__item__wrapper .card-img-overlay .overlay-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: clamp(0.75rem, 1.5vw, 1.1rem);
    padding: 0 0.25rem;
}
/* Constrain card images to uniform 16:9 aspect ratio */
.media__item__wrapper .card {
    aspect-ratio: 16 / 9;
    overflow: hidden;
}
.media__item__wrapper .card .card-img,
.media__item__wrapper .card picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 20%;
}
.media__item__wrapper .card picture {
    display: contents;
}"
);

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

<div class="container">
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