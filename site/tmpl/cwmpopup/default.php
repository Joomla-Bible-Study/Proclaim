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
use CWM\Component\Proclaim\Administrator\Helper\Cwmjwplayer;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Router\Route;

$style = 'body { background-color:' . $this->params->get('popupbackground', 'black') . ' !important; padding:0 !important;}
	#all{background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}';
$app   = Factory::getApplication();
$doc   = $app->getDocument();
$doc->addStyleDeclaration($style);
$CWMedia = new Cwmmedia();

// @todo need to move some of the is build process into the media helper. BCC

?>
<div id="popupwindow" class="popupwindow">
    <div class="popuptitle"><p class="popuptitle"><?php
            echo $this->headertext ?>
        </p>
    </div>
    <?php
    // Here is where we choose whether to use the Internal Viewer or All Videos
    if (
        $this->params->get('player') === "3" || $this->player === 3 || $this->params->get(
            'player'
        ) === "2" || $this->player === 2
    ) {
        $mediacode = $this->getMedia->getAVmediacode($this->media->mediacode, $this->media);
        echo HtmlHelper::_('content.prepare', $mediacode);
    }
    // Legacy Player (since JBS 6.2.2) is now deprecated and will be rendered with JWPlayer.
    if ($this->params->get('player') === "1" || $this->player === 1 || $this->player === 7) {
        $player = new stdClass();
        $player->mp3 = $this->player === 7;
        try {
            Cwmjwplayer::framework();
        } catch (Exception $e) {
        }
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
            echo Cwmjwplayer::render($this->media, $this->params, true, $player);
        }
    }

    if ($this->player === 8) {
        echo stripslashes($this->params->get('mediacode'));
    }

    if ($this->player === 0) {
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
