<?php

/**
 * MessageType Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * MessageType Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementmessagetype extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'messagetype';

    /**
     * Element Function
     * @param string $name
     * @param string $value
     * @param string $node
     * @param string $control_name
     * @return string
     */
    function fetchElement($name, $value, &$node, $control_name) {
        $db = JFactory::getDBO();

        $query = 'SELECT DISTINCT #__bsms_studies.messagetype, #__bsms_message_type.message_type, #__bsms_message_type.id' .
                ' FROM #__bsms_studies' .
                ' LEFT JOIN #__bsms_message_type ON (#__bsms_message_type.id = #__bsms_studies.messagetype)' .
                ' WHERE #__bsms_message_type.published = 1' .
                ' ORDER BY #__bsms_message_type.id ASC';
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Message Type') . ' -', 'id', 'message_type'));

        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'id', 'message_type', $value, $control_name . $name);
    }

}