<?php

/**
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementlocations extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'location_text';

    function fetchElement($name, $value, &$node, $control_name) {
        $db = JFactory::getDBO();

        $query = "SELECT l.id, l.location_text AS text"
                . "\n FROM #__bsms_locations AS l"
                . "\n WHERE l.published = 1"
                . "\n ORDER BY l.location_text ASC"
        ;
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Location') . ' -', 'id', 'text'));
        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'id', 'text', $value, $control_name . $name);
    }

}