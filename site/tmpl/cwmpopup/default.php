<?php

/**
 * Popup window template for media playback.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\View\Cwmpopup\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

/** @var HtmlView $this */

$app = Factory::getApplication();
$wa  = $app->getDocument()->getWebAssetManager();

$backgroundColor = htmlspecialchars((string) $this->params->get('popupbackground', 'black'), ENT_QUOTES, 'UTF-8');
$wa->addInlineStyle(
    "body { background-color: {$backgroundColor} !important; padding: 0 !important; }"
    . " #all { background-color: {$backgroundColor} !important; }"
);
?>
<div id="popupwindow" class="com-proclaim popupwindow">
    <div class="popuptitle">
        <p class="popuptitle"><?php echo $this->headertext; ?></p>
    </div>

    <?php
    echo LayoutHelper::render('popup.player', [
        'player'   => $this->player,
        'path'     => $this->path1,
        'params'   => $this->params,
        'media'    => $this->media,
        'width'    => $this->playerwidth,
        'height'   => $this->playerheight,
        'getMedia' => $this->getMedia,
    ], JPATH_COMPONENT_SITE . '/layouts');
?>

    <div class="popupfooter">
        <?php if ((int) $this->params->get('embedshare', 1) > 0) : ?>
            <?php
        try {
            echo $this->listing->getShare($this->path1, $this->media, $this->params);
        } catch (\Exception $e) {
            // Share module errors should not break the popup
        }
            ?>
        <?php endif; ?>
        <p class="popupfooter"><?php echo $this->footertext; ?></p>
    </div>
</div>
