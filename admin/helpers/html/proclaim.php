<?php
/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('JPATH_BASE') or die;


/**
 * Proclaim HTML class.
 *
 * @package    Proclaim.Admin
 * @since      7.0.0
 * @deprecated 10.0.0
 */
abstract class JHtmlProclaim
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  9.0.0
     */
    protected static array $loaded = array();

    /**
     * Method to get the field options.
     *
     * @return    object    The field option objects.
     *
     * @since    1.6
     */
    public static function playerList(): object
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
     * @since   2.5
     */
    public static function linkType(): string
    {
        // Create the batch selector to change the player on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-linkType" class="hasTip" title="' . Text::_(
                'JBS_MED_SHOW_DOWNLOAD_ICON'
            )
            . '::' . Text::_('JBS_MED_SHOW_DOWNLOAD_ICON_DESC') . '">',
            Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'),
            '</label>',
            '<select name="batch[linkType]" class="form-select" id="batch-linkType">',
            '<option value="">' . Text::_('JBS_BAT_DOWNLOAD_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::linkTypeList(), 'value', 'text'),
            '</select>'
        );

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
     * @since   2.5
     */
    public static function popup(): string
    {
        // Create the batch selector to change the popup on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-popup" class="hasTip" title="' . Text::_('JBS_MED_INTERNAL_POPUP')
            . '::' . Text::_('JBS_MED_INTERNAL_POPUP_DESC') . '">',
            Text::_('JBS_MED_POPUP'),
            '</label>',
            '<select name="batch[popup]" class="form-select" id="batch-popup">',
            '<option value="">' . Text::_('JBS_BAT_POPUP_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::popuplist(), 'value', 'text'),
            '</select>'
        );

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
     * @since   2.5
     */
    public static function mediaType(): string
    {
        // Create the batch selector to change the mediaType on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-mediaType" class="hasTip" title="' . Text::_('JBS_CMN_IMAGE')
            . '::' . Text::_('JBS_MED_IMAGE_DESC') . '">',
            Text::_('JBS_MED_SELECT_MEDIA_TYPE'),
            '</label>',
            '<select name="batch[mediaType]" class="form-select" id="batch-mediaType">',
            '<option value="">' . Text::_('JBS_BAT_MEDIATYPE_NOCHANGE') . '</option>',
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @throws  Exception
     * @since   2.5
     */
    public static function teacher(): string
    {
        // Create the batch selector to change the teacher on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-teacher" class="hasTip" title="' .
            Text::_('JBS_CMN_TEACHER') . '::' . Text::_('JBS_BAT_TEACHER_DESC') . '">',
            Text::_('JBS_CMN_TEACHER'),
            '</label>',
            '<select name="batch[teacher]" class="form-select" id="batch-teacher">',
            '<option value="">' . Text::_('JBS_BAT_TEACHER_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::Teacherlist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @throws Exception
     * @since    1.6
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
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @throws  Exception
     * @since   2.5
     */
    public static function messageType(): string
    {
        // Create the batch selector to change the message type on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-messageType" class="hasTip" title="' .
            Text::_('JBS_CMN_MESSAGETYPE') . '::' . Text::_('JBS_BAT_MESSAGETYPE_DESC') . '">',
            Text::_('JBS_CMN_MESSAGETYPE'),
            '</label>',
            '<select name="batch[messageType]" class="form-select" id="batch-messageType">',
            '<option value="">' . Text::_('JBS_BAT_MESSAGETYPE_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::Messagetypelist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @throws Exception
     * @since    1.6
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
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Display a batch widget for the teacher selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @throws  Exception
     * @since   2.5
     */
    public static function series(): string
    {
        // Create the batch selector to change the series on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-series" class="hasTip" title="' .
            Text::_('JBS_CMN_SERIES') . '::' . Text::_('JBS_BAT_SERIES_DESC') . '">',
            Text::_('JBS_CMN_SERIES'),
            '</label>',
            '<select name="batch[series]" class="form-select" id="batch-series">',
            '<option value="">' . Text::_('JBS_BAT_SERIES_NOCHANGE') . '</option>',
            HTMLHelper::_('select.options', self::Serieslist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Method to get the field options.
     *
     * @return array|null The field option objects.
     *
     * @throws Exception
     * @since    1.6
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
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }
}
