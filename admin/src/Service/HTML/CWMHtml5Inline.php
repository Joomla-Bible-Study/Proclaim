<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Service\HTML;

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
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
        ?int $t = null
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
            !str_contains($media->path1, 'youtube.com')
            && !str_contains($media->path1, 'youtu.be')
            && !str_contains($media->path1, 'rtmp://')
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
        } elseif (isset($player->mp3, $player->playerwidth)) {
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

        if ($popup || $params->get('pcplaylist')) {
            if ($params->get('playerresponsive') !== 0) {
                $media->playerwidth = '100%';
                $render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:100%;\">";
            } else {
                $render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:"
                    . $media->playerwidth . "px; height:" . $height . "px;\">";
            }

            $popupMarg = $params->get('popupmargin', '50');
        }

        if ($popup || $params->get('pcplaylist')) {
            $render .= "</div>";
        }

        if (isset($media->headertext)) {
            $header = $media->headertext;
        } elseif ($params->get('pcplaylist')) {
            $header = $media->studytitle;
        } else {
            $header = $params->get('popuptitle', '');
            $header = str_replace('{{title}}', $media->studytitle, $header);
        }

        $chapters     = $media->params->get('chapters', []);
        $chaptersAttr = '';

        if (!empty($chapters)) {
            $chaptersAttr = " data-chapters='" . htmlspecialchars(json_encode($chapters), ENT_QUOTES, 'UTF-8') . "'";
        }

        $render .= '<div class="media playhit" data-id="' . (int) $media->id . '"' . $chaptersAttr . '>';

        // Build subtitle/caption track tags for HTML5 players
        $subtitleTracks = $media->params->get('subtitle_tracks', []);
        $trackTags      = '';

        foreach ($subtitleTracks as $track) {
            $track     = (object) $track;
            $trackTags .= '<track kind="' . htmlspecialchars($track->kind ?? 'captions', ENT_QUOTES, 'UTF-8') . '"'
                . ' src="' . htmlspecialchars($track->src ?? '', ENT_QUOTES, 'UTF-8') . '"'
                . ' srclang="' . htmlspecialchars($track->srclang ?? '', ENT_QUOTES, 'UTF-8') . '"'
                . ' label="' . htmlspecialchars($track->label ?? '', ENT_QUOTES, 'UTF-8') . '">';
        }

        # If MP3 media
        if (self::isMimeTypeAllowed($media->params->get('mime_type'), $audioFormats)) {
            $render .= '<audio controls>';
            $render .= '<source src="' . $media->path1 . '" type="' . $media->params->get('mime_type') . '">';
            $render .= $trackTags;
            $render .= 'Your browser does not support the audio element.';
            $render .= '</audio>';
        }

        if (self::isMimeTypeAllowed($media->params->get('mime_type'), $videoFormats)) {
            $render .= '<video width="' . $media->playerwidth . '" height="' . $height . '" controls>';
            $render .= '<source src="' . $media->path1 . '" type="' . $media->params->get('mime_type') . '">';
            $render .= $trackTags;
            $render .= 'Your browser does not support the video tag.';
            $render .= '</video>';
        }

        $render .= "</div>";

        // Interactive transcript panel — renders from the first caption/subtitle track
        if (!empty($subtitleTracks)) {
            $firstTrack    = (object) $subtitleTracks[0];
            $transcriptSrc = htmlspecialchars($firstTrack->src ?? '', ENT_QUOTES, 'UTF-8');

            if (!empty($transcriptSrc)) {
                $render .= '<div data-transcript-src="' . $transcriptSrc . '"></div>';
            }
        }

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
        return \in_array($mimeType, $mimeArray, true);
    }
}
