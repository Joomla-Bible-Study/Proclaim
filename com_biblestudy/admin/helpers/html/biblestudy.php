<?php
/**
 * @package     Biblestudy.Administrator
 * @subpackage  com_biblestudy
 *
 * @copyright   Copyright (C) 2007 - 2012 Joomla Bible Study. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
    defined('JPATH_BASE') or die;

    /**
     * Biblestudy HTML class.
     *
     * @package     Joomla.Administrator
     * @subpackage  com_biblestudy
     * @since       2.5
     */
abstract class JHtmlBiblestudy
{
    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     * @since	1.6
     */
    public static function playerlist()
    {
        $options = array();
        $options[] = array('value'=>100, 'text'=>JText::_('JBS_CMN_USE_GLOBAL'));
        $options[] = array('value'=>0, 'text'=>JText::_('JBS_CMN_DIRECT_LINK'));
        $options[] = array('value'=>1, 'text'=>JText::_('JBS_CMN_USE_INTERNAL_PLAYER'));
        $options[] = array('value'=>3, 'text'=>JText::_('JBS_CMN_USE_AV'));
        $options[] = array('value'=>7, 'text'=>JText::_('JBS_CMN_USE_LEGACY_PLAYER'));
        $options[] = array('value'=>8, 'text'=>JText::_('JBS_CMN_USE_EMBED_CODE'));
        $object = new stdClass();
        foreach($options as $key=>$value)
        {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     * @since	1.6
     */
    public static function popuplist()
    {
        $options = array();
        $options[] = array('value'=>3, 'text'=>JText::_('JBS_CMN_USE_GLOBAL'));
        $options[] = array('value'=>2, 'text'=>JText::_('JBS_CMN_INLINE'));
        $options[] = array('value'=>1, 'text'=>JText::_('JBS_CMN_POPUP'));

        $object = new stdClass();
        foreach($options as $key=>$value)
        {
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
    public static function players()
    {
        // Create the batch selector to change the player on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="'.JText::_('JBS_MED_PLAYER').'::'.JText::_('JBS_MED_PLAYER_DESC').'">',
            JText::_('JBS_MED_PLAYER'),
            '</label>',
            '<select name="batch[player]" class="inputbox" id="batch-player">',
            JHtml::_('select.options', self::playerlist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }

    /**
     * Display a batch widget for the popup selector.
     *
     * @return  string  The necessary HTML for the widget.
     *
     * @since   2.5
     */
    public static function popup()
    {
        // Create the batch selector to change the popup on a selection list.
        $lines = array(
            '<label id="batch-client-lbl" for="batch-client" class="hasTip" title="'.JText::_('JBS_MED_INTERNAL_POPUP').'::'.JText::_('JBS_MED_INTERNAL_POPUP_DESC').'">',
            JText::_('JBS_MED_POPUP'),
            '</label>',
            '<select name="batch[popup]" class="inputbox" id="batch-popup">',
            JHtml::_('select.options', self::popuplist(), 'value', 'text'),
            '</select>'
        );

        return implode("\n", $lines);
    }
}