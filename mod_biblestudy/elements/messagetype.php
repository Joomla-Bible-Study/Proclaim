<?php

/**
 * @version		$Id: messagetype.php 8591 2007-08-27 21:09:32Z Tom Fuller $
 * @package		mod_biblestudy
 * @copyright            2010-2011
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class JElementmessagetype extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'messagetype';

    function fetchElement($name, $value, &$node, $control_name) {
        $db = JFactory::getDBO();
        $language = JFactory::getLanguage();
        $language->load('com_biblestudy');

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