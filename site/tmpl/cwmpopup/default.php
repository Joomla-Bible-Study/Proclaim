<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\View\Cwmpopup\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var HtmlView $this */

try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    die('Application not found');
}

$doc = $app->getDocument();

$backgroundColor = $this->params->get('popupbackground', 'black');
$style           = "body { background-color: $backgroundColor !important; padding:0 !important;}
	#all{background-color: $backgroundColor !important;}";

$doc->getWebAssetManager()->addInlineStyle($style);

// Normalize player value to integer for easier comparison
$playerParam = (int) $this->params->get('player');
$player      = $this->player;
?>
<div id="popupwindow" class="popupwindow">
    <div class="popuptitle">
        <p class="popuptitle"><?php echo $this->headertext; ?></p>
    </div>
    <?php
    // Internal Viewer or All Videos
    if (\in_array($player, [2, 3], true) || \in_array($playerParam, [2, 3], true)) {
        $mediaCode = $this->getMedia->getAVmediacode($this->media->mediacode, $this->media);
        echo HTMLHelper::_('content.prepare', $mediaCode);
    }

// HTML audio player / YouTube / Vimeo
if ($player === 1 || $player === 7 || $playerParam === 1) {
    $path = $this->path1;

    $width  = $this->playerwidth;
    $height = $this->playerheight;

    // Addon-owned rendering: resolve URL to addon for popup player
    $addon = CWMAddon::resolveForUrl($path);

    if ($addon) {
        echo $addon->renderPopupPlayer($path, $this->params, $width, $height);
    } else {
        echo '<audio controls autoplay><source src="' . $path . '" type="audio/mp3"></audio>';
    }
}

if ($player === 8) {
    echo stripslashes($this->params->get('mediacode'));
} elseif ($player === 0) {
    $app->redirect(Route::_($this->path1));
}
?>
    <div class="popupfooter">
        <?php
    // Social Networking
    if ($this->params->get('embedshare') !== 'FALSE') {
        try {
            echo $this->listing->getShare($this->path1, $this->media, $this->params);
        } catch (Exception $e) {
            // Ignore errors in share module
        }
    }
?>
        <p class="popupfooter">
           <?php echo $this->footertext; ?>
        </p>
    </div>
</div>
