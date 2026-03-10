<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Proclaim HTML class.
 *
 * @package    Proclaim.Admin
 * @since      10.0.0
 */
class Cwmhtml
{
    /**
     * Display a batch widget for the player selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function linkType(): string
    {
        // Create the batch selector to change the player on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-linkType" class="hasTip" title="' . Text::_(
                'JBS_MED_SHOW_DOWNLOAD_ICON'
            )
            . '::' . Text::_('JBS_MED_SHOW_DOWNLOAD_ICON_DESC') . '">',
            Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'),
            '</label>',
            '<select name="batch[linkType]" class="form-select" id="batch-linkType">',
            '<option value="">' . Text::_('JBS_BAT_DOWNLOAD_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::linkTypeList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return   object    The field option objects.
     *
     * @since    1.6
     */
    public static function linkTypeList(): object
    {
        $options = [];

        $options[] = ['value' => 0, 'text' => Text::_('JBS_MED_NO_DOWNLOAD_ICON')];
        $options[] = ['value' => 1, 'text' => Text::_('JBS_MED_SHOW_DOWNLOAD_ICON')];
        $options[] = ['value' => 2, 'text' => Text::_('JBS_MED_SHOW_ONLY_DOWNLOAD_ICON')];

        $object = new \stdClass();

        foreach ($options as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Method to get the player field options.
     *
     * @return  array  The field option objects.
     *
     * @since   10.1.0
     */
    public static function playerlist(): array
    {
        return [
            (object) ['value' => 0, 'text' => Text::_('JBS_CMN_DIRECT_LINK')],
            (object) ['value' => 1, 'text' => Text::_('JBS_CMN_USE_INTERNAL_PLAYER')],
            (object) ['value' => 8, 'text' => Text::_('JBS_CMN_USE_EMBED_CODE')],
        ];
    }

    /**
     * Display a batch widget for the popup selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function popup(): string
    {
        // Create the batch selector to change the popup on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-popup" class="hasTip" title="' . Text::_('JBS_MED_INTERNAL_POPUP')
            . '::' . Text::_('JBS_MED_INTERNAL_POPUP_DESC') . '">',
            Text::_('JBS_MED_POPUP'),
            '</label>',
            '<select name="batch[popup]" class="form-select" id="batch-popup">',
            '<option value="">' . Text::_('JBS_BAT_POPUP_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::popupList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return    object    The field option objects.
     *
     * @since    1.6
     */
    public static function popupList(): object
    {
        $options   = [];
        $options[] = ['value' => 3, 'text' => Text::_('JBS_CMN_USE_GLOBAL')];
        $options[] = ['value' => 2, 'text' => Text::_('JBS_CMN_INLINE')];
        $options[] = ['value' => 1, 'text' => Text::_('JBS_CMN_POPUP')];

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
     * @since   2.5
     */
    public static function mediaType(): string
    {
        // Create the batch selector to change the mediaType on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-mediaType" class="hasTip" title="' . Text::_('JBS_CMN_IMAGE')
            . '::' . Text::_('JBS_MED_IMAGE_DESC') . '">',
            Text::_('JBS_MED_SELECT_MEDIA_TYPE'),
            '</label>',
            '<select name="batch[mediaType]" class="form-select" id="batch-mediaType">',
            '<option value="">' . Text::_('JBS_BAT_MEDIATYPE_NOCHANGE') . '</option>',
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function teacher(): string
    {
        // Create the batch selector to change the teacher on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-teacher" class="hasTip" title="' .
            Text::_('JBS_CMN_TEACHER') . '::' . Text::_('JBS_BAT_TEACHER_DESC') . '">',
            Text::_('JBS_CMN_TEACHER'),
            '</label>',
            '<select name="batch[teacher]" class="form-select" id="batch-teacher">',
            '<option value="">' . Text::_('JBS_BAT_TEACHER_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::teacherList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return ?array The field option objects.
     *
     * @since    1.6
     */
    public static function teacherList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true);

        $query->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('teachername', 'text'));
        $query->from($db->quoteName('#__bsms_teachers', 'a'));
        $query->order($db->quoteName('a.teachername') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\Exception $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
                return [];
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function messageType(): string
    {
        // Create the batch selector to change the message type on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-messageType" class="hasTip" title="' .
            Text::_('JBS_CMN_MESSAGETYPE') . '::' . Text::_('JBS_BAT_MESSAGETYPE_DESC') . '">',
            Text::_('JBS_CMN_MESSAGETYPE'),
            '</label>',
            '<select name="batch[messageType]" class="form-select" id="batch-messageType">',
            '<option value="">' . Text::_('JBS_BAT_MESSAGETYPE_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::messageTypeList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return ?array The field option objects.
     *
     * @since    1.6
     */
    public static function messageTypeList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true);

        $query->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('message_type', 'text'));
        $query->from($db->quoteName('#__bsms_message_type', 'a'));
        $query->order($db->quoteName('a.message_type') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\Exception $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
                return [];
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function series(): string
    {
        // Create the batch selector to change the series on a selection list.
        $lines = [
            '<label id="batch-client-lbl" for="batch-series" class="hasTip" title="' .
            Text::_('JBS_CMN_SERIES') . '::' . Text::_('JBS_BAT_SERIES_DESC') . '">',
            Text::_('JBS_CMN_SERIES'),
            '</label>',
            '<select name="batch[series]" class="form-select" id="batch-series">',
            '<option value="">' . Text::_('JBS_BAT_SERIES_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::seriesList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return ?array The field option objects.
     *
     * @since    1.6
     */
    public static function seriesList(): ?array
    {
        $options = null;
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->getQuery(true);

        $query->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('series_text', 'text'));
        $query->from($db->quoteName('#__bsms_series', 'a'));
        $query->order($db->quoteName('a.series_text') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\Exception $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
                return [];
            }
        }

        return $options;
    }

    /**
     * Display a batch widget for the location selector.
     *
     * When the location system is enabled, the dropdown is filtered to
     * locations the current user has visibility over.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   10.1.0
     */
    public static function location(): string
    {
        $lines = [
            '<label id="batch-location-lbl" for="batch-location" class="hasTip" title="' .
            Text::_('JBS_CMN_LOCATION') . '::' . Text::_('JBS_BAT_LOCATION_DESC') . '">',
            Text::_('JBS_CMN_LOCATION'),
            '</label>',
            '<select name="batch[location]" class="form-select" id="batch-location">',
            '<option value="">' . Text::_('JBS_BAT_LOCATION_NOCHANGE') . '</option>',
            '<option value="0">' . Text::_('JBS_BAT_LOCATION_CLEAR') . '</option>',
            HTMLHelper::_('select.options', self::locationList(), 'value', 'text'),
            '</select>',
        ];

        return implode("\n", $lines);
    }

    /**
     * Return published locations as select options, filtered to accessible ones.
     *
     * Super admins and installations without the location system enabled see
     * all published locations. Other users see only locations returned by
     * CwmlocationHelper::getUserLocations().
     *
     * @return  array  Option objects with ->value and ->text.
     *
     * @since   10.1.0
     */
    public static function locationList(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('location_text', 'text'))
            ->from($db->quoteName('#__bsms_locations', 'a'))
            ->where($db->quoteName('a.published') . ' = 1')
            ->order($db->quoteName('a.location_text') . ' ASC');

        // If location filtering is enabled, restrict to accessible locations
        if (CwmlocationHelper::isEnabled()) {
            $accessible = CwmlocationHelper::getUserLocations();

            if (!empty($accessible)) {
                $query->whereIn($db->quoteName('a.id'), $accessible);
            }
        }

        $db->setQuery($query);

        try {
            return $db->loadObjectList() ?: [];
        } catch (\Exception $e) {
            try {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            } catch (\Exception $e) {
                return [];
            }
        }

        return [];
    }
}
