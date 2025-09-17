<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Service\HTML\CWMFancyBox;
use CWM\Component\Proclaim\Administrator\Service\HTML\CWMHtml5Inline;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Joomla! Bible Study Media class.
 *
 * @since  7.0.0
 */
class Cwmmedia
{
    /**
     * @var int File Size
     *
     * @since    7.0
     */
    private int $fsize = 0;

    /**
     * @param   string  $url  url to process
     *
     * @return bool
     *
     * @since 10.0.0
     */
    public static function isExternal(string $url): bool
    {
        // Check if the url has a website string as some time it's just a path to a local file.
        if (strpos($url, "http") || strpos($url, "https") || strpos($url, "//")) {
            $components = parse_url($url);
            $root       = parse_url(Uri::root());

            // We will treat url like '/relative.php' as relative
            if (empty($components['host'])) {
                return false;
            }

            // Url host looks exactly like the local host
            if (strcasecmp($components['host'], $root['host']) === 0) {
                return false;
            }

            // Check if the url host is a subdomain
            return strripos($components['host'], $root['host']) !== strlen($components['host']) - strlen($root['host']);
        }

        return false;
    }

    /**
     * Return Fluid Media row
     *
     * @param   Object                   $media     Media info
     * @param   Registry                 $params    Params
     * @param   CwmtemplateTable|Object  $template  Template Table
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getFluidMedia(object $media, Registry $params, $template): ?string
    {
        $mediafile = null;
        $filesize  = 0;

        if (isset($media->smedia)) {
            // $smedia are the media settings for each server
            $registory = new Registry();
            $registory->loadString($media->smedia);
            $media->smedia = $registory;
        }

        // $params are the individual params for the media file record
        $registory = new Registry();
        $registory->loadString($media->params);
        $media->params = $registory;

        // Ssparams are the server parameters
        $registory = new Registry();
        $registory->loadString($media->sparams);
        $media->sparams = $registory;

        if ($media->params->get('media_use_button_icon') === '-1') {
            $imageparams = $media->smedia;
        } else {
            $imageparams = $media->params;
        }

        if (
            $imageparams->get('media_use_button_icon') >= 1 || (int)$params->get('simple_mode') === 1 || $params->get(
                'sermonstemplate'
            ) === 'easy'
        ) {
            $image = $this->mediaButton($imageparams, $params, $media->params);
        } else {
            $mediaImage = (string)$imageparams->get('media_image');
            $image      = $this->useJImage(
                $mediaImage,
                $media->params->get('media_button_text', $params->get('download_button_text', 'Audio'))
            );
        }

        // New Podcast Playlist cast Player code override option.
        $player       = $this->getPlayerAttributes($params, $media);
        $playerCode   = $this->getPlayerCode($params, $player, $image, $media);
        $downloadLink = $this->getFluidDownloadLink($media, $params, $template);

        $link_type = 0;

        if ($media->params->get('link_type') === '0' || $media->params->get('link_type')) {
            $link_type = $media->params->get('link_type', 3);
        } elseif ($params->get('download_show') !== '0' && !$media->params->get('link_type')) {
            $link_type = 3;
        }

        if ($params->get('simple_mode') === '1' || $params->get('sermonstemplate') === 'easy') {
            $link_type = 3;
        }

        // Used to override everything if used for use of the Podcast playlist system..
        if ($params->get('pcplaylist')) {
            $link_type = 0;
        }

        if ($link_type < 2 && $params->get('show_filesize') > 0) {
            $file_size = (int)$media->params->get('size', 0);

            if (!$file_size && $link_type !== 0) {
                $file_size = Cwmhelper::getRemoteFileSize(
                    Cwmhelper::mediaBuildUrl(
                        $media->sparams->get('path'),
                        $media->params->get('filename'),
                        $params,
                        true
                    )
                );
                Cwmhelper::setFileSize($media->id, $file_size);
            }

            if ($file_size > 1) {
                $file_size = $this->convertFileSize($file_size, $params, $media);
                $filesize  = '<span class="JBSMFilesize" style="display:inline;padding-left: 5px;">' .
                $file_size . '</span>';
            }
        }

        switch ($link_type) {
            case 0:
                $mediafile = $playerCode;
                break;

            case 1:
                if ($downloadLink) {
                    if ($filesize > 0) {
                        $mediafile = $playerCode . '<div class="col">' . $downloadLink . $filesize . '</div>';
                    } else {
                        $mediafile = $playerCode . '<div class="col">' . $downloadLink .  '</div>';
                    }
                } else {
                    $mediafile = $playerCode;
                }
                break;

            case 2:
                $mediafile = $downloadLink;
                break;

            case 3:
                $mediafile = $playerCode . $downloadLink;
                break;
        }

        return $mediafile;
    }

    /**
     * Used to get the button and/or icon for the image
     *
     * @param   Registry  $imageparams  ?
     * @param   Registry  $params       ?
     * @param   object    $media        Media Object
     *
     * @return ?string
     *
     * @since 9.0.0
     */
    public function mediaButton(Registry $imageparams, Registry $params, object $media): ?string
    {
        $mediaimage = null;
        $button     = $imageparams->get('media_button_type', 'btn-link');
        $buttontext = $imageparams->get('media_button_text', 'Audio');
        $textsize   = $imageparams->get('media_icon_text_size', '24');

        if ($imageparams->get('media_button_color')) {
            $color = 'style="background-color:' . $imageparams->get('media_button_color') . ';"';
        } else {
            $color = '#1e3e48';
        }

        switch ($imageparams->get('media_use_button_icon')) {
            case 1:
                // Button only
                $mediaimage = '<div  class="btn ' . $button . ' title="' . $buttontext . '" ' . $color . '>' . $buttontext . '</div>';
                break;
            case 2:
                // Button and icon
                if ($imageparams->get('media_icon_type') === '1') {
                    $icon = $imageparams->get('media_custom_icon');
                } else {
                    $icon = $imageparams->get('media_icon_type', 'fas fa-play');

                    // Check for fa youtube tag, change to fab
                    $icon = str_replace('fa fa-youtube', 'fab fa-youtube', $icon);
                }

                $mediaimage = '<div  type="button" class="btn ' . $button . '" title="' . $buttontext . '" ' . $color . '><span class="' .
                    $icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span></div>';
                break;
            case 3:
                // Icon only
                if ($imageparams->get('media_icon_type') === '1') {
                    $icon = $imageparams->get('media_custom_icon');
                } else {
                    $icon = $imageparams->get('media_icon_type', 'fas fa-play');

                    // Check for fa-youtube tag, change to fab
                    $icon = str_replace('fa fa-youtube', 'fab fa-youtube', $icon);
                }

                $mediaimage = '<span class="' . $icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span>';
                break;
        }

        if ($params->get('simple_mode') === '1' || $params->get('sermonstemplate') === 'easy') {
            $filename = $media->get('filename');

            switch ($filename) {
                case preg_match('(youtube.com|youtu.be)', $filename) === 1:
                    $mediaimage = '<span class="fab fa-youtube" title="YouTube" style="font-size:24px;"></span>';
                    break;

                case preg_match('(pdf|PDF)', $filename) === 1:
                    $mediaimage = '<span class="fas fa-file-pdf" title="PDF" style="font-size:24px;"></span>';
                    break;

                case preg_match('(mp3|MP3)', $filename) === 1:
                    $mediaimage = '<span class="fas fa-play" title="Audio" style="font-size:24px;"></span>';
                    break;

                case preg_match('(mp4|MP4)', $filename) === 1:
                case preg_match('(m4v|M4V)', $filename) === 1:
                    $mediaimage = '<span class="fas fa-television" title="Video" style="font-size:24px;"></span>';
                    break;

                case preg_match('(pptx|ppt|PPTX|PPT)', $filename) === 1:
                    $mediaimage = '<span class="fas fa-file-powerpoint" title="Powerpoint" style="font-size:24px;"></span>';
                    break;
                case preg_match('(docx|DOCX)', $filename) === 1:
                    $mediaimage = '<span class="fas fa-word" title="Word" style="font-size:24px;"></span>';
                    break;
                default:
                    $mediaimage = '<span class="fas fa-file" title="' . $filename . '" style="font-size:24px;"></span>';
                    break;
            }
        }

        return $mediaimage;
    }

    /**
     * Use JImage to create images
     *
     * @param   string  $path  Path to file
     * @param   string  $alt   Accessibility string
     *
     * @return string
     *
     * @since 9.0.0
     */
    public function useJImage(string $path, string $alt): string
    {
        if (!$path) {
            return $alt;
        }

        try {
            $return = Image::getImageFileProperties($path);
        } catch (\Exception $e) {
            return $alt;
        }

        return '<img src="' . Uri::base() . $path . '" alt="' . $alt . '" ' . $return->attributes . ' >';
    }

    /**
     * Set up Player Attributes
     *
     * @param   Registry  $params  Params
     * @param   object    $media   Media info
     *
     * @return object
     *
     * @since 9.0.0
     */
    public function getPlayerAttributes(Registry $params, object $media): object
    {
        $player               = new \stdClass();
        $player->playerwidth  = $params->get('player_width');
        $player->playerheight = $params->get('player_height');

        if ($media->params->get('playerheight')) {
            $player->playerheight = $media->params->get('playerheight');
        }

        if ($media->params->get('playerwidth')) {
            $player->playerwidth = $media->params->get('playerwidth');
        }

        /**
         * @desc Players - from Template:
         * First, we check to see if in the template the user has set to use the internal player for all media. This can be overridden by itemparams
         * popuptype = whether AVR should be window or lightbox (handled in avr code)
         * internal_popup = whether direct or internal player should be popup/new window or inline
         * From media file:
         * player 0 = direct, 1 = internal, 2 = AVR, 3 = AV 7 = legacy internal player (from JBS 6.2.2)
         * internal_popup 0 = inline, 1 = popup, 2 = global settings
         *
         * Get the $player->player: 0 = direct, 1 = internal, 2 = AVR (no longer supported),
         * 3 = All Videos or JPlayer, 4 = Docman, 5 = article, 6 = Virtuemart, 7 = legacy player, 8 = embed code
         * $player->type 0 = inline, 1 = popup/new window 3 = Use Global Settings (from params)
         * In 6.2.3 we changed inline = 2
         */
        $player->player   = 0;
        $item_mediaplayer = (int)$media->params->get('player');

        // Check to see if the item player is set to 100 - that means use global settings which comes from $params
        if ($item_mediaplayer === 100) {
            // Player is set from the $params
            $player->player = $params->get('media_player', '0');
        } elseif ($params->get('pcplaylist')) {
            $player->player = 7;
        } elseif ($media->params->get('player', null) !== null) {
            $player->player = (int)$media->params->get('player');
        } else {
            $player->player = (int)$params->get('player', 0);
        }

        if ($player->player === 3) {
            $player->player = 2;
        }

        if ((int)$params->get('docMan_id') !== 0) {
            $player->player = 4;
        }

        if ($params->get('article_id') > 0) {
            $player->player = 5;
        }

        if ($params->get('virtueMart_id') > 0) {
            $player->player = 6;
        }

        $player->type = 1;

        // This is the global parameter set in Template Display settings
        $param_playertype = $params->get('internal_popup', 1);

        if ($params->get('pcplaylist', false)) {
            $item_playertype = 2;
        } else {
            $item_playertype = $params->get('popup');
        }

        if ($param_playertype && !$media->params->get('popup') && !$params->get('pcplaylist', false)) {
            $player->type = $param_playertype;
        } else {
            $player->type = $media->params->get('popup');
        }

        switch ($item_playertype) {
            case 3:
                $player->type = $param_playertype;
                break;

            case 2:
                $player->type = 2;
                break;

            case 1:
                $player->type = 1;
                break;
        }

        return $player;
    }

    /**
     * Setup Player Code.
     *
     * @param   Registry  $params  Params are the merged of system and items.
     * @param   object    $player  Player code
     * @param   String    $image   Image info
     * @param   object    $media   Media
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getPlayerCode(Registry $params, object $player, string $image, object $media): string
    {
        // Merging the item params into the global.
        $params = clone $params;
        $params->merge($media->params);

        $input    = new Input();
        $template = $input->getInt('t', '1');
        $youtube  = new CWMAddonYoutube();

        // Here we get more information about the particular media file
        $filesize = $this->getFluidFilesize($media, $params);

        $path = Cwmhelper::mediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true);

        switch ($player->player) {
            case 0: // Direct
                $playercode = '';

                switch ($player->type) {
                    case 2: // New window - popup code added here because new window code does not work (Tom 10-12-2022)
                        $return     = base64_encode($path);
                        $playercode = '<a href="javascript:;" onclick="window.open(\'index.php?option=com_proclaim&amp;' .
                            'task=Cwmsermons.playHit&amp;return=' . $return . '&amp;' . Session::getFormToken(
                            ) . '=1\')" title="' .
                            $media->params->get("media_button_text") . ' - ' . $media->comment . ' '
                            . $filesize . '">' . $image . '</a>';
                        break;

                    case 3: // Squeezebox view
                        return $this->renderSB($media, $params, $player, $image, $path, true);

                    case 1: // Popup window
                        $playercode = "<a style='color: #5F5A58;' href=\"javascript:;\"" .
                            " onclick=\"window.open('index.php?option=com_proclaim&amp;player="
                            . $params->toObject()->player .
                            "&amp;view=cwmpopup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" .
                            $player->playerwidth . ",height=" . $player->playerheight . "'); return false\"  class=\"jbsmplayerlink\">"
                            . $image . "</a>";
                        break;
                }

                return $playercode;

            case 7:
            case 1: // Internal
                $playercode = '';

                switch ($player->type) {
                    case 3: // Squeezebox view
                        return $this->renderSB($media, $params, $player, $image, $path);

                    case 2: // Inline
                        CWMFancyBox::framework();

                        if ($player->player === 7) {
                            $player->playerheight    = '40';
                            $player->boxplayerheight = '40';
                            $player->mp3             = true;

                            if ($player->playerwidth <= '259') {
                                $player->playerwidth = null;
                            }
                        }

                        if (preg_match('(youtube.com|youtu.be)', $path) === 1) {
                            $playercode = '<iframe class="playhit" data-id="' . $media->id . '" width="' . $player->playerwidth . '" height="' .
                                $player->playerheight . '" src="' . $youtube->convertYoutube($path) .
                                '" allow="autoplay; encrypted-media" allowfullscreen style="border: none"></iframe>';
                        } elseif (preg_match('(vimeo.com)', $path) === 1) {
                            $playercode = '<iframe class="playhit" data-id="' . $media->id . '" src="' . $this->convertVimeo(
                                $path
                            ) .
                                '" width="' . $player->playerwidth . '" height="' . $player->playerheight .
                                '" webkitallowfullscreen mozallowfullscreen allowfullscreen style="border: none"></iframe>';
                        } else {
                            $playercode = CWMHtml5Inline::render($media, $params, $player, false, $template);
                        }

                        break;

                    case 1: // Popup
                        // Add space for a popup window

                        $diff                 = $params->get('player_width') - $params->get('playerwidth');
                        $player->playerwidth += abs($diff) + 10;
                        $player->playerheight += $params->get('popupmargin', '50');
                        $playercode           = "<a style='color: #5F5A58;' href=\"javascript:;\"" .
                            " onclick=\"window.open('index.php?option=com_proclaim&amp;player="
                            . $player->player
                            . "&amp;view=cwmpopup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow', 'width="
                            . $player->playerwidth . ", height=" .
                            $player->playerheight . "'); return false\" class=\"jbsmplayerlink\">" . $image . "</a>";
                        break;
                }

                return $playercode;

            case 2: // All Videos Reloaded
            case 3:
                $playercode = '';

                switch ($player->type) {
                    case 1: // This goes to the popup view
                        $playercode = "<a style='color: #5F5A58;' href=\"javascript:;\" onclick=\"window.open('index.php?option=com_proclaim"
                            . "&amp;view=cwmpopup&amp;player=3&amp;t=" . $template .
                            "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height="
                            . $player->playerheight . "'); return false\"  class=\"jbsmplayerlink\">" . $image . "</a>";
                        break;

                    case 2: // This plays the video inline
                        $mediacode  = $this->getAVmediacode($media->mediacode, $media);
                        $playercode = HtmlHelper::_('content.prepare', $mediacode);
                        break;
                }

                return $playercode;

            case 4: // Docman
                return $this->getDocman($media, $image);

            case 5: // Article
                return $this->getArticle($media, $image);

            case 6: // Virtuemart
                return $this->getVirtuemart($media, $image);

            case 8: // Embed code
                return "<a style='color: #5F5A58;' href=\"javascript:;\" onclick=\"window.open('index.php?option=com_proclaim"
                    . "&amp;view=cwmpopup&amp;player=8&amp;t=" . $template .
                    "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height="
                    . $player->playerheight . "'); return false\">" . $image . "</a>";
        }

        return false;
    }

    /**
     * return $table
     *
     * @param   Object    $media   Media info
     * @param   Registry  $params  Params
     *
     * @return string file size with format
     *
     * @since 9.0.0
     */
    public function getFluidFilesize(object $media, Registry $params): string
    {
        $filesize = 0;

        // Check to see if we need to look up file size or not. By looking at if download like is set.
        if ($media->params->get('link_type') === '0') {
            $this->fsize = $filesize;

            return $this->fsize;
        }

        $file_size = (int) $media->params->get('size', '0');

        if ($file_size === 0) {
            $file_size = Cwmhelper::getRemoteFileSize(
                Cwmhelper::mediaBuildUrl(
                    $media->sparams->get('path'),
                    $params->get('filename'),
                    $params,
                    true
                )
            );
            Cwmhelper::setFileSize($media->id, $file_size);
        }

        if ($file_size > 1) {
            $file_size = $this->convertFileSize($file_size, $params, $media);
        }

        return (string) $file_size;
    }

    /**
     * Build File Size String Display
     *
     * @param   int       $file_size  Size of the file
     * @param   Registry  $params     Registry of parameters
     * @param   Object    $media      Media info
     *
     * @return string
     *
     * @since 10.0.0
     */
    public function convertFileSize(int $file_size, Registry $params, object $media): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $file_size = max($file_size, 0);
        $pow       = $file_size > 0 ? floor(log($file_size, 1024)) : 0;
        $pow       = min($pow, count($units) - 1);

        $file_size /= 1024 ** $pow;

        $file_size_results = round($file_size, 2) . ' ' . $units[$pow];

        switch ($params->get('show_filesize')) {
            case 2:
                $file_size_results = $media->comment;
                break;
            case 3:
                if ($media->comment) {
                    $file_size_results = $media->comment;
                }
                break;
        }

        return $file_size_results;
    }

    /**
     * Render Squeezebox
     *
     * @param   object    $media   Media
     * @param   Registry  $params  Params.
     * @param   object    $player  Player settings
     * @param   string    $image   The image
     * @param   string    $path    The path to the media
     * @param   bool      $direct  If coming from Direct
     *
     * @return string
     *
     * @since 9.1.2
     */
    public function renderSB(
        object $media,
        Registry $params,
        object $player,
        string $image,
        string $path,
        bool $direct = false
    ): string {
        CWMFancyBox::framework();

        if ($player->player === '7' && !$direct) {
            $player->playerheight = '40';
        }

        if ($params->get('media_popout_yes', true)) {
            $popout = $params->get('media_popout_text', Text::_('JBS_CMN_POPOUT'));
        } else {
            $popout = '';
        }

        if (preg_match('(youtube.com|youtu.be|vimeo.com)', $path) === 1) {
            $path = (new CWMAddonYoutube())->convertYoutube($path);
            $data = '<a data-fancybox="video-gallery" class="fancybox_player playhit" data-id="' . $media->id . '" aria-hidden="false" data-src="' . $path . '" data-options=\'{"autoplay" : "' .
                (int)$params->get('autostart', false) . '", "controls" : "' . (int)$params->get('controls') .
                '", "caption" : "' . $media->studytitle . ' - ' .
                $media->teachername . '"}\'  href="javascript:;">' . $image . '</a>';
            return $data;
        }

        return '<a data-src="' . $path . '" data-id="' . $media->id . '" id="' . $media->id . '" title="' . $params->get(
            'filename'
        ) .
            '" data-fancybox class="fancybox_player hitplay" potext="' . $popout . '" ptype="' . $player->player .
            '" pwidth="' . $player->playerwidth . '" pheight="' .
            $player->playerheight . '" autostart="' . $params->get('autostart', false) . '" controls="' .
            $params->get('controls') . '"" data-image="' . $params->get('jwplayer_image') . '" data-mute="' .
            $params->get('jwplayer_mute') . '" data-logo="' . $params->get('jwplayer_logo') . '" data-logolink="' .
            $params->get('jwplayer_logolink', Uri::base()) . '">' .
            $image . '</a>';
    }

    /**
     * Vimeo url to embed.
     *
     * @param   string  $string  Vimeo url to transform.
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function convertVimeo(string $string): string
    {
        return (string) preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*viemo.com\/([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "//player.vimeo.com/video/$2",
            $string
        );
    }

    /**
     * Return AVMedia Code.
     *
     * @param   string  $mediacode  Media string
     * @param   object  $media      Media info
     *
     * @return string
     *
     * @since 9.0.0
     */
    public function getAVmediacode(string $mediacode, object $media): string
    {
        $bracketpos   = strpos($mediacode, '}');
        $bracketend   = strpos($mediacode, '{', $bracketpos);
        $dashposition = strpos($mediacode, '-', $bracketpos);
        $isonlydash   = substr_count($mediacode, '}-{');

        if ($isonlydash) {
            $mediacode = substr_replace(
                $mediacode,
                'https://' . Cwmhelper::mediaBuildUrl($media->spath, $media->filename, null),
                $dashposition,
                1
            );
        } elseif ($dashposition) {
            $mediacode = substr_replace(
                $mediacode,
                Cwmhelper::mediaBuildUrl($media->spath, $media->filename, null),
                $bracketend - 1,
                1
            );
        }

        return $mediacode;
    }

    /**
     * Return Docman Media
     *
     * @param   object  $media  Media
     * @param   string  $image  Image
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getDocman(object $media, string $image): string
    {
        return '<a href="index.php?option=com_docman&amp;view=document&amp;slug=' .
            $media->docMan_id . '" alt="' . $media->malttext . ' - ' . $media->comment .
            '" target="' . $media->special . '">' . $image . '</a>';
    }

    /**
     * Return Articles.
     *
     * @param   object  $media  Media
     * @param   string  $image  Image
     *
     * @return string
     *
     * @since 9.0.0
     */
    public function getArticle(object $media, string $image): string
    {
        return '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
		target="' . $media->special . '">' . $image . '</a>';
    }

    /**
     * Set up Virtumart if Vertumart is installed.
     *
     * @param   object  $media  Media
     * @param   string  $image  Image
     *
     * @return string
     *
     * @since 9.0.0
     */
    public function getVirtuemart(object $media, string $image): string
    {
        return '<a class="playhit" data-id="' . $media->id
            . '" href="index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' .
            $media->virtueMart_id . '" target="' . $media->special .
            '">' . $image . '</a>';
    }

    /**
     * Return download link
     *
     * @param   Object                   $media     Media
     * @param   Registry                 $params    Params
     * @param   CwmtemplateTable|Object  $template  Template ID
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getFluidDownloadLink(object $media, Registry $params, $template): string
    {
        // Remove download form Youtube links.
        $filename  = $media->params->get('filename');
        $link_type = 0;

        if (substr_count($filename, 'youtube') || substr_count($filename, 'youtu.be')) {
            return '';
        }

        $downloadLink = '';

        if (
            $params->get('download_use_button_icon') >= 2 && ($params->get('simple_mode') === '1' || $params->get(
                'sermonstemplate'
            ) === 'easy')
        ) {
            $download_image = $this->downloadButton($params);
        } elseif ($params->get('default_download_image')) {
            $d_image        = $params->get('default_download_image');
            $download_image = $this->useJImage($d_image, Text::_('JBS_MED_DOWNLOAD'));
        } else {
            $download_image = $this->downloadButton($params);
        }

        if ($media->params->get('link_type')) {
            $link_type = $media->params->get('link_type');
        }

        if (
            ($params->get('download_show')
                && (!$media->params->get('link_type')))
        ) {
            $link_type = 2;
        }

        if ($link_type > 0) {
            $compat_mode = (int)$params->get('compat_mode');

            if ($compat_mode === 0) {
                $downloadLink = '<a style="color: #5F5A58;" href="index.php?option=com_proclaim&amp;view=Cwmsermon&amp;id=' .
                    $media->study_id . '&amp;mid=' . $media->id . '&amp;task=Cwmsermon.download">' . $download_image . '</a>';
            } else {
                $url = Cwmhelper::mediaBuildUrl(
                    $media->sparams->get('path'),
                    $media->params->get('filename'),
                    $params,
                    true
                );

                if ($media->params->get('size') === '0') {
                    $size = Cwmhelper::getRemoteFileSize($url);
                    Cwmhelper::setFileSize($media->id, $size);
                } else {
                    $size = $media->params->get('size');
                }

                $url = Cwmhelper::removeHttp($url);

                $downloadLink = '<a style="color: #5F5A58;" href="https://christianwebministries.org/router.php?file=' .
                    $url . '&amp;size=' . $size . '">';
            }

            // Check to see if they want to use a popup
            $app   = Factory::getApplication();
            $input = $app->input;
            $opt   = $input->get('option');

            if (($opt === 'com_proclaim') && $params->get('useterms') > 0) {
                $downloadLink = '<a style="color: #5F5A58;" href="#modal-test-modal" data-bs-toggle="modal"' .
                    'class="btn btn-default btn-small btn-sm">' . $download_image . '</a>';
                $modalParams  = [
                    'title'       => Text::_('JBS_TERMS_TITLE'),
                    'closeButton' => true,
                    'height'      => '300px',
                    'width'       => '300px',
                    'backdrop'    => 'static',
                    'keyboard'    => true,
                    'modalWidth'  => 30,
                    'bodyHeight'  => 30,
                    'footer'      => '<div class="alert alert-info">' . Text::_('JBS_TERMS_FOOTER') . '</div>',
                ];

                $modalBody = '<div class="alert alert-success">' . $params->get('terms') .
                    '<a style="color: #5F5A58;" href="index.php?option=com_proclaim&task=Cwmsermons.download&id=' .
                    $media->study_id . '&mid=' . $media->id . '">'
                    . Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a></div>';

                $downloadLink .= HTMLHelper::_('bootstrap.renderModal', 'modal-test-modal', $modalParams, $modalBody);
            }
        }

        return $downloadLink;
    }

    /**
     * Used to get the button and/or icon for the image
     *
     * @param   Registry  $download  ?
     *
     * @return ?string
     *
     * @since 9.0.0
     */
    public function downloadButton(Registry $download): ?string
    {

        $downloadImage = null;
        $button        = $download->get('download_button_type', 'btn-outline-primary');
        $buttonText    = $download->get('download_button_text', 'Audio');
        $textSize      = $download->get('download_icon_text_size', '24');

        if ($download->get('download_button_color')) {
            $color = 'style="background-color:' . $download->get('download_button_color') . ';"';
        } else {
            $color = '';
        }

        switch ($download->get('download_use_button_icon')) {
            case 2:
                // Button only
                $downloadImage = '<button class="btn ' . $button . '" title="' . $buttonText . '" ' . $color . '>' . $buttonText . '</button>';
                break;
            case 3:
                // Button and icon
                if ($download->get('download_icon_type') === '1') {
                    $icon = $download->get('download_custom_icon');
                } else {
                    $icon = $download->get('download_icon_type', 'icon-play');
                }

                $downloadImage = '<button class="btn ' . $button . '" title="' . $buttonText . '" ' . $color . '><span class="' .
                    $icon . '" title="' . $buttonText . '" style="font-size:' . $textSize . 'px;"></span></button>';
                break;
            case 4:
                // Icon only
                if ($download->get('download_icon_type') === '1') {
                    $icon = $download->get('download_custom_icon');
                } else {
                    $icon = $download->get('download_icon_type', 'icon-play');
                }

                $downloadImage = '<span class="' . $icon . '" title="' . $buttonText . '" style="font-size:' . $textSize . 'px;"></span>';
                break;
        }

        if ($download->get('simple_mode') === '1' || $download->get('sermonstemplate') === 'easy') {
            $downloadImage = '<span class="fas fa-chevron-circle-down" title="download" style="font-size: 24px;"></span>';
        }

        return $downloadImage;
    }

    /**
     * Update Hit count for plays.
     *
     * @param   int  $id  ID to apply the hit to.
     *
     * @return bool
     *
     * @since 9.0.0
     */
    public function hitPlay(int $id): bool
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update('#__bsms_mediafiles')
            ->set('plays = plays + 1')
            ->where('id = ' . $db->q($id));
        $db->setQuery($query);

        if ($db->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Get Media info Row2
     *
     * @param   int  $id  ID of Row
     *
     * @return object|bool
     *
     * @since 9.0.0
     */
    public function getMediaRows2(int $id): object|bool
    {
        // We use this for the popup view because it relies on the media file's id rather than the study_id field above
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            '#__bsms_mediafiles.*, #__bsms_servers.params AS sparams,'
            . ' s.studyintro, s.series_id, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin,'
            . ' s.verse_end, t.teachername, t.teacher_thumbnail, t.teacher_image, t.thumb, t.image, t.id as tid, s.id as sid, s.studyintro,'
            . ' se.id as seriesid, se.series_text, se.series_thumbnail'
        )
            ->from('#__bsms_mediafiles')
            ->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)')
            ->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)')
            ->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)')
            ->leftJoin('#__bsms_series AS se ON (s.series_id = se.id)')
            ->where('#__bsms_mediafiles.id = ' . (int)$id)
            ->where('#__bsms_mediafiles.published = ' . 1)
            ->where(
                '#__bsms_mediafiles.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->q(
                    '*'
                ) . ')'
            )
            ->order('ordering asc');
        $db->setQuery($query);
        $media = $db->loadObject();

        if ($media) {
            $reg = new Registry();
            $reg->loadString($media->sparams);
            $params = $reg->toObject();

            if (isset($params->path)) {
                $media->spath = $params->path;
            } else {
                $media->spath = '';
            }

            return $media;
        }

        return false;
    }

    /**
     * List of MimeTypes Supported
     *
     * @return array
     *
     * @since 9.0.12
     */
    public function getMimetypes(): array
    {
        return [
            // Image formats
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif'          => 'image/gif',
            'png'          => 'image/png',
            'bmp'          => 'image/bmp',
            'tif|tiff'     => 'image/tiff',
            'ico'          => 'image/x-icon',

            // Video formats
            'asf|asx'      => 'video/x-ms-asf',
            'wmv'          => 'video/x-ms-wmv',
            'wmx'          => 'video/x-ms-wmx',
            'wm'           => 'video/x-ms-wm',
            'avi'          => 'video/avi',
            'divx'         => 'video/divx',
            'flv'          => 'video/x-flv',
            'mov|qt'       => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v'      => 'video/mp4',
            'ogv'          => 'video/ogg',
            'webm'         => 'video/webm',
            'mkv'          => 'video/x-matroska',

            // Text formats
            'txt|asc|c|cc|h' => 'text/plain',
            'csv'            => 'text/csv',
            'tsv'            => 'text/tab-separated-values',
            'ics'            => 'text/calendar',
            'rtx'            => 'text/richtext',
            'css'            => 'text/css',
            'htm|html'       => 'text/html',

            // Audio formats
            'm4a|m4b'  => 'audio/mpeg',
            'mp3'      => 'audio/mp3',
            'ra|ram'   => 'audio/x-realaudio',
            'wav'      => 'audio/wav',
            'ogg|oga'  => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'wma'      => 'audio/x-ms-wma',
            'wax'      => 'audio/x-ms-wax',
            'mka'      => 'audio/x-matroska',

            // Misc application formats
            'rtf'     => 'application/rtf',
            'js'      => 'application/javascript',
            'pdf'     => 'application/pdf',
            'swf'     => 'application/x-shockwave-flash',
            'class'   => 'application/java',
            'tar'     => 'application/x-tar',
            'zip'     => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar'     => 'application/rar',
            '7z'      => 'application/x-7z-compressed',
            'exe'     => 'application/x-msdownload',

            // MS Office formats
            'doc'                          => 'application/msword',
            'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
            'wri'                          => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
            'mdb'                          => 'application/vnd.ms-access',
            'mpp'                          => 'application/vnd.ms-project',
            'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',

            // OpenOffice formats
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',

            // WordPerfect formats
            'wp|wpd' => 'application/wordperfect',

            // Apple formats
            'key'     => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages'   => 'application/vnd.apple.pages',
        ];
    }

    /**
     * List of Icons Supported
     *
     * @return array
     *
     * @since 9.1.3
     */
    public function getIcons(): array
    {
        return [
            'JBS_MED_PLAY'      => 'fas fa-play',
            'JBS_MED_YOUTUBE'   => 'fab fa-youtube',
            'JBS_MED_VIDEO'     => 'fas fa-video',
            'JBS_MED_BROADCAST' => 'fas fa-tv',
            'JBS_MED_FILE'      => 'fas fa-file',
            'JBS_MED_FILE_PDF'  => 'fas fa-file-pdf',
            'JBS_MED_VIMEO'     => 'fab fa-vimeo',
            'JBS_MED_CUSTOM'    => '1',
        ];
    }

    /**
     * @param   string  $path
     *
     * @return string
     *
     * @since 10.0.0
     */
    public function convertYoutube(string $path): string
    {
        return $this->ensureHttpJoomla((new CWMAddonYoutube())->convertYoutube($path));
    }

    public function ensureHttpJoomla($url)
    {
        $uri = new Uri($url);
        if (!$uri->getScheme()) {
            $url = "https:" . $url; // Default to HTTPS
        }
        return $url;
    }
}
