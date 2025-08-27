<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Service\HTML;

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proclaim Component HTML Helper
 *
 * @package    Proclaim.Admin
 * @since      10.0.0
 */
class CWMHtml5Inline
{
    /**
     * Render html for media presentation for Html 5 Player
     *
     * @param   object       $media   Media info
     * @param   Registry     $params  Params from the media have to be in object for do to protection.
     * @param   object       $player  To make player for audio like (MP3, M4A, etc.)
     * @param   bool         $popup   If from a popup
     * @param   ?int         $t       Template id.
     *
     * @return  string
     *
     * @since      10.0.0
     * @todo Need to clean up and verify all sanserif.
     */
    public static function render(
        object $media,
        $params,
        object $player,
        bool $popup = false,
        int $t = null
    ): string {
        $popupMarg = 0;

        // Used to set for MP3 and audio player look
        if (isset($player->mp3) && $player->mp3 === true) {
            $media->playerheight = 30;
        } else {
            $media->playerheight = $params->get('player_hight');
        }

        $media->path1 = Cwmhelper::mediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true);

        // Fall back check to see if player can play the media. If not will try and return a link to the file.
        $videoFormats = [
            // Video formats
            'mp4|m4v' => 'video/mp4',
            'ogv'     => 'video/ogg',
            'webm'    => 'video/webm',
        ];

        $audioFormats = [
            // Audio formats
            'acc'      => 'audio/acc',
            'flc'      => 'audio/flac',
            'm4a|m4b'  => 'audio/mpeg',
            'mp3'      => 'audio/mp3',
            'wav'      => 'audio/wav',
            'wav-xpn'  => 'audio/x-pn-wav',
            'ogg|oga'  => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'mka'      => 'audio/x-matroska',
        ];

        $acceptedFormats = array_merge($videoFormats, $audioFormats);

        if (
            !strpos($media->path1, 'youtube.com')
            && !strpos($media->path1, 'youtu.be')
            && !strpos($media->path1, 'rtmp://')
            && !self::isMimeTypeAllowed($media->params->get('mime_type'), $acceptedFormats)
        ) {
            return '<a href="' . $media->path1 . '"><img src="' . Uri::root() . $params->get(
                'media_image'
            ) . '" alt="' . $media->studytitle . '"/></a>';
        }

        if ($params->get('playerheight') < 55 && $params->get('playerheight') && !isset($player->mp3)) {
            $media->playerheight = 55;
        } elseif ($params->get('playerheight') && !isset($player->mp3)) {
            $media->playerheight = $params->get('playerheight');
        }

        if ($params->get('playerwidth') && !isset($player->mp3)) {
            $media->playerwidth = $params->get('playerwidth');
        } elseif (isset($player->mp3) && isset($player->playerwidth)) {
            $media->playerwidth = $player->playerwidth;
        } else {
            $media->playerwidth = $params->get('player_width');
        }

        if ($params->get('playervars')) {
            $media->extraparams = $params->get('playervars');
        }

        if ($params->get('altflashvars')) {
            $media->flashvars = $params->get('altflashvars');
        }

        $media->backcolor   = $params->get('backcolor', '0x287585');
        $media->frontcolor  = $params->get('frontcolor', '0xFFFFFF');
        $media->lightcolor  = $params->get('lightcolor', '0x000000');
        $media->screencolor = $params->get('screencolor', '0xFFFFFF');

        if ($params->get('autostart', 1) === 1) {
            $media->autostart = 'true';
        } else {
            $media->autostart = 'false';
        }

        if ($params->get('playeridlehide')) {
            $media->playeridlehide = 'true';
        } else {
            $media->playeridlehide = 'false';
        }

        if ($params->get('autostart') === 1) {
            $media->autostart = 'true';
        } elseif ($params->get('autostart') === 2) {
            $media->autostart = 'false';
        }

        // Calculate Height base off width for a 16:9 ratio.
        $render = "";
        $rat1   = 16;
        $rat2   = 9;

        $ratio  = $media->playerwidth / $rat1;
        $height = $ratio * $rat2;

        if ($popup) {
            if ($params->get('playerresponsive') !== 0) {
                $media->playerwidth = '100%';
                $render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:100%;\">";
            } else {
                $render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:"
                    . $media->playerwidth . "px; height:" . $height . "px;\">";
            }

            $popupMarg = $params->get('popupmargin', '50');
        }

        if ($params->get('media_popout_yes', true)) {
            $popoutText = $params->get('media_popout_text', Text::_('JBS_CMN_POPOUT'));
        } else {
            $popoutText = '';
        }

        if ($popup || $params->get('pcplaylist')) {
            $render .= "</div>";
        } elseif ($popoutText) {
            // Add space for a popup window
            $player->playerwidth += 20;
            $player->playerheight += $popupMarg;
            $render .= "<a href=\"#\" onclick=\"window.open('index.php?option=com_proclaim&amp;player="
                . $player->player . "&amp;view=cwmpopup&amp;t=" . $t . "&amp;mediaid=" . $media->id
                . "&amp;tmpl=component', 'newwindow', 'width="
                . $player->playerwidth . ",height=" .
                $player->playerheight . "'); return false\">" . $popoutText . "</a>";
        }

        if (isset($media->headertext)) {
            $header = $media->headertext;
        } elseif ($params->get('pcplaylist')) {
            $header = $media->studytitle;
        } else {
            $header = $params->get('popuptitle', '');
            $header = str_replace('{{title}}', $media->studytitle, $header);
        }

        $render .= "<div class=\"media\">";

        # If MP3 media
        if (self::isMimeTypeAllowed($media->params->get('mime_type'), $audioFormats)) {
            $render .= '<audio controls>';
            $render .= '<source src="' . $media->path1 . '" type="' . $media->params->get('mime_type') . '">';
            $render .= 'Your browser does not support the audio element.';
            $render .= '</audio>';
        }

        if (self::isMimeTypeAllowed($media->params->get('mime_type'), $videoFormats)) {
            $render .= '<video width="' . $media->playerwidth . '" height="' . $height . '" controls>';
            $render .= '<source src="' . $media->path1 . '" type="' . $media->params->get('mime_type') . '">';
            $render .= 'Your browser does not support the video tag.';
            $render .= '</video>';
        }

        $render .= "</div>";

        return $render;
    }

    /**
     * Search through Mime Type and see if it's allowed
     *
     * @param   string  $mimeType   Mime Type like: 'audio/mp3'
     * @param   array   $mimeArray  Array of Mime Types like ['mp3' => 'audio/mp3']
     *
     * @return bool
     *
     * @since 10.0.0
     */
    private static function isMimeTypeAllowed(string $mimeType, array $mimeArray): bool
    {
        return in_array($mimeType, $mimeArray, true);
    }
}
