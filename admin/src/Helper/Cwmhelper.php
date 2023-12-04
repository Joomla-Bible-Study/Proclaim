<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Core Bible Study Helper
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 * */
class Cwmhelper
{
    /**
     * Extension Name
     *
     * @var string
     *
     * @since 8.0.0
     */
    public static string $extension = 'com_proclaim';

    /**
     * Get tooltip.
     *
     * @param   object     $row       JTable
     * @param   Registry   $params    Item Params
     * @param   \stdClass  $template  Template Table
     *
     * @return string
     *
     * @throws \Exception
     * @since  9.0.0
     */
    public static function getTooltip(object $row, Registry $params, \stdClass $template): string
    {
        $JBSMElements = new Cwmlisting();

        $linktext = '<span class="hasTip" title="<strong>' . $params->get('tip_title') . '  :: ';

        $tip1 = $JBSMElements->getElement($params->get('tip_item1'), $row, $params, $template, $type = 0);
        $tip2 = $JBSMElements->getElement($params->get('tip_item2'), $row, $params, $template, $type = 0);
        $tip3 = $JBSMElements->getElement($params->get('tip_item3'), $row, $params, $template, $type = 0);
        $tip4 = $JBSMElements->getElement($params->get('tip_item4'), $row, $params, $template, $type = 0);
        $tip5 = $JBSMElements->getElement($params->get('tip_item5'), $row, $params, $template, $type = 0);

        $linktext .= '<strong>' . $params->get('tip_item1_title') . '</strong>: ' . $tip1 . '<br />';
        $linktext .= '<strong>' . $params->get('tip_item2_title') . '</strong>: ' . $tip2 . '<br />';
        $linktext .= '<strong>' . $params->get('tip_item3_title') . '</strong>: ' . $tip3 . '<br />';
        $linktext .= '<strong>' . $params->get('tip_item4_title') . '</strong>: ' . $tip4 . '<br />';
        $linktext .= '<strong>' . $params->get('tip_item5_title') . '</strong>: ' . $tip5;
        $linktext .= '">';

        return $linktext;
    }

    /**
     * Get ShowHide.
     *
     * @return string
     *
     * @deprecated 7.1.8
     *
     * @since      8.2.0
     */
    public static function getShowhide(): string
    {
        return '
        function HideContent(d) {
        document.getElementById(d).style.display = "none";
        }
        function ShowContent(d) {
        document.getElementById(d).style.display = "block";
        }
        function ReverseDisplay(d) {
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
        }
        ';
    }

    /**
     * Method to get file size
     *
     * @param   string  $url  URL
     *
     * @return  integer  Return size or false read.
     *
     * @since 9.0.0
     */
    public static function getRemoteFileSize(string $url): int
    {
        $size = 0;

        if ($url === '' || substr_count($url, 'youtu.be') > 0 || substr_count($url, 'youtube.com') > 0) {
            return 0;
        }

        // Removes a bad url problem in some DB's
        if (substr_count($url, '/http')) {
            $url = ltrim($url, '/');
        }

        if (!substr_count($url, 'http://') && !substr_count($url, 'https://')) {
            if (substr_count($url, '//')) {
                $url = 'http:' . $url;
            } elseif (!substr_count($url, '//')) {
                $url = 'http://' . $url;
            }
        }

        try {
            $headers = @get_headers($url, true);
        } catch (\Exception $e) {
            return 0;
        }

        if (is_array($headers)) {
            $head = array_change_key_case($headers);
        } else {
            return 0;
        }

        if (isset($head['content-length']) && is_array($head['content-length'])) {
            if (count($head['content-length']) >= 1) {
                $dif  = count($head['content-length']) - 1;
                $size = $head['content-length'][$dif];
            } else {
                $size = $head['content-length'][0];
            }
        } elseif (isset($head['content-length'])) {
            $size = $head['content-length'];
        }

        return (int)$size;
    }

    /**
     * Set File Size for MediaFile
     *
     * @param   int  $id    ID of MediaFile
     * @param   int  $size  Size of file in bits
     *
     * @return void
     *
     * @since 9.0.14
     */
    public static function setFileSize(int $id, int $size): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__bsms_mediafiles')
            ->where('id = ' . (int)$id);

        $db->setQuery($query);
        $media = $db->loadObject();

        $reg = new Registry();
        $reg->loadString($media->params);
        $reg->set('size', $size);

        $update         = new \stdClass();
        $update->id     = $id;
        $update->params = $reg->toString();

        $db->updateObject('#__bsms_mediafiles', $update, 'id');
    }

    /**
     * Media Build URL Fix up for '/' and protocol.
     *
     * @param   string    $spath        Server Path
     * @param   string    $path         File
     * @param   Registry  $params       Parameters.
     * @param   bool      $setProtocol  True add protocol els no
     * @param   bool      $local        Local server
     * @param   bool      $podcast      True if from a precast
     *
     * @return string Completed path.
     *
     * @since 9.0.3
     */
    public static function mediaBuildUrl(
        $spath,
        $path,
        Registry $params,
        bool $setProtocol = false,
        bool $local = false,
        bool $podcast = false
    ): string {
        if (empty($path)) {
            return false;
        }

        if ($spath) {
            $spath = rtrim($spath, '/');
        } else {
            $spath = '';
        }

        $path     = ltrim($path, '/');
        $host     = $_SERVER['HTTP_HOST'];
        $protocol = Uri::root();

        // To see if the server is local
        if (str_contains($spath, $host)) {
            $local = true;
        }

        if (substr_count($path, 'http://') && $podcast) {
            return str_replace('http://', "", $path);
        }

        if (substr_count($path, 'https://') && $podcast) {
            return str_replace('https://', "", $path);
        }

        if (!empty($spath) && $podcast) {
            return str_replace('//', "", $spath) . '/' . $path;
        }

        if (!substr_count($path, '://') && !substr_count($path, '//') && $setProtocol) {
            if (empty($spath)) {
                return $protocol . $path;
            }

            $protocol = $params->get('protocol', 'http://');

            if ((substr_count($spath, '://') || substr_count($spath, '//')) && !empty($spath)) {
                if (substr_count($spath, '//')) {
                    $spath = substr($spath, 2);
                }

                return $protocol . $spath . '/' . $path;
            }

            // Set Protocol based on server status
            $path = $protocol . $spath . '/' . $path;
        } elseif ((!substr_count($spath, '://') || !substr_count($spath, '//')) && !empty($spath)) {
            $path = $spath . '/' . $path;
        }

        return $path;
    }

    /**
     * Clear Cache of Proclaim
     *
     * @param   string  $state  Where to clean the cache from. Site or Admin.
     *
     * @return void
     * @throws \Exception
     * @since 9.0.4
     */
    public static function clearcache(string $state = 'site'): void
    {
        $conf    = Factory::getApplication()->getConfig();
        $options = array();

        if ($state === 'administrator') {
            $options = array(
                'defaultgroup' => 'com_proclaim',
                'storage'      => $conf->get('cache_handler', ''),
                'caching'      => true,
                'cachebase'    => $conf->get('cache_path', JPATH_ADMINISTRATOR . '/cache')
            );
        } elseif ($state === 'site') {
            $options = array(
                'defaultgroup' => 'com_proclaim',
                'storage'      => $conf->get('cache_handler', ''),
                'caching'      => true,
                'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
            );
        }

        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController(
            '',
            $options
        );
        $cache->clean();
    }

    /**
     * Remove Http
     *
     * @param   string  $url  Url
     *
     * @return array|string|string[]
     *
     * @since 9.0.18
     */
    public static function removeHttp(string $url): array|string
    {
        $disallowed = array('http://', 'https://');

        foreach ($disallowed as $d) {
            if (str_starts_with($url, $d)) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }

    /**
     * Get Simple View Sate
     *
     * @param   Registry|null  $params  AdminTable + parameters
     *
     * @return  \stdClass
     *
     * @throws \Exception
     * @since 9.1.6
     */
    public static function getSimpleView(?Registry $params = null): \stdClass
    {
        $simple = new \stdClass();

        if ($params === null) {
            $params = Cwmparams::getAdmin()->params;
        }

        $simple->mode    = (int)$params->get('simple_mode');
        $simple->display = (int)$params->get('simple_mode_display');

        return $simple;
    }

    /**
     * @var    array  Array containing information for loaded files
     * @since  9.0.0
     */
    protected static array $loaded = array();

    /**
     * Display a batch widget for the player selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function players(): string
    {
        // Create the batch selector to change the player on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_MED_PLAYER')
            . '::' . Text::_('JBS_MED_PLAYER_DESC') . '">',
            Text::_('JBS_MED_PLAYER'),
            '</label>',
            '<select name="batch[player]" class="inputbox" id="batch-player">',
            '<option value="">' . Text::_('JBS_BAT_PLAYER_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::playerList(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return    \stdClass    The field option objects.
     *
     * @since    10.0.0
     */
    public static function playerList(): \stdClass
    {
        $options   = array();
        $options[] = array('value' => '', 'text' => Text::_('JBS_CMN_USE_GLOBAL'));
        $options[] = array('value' => 0, 'text' => Text::_('JBS_CMN_DIRECT_LINK'));
        $options[] = array('value' => 1, 'text' => Text::_('JBS_CMN_USE_INTERNAL_PLAYER'));
        $options[] = array('value' => 3, 'text' => Text::_('JBS_CMN_USE_AV'));
        $options[] = array('value' => 7, 'text' => Text::_('JBS_CMN_USE_MP3_PLAYER'));
        $options[] = array('value' => 8, 'text' => Text::_('JBS_CMN_USE_EMBED_CODE'));
        $object    = new \stdClass();

        foreach ($options as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Display a batch widget for the player selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function linkType(): string
    {
        // Create the batch selector to change the player on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_(
                'JBS_MED_SHOW_DOWNLOAD_ICON'
            )
            . '::' . Text::_('JBS_MED_SHOW_DOWNLOAD_ICON_DESC') . '">',
            Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'),
            '</label>',
            '<select name="batch[link_type]" class="inputbox" id="batch-link_type">',
            '<option value="">' . Text::_('JBS_BAT_DOWNLOAD_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::linkTypeList(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return   \stdClass    The field option objects.
     *
     * @since    10.0.0
     */
    public static function linkTypeList(): \stdClass
    {
        $options = array();

        $options[] = array('value' => 0, 'text' => Text::_('JBS_MED_NO_DOWNLOAD_ICON'));
        $options[] = array('value' => 1, 'text' => Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'));
        $options[] = array('value' => 2, 'text' => Text::_('JBS_MED_SHOW_ONLY_DOWNLOAD_ICON'));

        $object = new \stdClass();

        foreach ($options as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Display a batch widget for the popup selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function popup(): string
    {
        // Create the batch selector to change the popup on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_MED_INTERNAL_POPUP')
            . '::' . Text::_('JBS_MED_INTERNAL_POPUP_DESC') . '">',
            Text::_('JBS_MED_POPUP'),
            '</label>',
            '<select name="batch[popup]" class="inputbox" id="batch-popup">',
            '<option value="">' . Text::_('JBS_BAT_POPUP_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::popuplist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return    \stdClass    The field option objects.
     *
     * @since    10.0.0
     */
    public static function popupList(): \stdClass
    {
        $options   = array();
        $options[] = array('value' => 3, 'text' => Text::_('JBS_CMN_USE_GLOBAL'));
        $options[] = array('value' => 2, 'text' => Text::_('JBS_CMN_INLINE'));
        $options[] = array('value' => 1, 'text' => Text::_('JBS_CMN_POPUP'));

        $object = new \stdClass();

        foreach ($options as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Display a batch widget for the player selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function mediaType(): string
    {
        // Create the batch selector to change the media type on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_CMN_IMAGE')
            . '::' . Text::_('JBS_MED_IMAGE_DESC') . '">',
            Text::_('JBS_MED_SELECT_MEDIA_TYPE'),
            '</label>',
            '<select name="batch[mediatype]" class="inputbox" id="batch-mediatype">',
            '<option value="">' . Text::_('JBS_BAT_MEDIATYPE_NOCHANGE') . '</option>',
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @since    10.0.0
     */
    public static function MediaTypeList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $query   = $db->getQuery(true);

        $query->select('id As value, media_text As text');
        $query->from('#__bsms_media AS a');
        $query->order('a.media_text ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function teacher(): string
    {
        // Create the batch selector to change the teacher on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
            Text::_('JBS_CMN_TEACHER') . '::' . Text::_('JBS_BAT_TEACHER_DESC') . '">',
            Text::_('JBS_CMN_TEACHER'),
            '</label>',
            '<select name="batch[teacher]" class="inputbox" id="batch-teacher">',
            '<option value="">' . Text::_('JBS_BAT_TEACHER_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::teacherList(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @since    10.0.0
     */
    public static function teacherList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $query   = $db->getQuery(true);

        $query->select('id As value, teachername As text');
        $query->from('#__bsms_teachers AS a');
        $query->order('a.teachername ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\exception $e) {
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function messageType(): string
    {
        // Create the batch selector to change the message type on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
            Text::_('JBS_CMN_MESSAGETYPE') . '::' . Text::_('JBS_BAT_MESSAGETYPE_DESC') . '">',
            Text::_('JBS_CMN_MESSAGETYPE'),
            '</label>',
            '<select name="batch[messagetype]" class="inputbox" id="batch-messagetype">',
            '<option value="">' . Text::_('JBS_BAT_MESSAGETYPE_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::messageTypeList(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @since    10.0.0
     */
    public static function messageTypeList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $query   = $db->getQuery(true);

        $query->select('id As value, message_type As text');
        $query->from('#__bsms_message_type AS a');
        $query->order('a.message_type ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.0.0
     */
    public static function series(): string
    {
        // Create the batch selector to change the series on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
            Text::_('JBS_CMN_SERIES') . '::' . Text::_('JBS_BAT_SERIES_DESC') . '">',
            Text::_('JBS_CMN_SERIES'),
            '</label>',
            '<select name="batch[series]" class="inputbox" id="batch-series">',
            '<option value="">' . Text::_('JBS_BAT_SERIES_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::seriesList(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @since    10.0.0
     */
    public static function seriesList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get('DatabaseDriver');
        $query   = $db->getQuery(true);

        $query->select('id As value, series_text As text');
        $query->from('#__bsms_series AS a');
        $query->order('a.series_text ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
            }
        }

        return $options;
    }
}
