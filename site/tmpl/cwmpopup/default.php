<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Router\Route;

$style = 'body { background-color:' . $this->params->get('popupbackground', 'black') . ' !important; padding:0 !important;}
	#all{background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}';
try {
    $app = Factory::getApplication();
} catch (Exception $e) {
    die();
}
$doc   = $app->getDocument();
$doc->addStyleDeclaration($style);
$CWMedia = new Cwmmedia();

?>
<div id="popupwindow" class="popupwindow">
    <div class="popuptitle"><p class="popuptitle"><?php
            echo $this->headertext ?>
        </p>
    </div>
    <?php
    // Here is where we choose whether to use the Internal Viewer or All Videos
    if (
        $this->player === 2 || $this->player === 3 || $this->params->get('player') === "3" || $this->params->get('player') === "2"
    ) {
        $mediaCode = $this->getMedia->getAVmediacode($this->media->mediacode, $this->media);
        echo HtmlHelper::_('content.prepare', $mediaCode);
    }
    //We now use HTML audio player for playing mp3 files.
    if ($this->player === 1 || $this->player === 7 || $this->params->get('player') === "1") {
        $path = Cwmhelper::mediaBuildUrl(
            $this->media->sparams->get('path'),
            $this->params->get('filename'),
            $this->params,
            true
        );

        if (preg_match('(youtube.com|youtu.be)', $path) === 1) {
            echo '<iframe width="' . $this->params->get('player_width') . '" height="' . $this->params->get(
                'player_height'
            ) . '" src="' .
                $CWMedia->convertYoutube(
                    $path
                ) . '" style="border:0;" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        } elseif (preg_match('(vimeo.com)', $path) === 1) {
            echo '<iframe src="' . $CWMedia->convertVimeo($path) . '" width="' . $this->params->get(
                'player_width'
            ) . '" height="' .
                $this->params->get(
                    'player_height'
                ) . '" style="border:0;" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        } else {
            echo '<audio controls autoplay><source src="' . $path . '" type="audio/mp3"></audio>';
        }
    }

    if ($this->player === 8) {
        echo stripslashes($this->params->get('mediacode'));
    } elseif ($this->player === 0) {
        $app->redirect(Route::_($this->path1));
    }
    ?>
    <div class="popupfooter">
        <p class="popupfooter">
            <?php
            echo $this->footertext; ?>
        </p>
    </div>
</div>
