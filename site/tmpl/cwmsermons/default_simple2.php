<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

HtmlHelper::_('dropdown.init');
HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', 'select');
$wa = $this->document->getWebAssetManager();

$wa->addInlineStyle(
    "
.title {text-transform: uppercase;
    color: #1e3e48;
    font-family: 'Fjalla One',sans-serif; font-size: 16px;
    text-align: left;
    margin-top: 10px;
    margin-bottom: 2px;
    padding: 0;
    color: #444;
    font-weight: 700;} 
    .authordate {font-size: 12px;
    color: #777; .small {font-size: .875em;} .media__item__wrapper {line-height: 1.5;}
    .media__item__wrapper 
    .placeholder {
    width: 100%;
    aspect-ratio: 1.78;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 3rem;
    background: #eee;
    color: #ccc;
    svg.icon {
    fill: currentColor;}
    @media (max-width: @screen-sm-max) {
	#su_media_filters,
	.media__filter__wrapper {
		display: block;
	}
}
.media__container {
	.flexbox-Display;
	margin-top: 1em;
}
.media__view {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 15px;
}
@media (max-width: @screen-md-min) {
	#su_media_sortby {
		margin-bottom: 15px;
		margin-left: 0;
	}
	.media__view {
		grid-template-columns: repeat(2, 1fr);
	}
}
@media (max-width: @screen-xs-min) {
	.media__view {
		grid-template-columns: repeat(1, 1fr);
	}
}
.sf_hidden {
	display: none !important;
}
svg.icon {
	fill: currentColor;

	&.big {
		width: 6rem;
		height: 6rem;
	}
	&.huge {
		width: 12rem;
		height: 12rem;
	}
	&.spin {
		animation: spin 2s linear infinite;
	}
}
img,
svg {
  vertical-align: middle;
}
.centered .media__item__image{position: absolute;
        z-index: 999;
        margin: 0 auto;
        left: 0;
        right: 0;
        top: 40%; /* Adjust this value to move the positioned div up and down */
        text-align: center;
        width: 60%; /* Set the width of the positioned div */;
}
.media__item__image--wrapper{position: relative;
        display: inline-block;}
        
 .h5 {-webkit-text-stroke: 4px black;}
 
.img-container img{
margin-left:36%;
   display: inline-block;
   position: relative;
   text-align: center;
   color: rgb(64, 11, 124);
}
.center .img-container {
position: absolute;
   top: 50%;
   left: 50%;
   transform: translate(-50%, -50%);
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
.overlay-div{
   height:100%;
   width: 100%;
   position:absolute;
   background-color:#000;
   opacity:.7;
}"
);
$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'study.ordering';
$listing   = new Cwmlisting();
$files     = new File();
$folder    = Folder::files('media/com_proclaim/images/rotating');
$count     = count($folder);


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
                     style="display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;">
                    <?php
    foreach ($this->items as $item) {
        $itemparams = new Registry();
        $params     = $itemparams->loadString($item->params);
        $studyimage = $params->get('studyimage');
        if (!empty($item->thumbnailm)) {
            $image = $item->thumbnailm;
        }
        if ($params->get('studyimage') !== -1) {
            //clean up extra data in the image
            $hash = str_contains($params->get('studyimage'), '#');
            if ($hash == 1) {
                $imageparam   = $params->get('studyimage');
                $hashlocation = strpos($imageparam, '#');
                $image        = substr($imageparam, 0, $hashlocation);
            } else {
                $image = 'media/com_proclaim/images/stockimages/' . $params->get('studyimage');
            }
        }
        if ($studyimage === null || (empty($item->thumbnailm) && ($params->get('studyimage') == -1))) {
            $random = random_int(0, $count);
            if (array_key_exists($random, $folder)) {
                $image = 'media/com_proclaim/images/rotating/' . $folder[$random];
            }
            if ($image == 'media/com_proclaim/images/stockimages/') {
                $image = 'media/com_proclaim/images/rotating/bible01.jpg';
            }
            if ($image == 'media/com_proclaim/images/stockimages/-1') {
                $image = 'media/com_proclaim/images/rotating/bible01.jpg';
            }
        }
        if (
            $this->params->get('simplegridtextoverlay') == 1 || $params->get(
                'nooverlaysimplemode'
            ) == 'yes'
        ) {
            $overlaytext = '<h5 class="card-title text-uppercase overlay-text -webkit-text-stroke" style="text-shadow: 2px 2px #000000;">' . $item->studytitle . '</h5>';
        }
        if ($params->get('nooverlaysimplemode') == 'no') {
            $overlaytext = '';
        }
        ?>
                        <article class="media__item__wrapper">
                            <div class="thumbnail text-center">
                                <div class="card overflow-hidden border-0 rounded-0 text-center text-white">
                                    <img src="<?php
                    echo $image; ?>" class="card-img rounded-0" alt="...">
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
<div class="row-fluid col-sm-12 pagination pagelinks" style="background-color: #A9A9A9;
    margin: 0 -5px;
    padding: 8px 8px;
    border: 1px solid #C5C1BE;
    position: relative;
    -webkit-border-radius: 9px;">
    <?php
    echo $this->pagination->getPageslinks(); ?>
</div>