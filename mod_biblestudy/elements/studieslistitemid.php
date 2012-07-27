<?php

/**
 * Studies list Itemid Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Studies list Itemid Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementstudieslistitemid extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'studieslistitemid';

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

        $query = "SELECT m.id, CONCAT(m.id,' - ',m.name) AS text, m.link"
                . "\n FROM #__menu AS m"
                . "\n WHERE m.link LIKE '%studieslist%'"
                . "\n ORDER BY m.name ASC"
        ;
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Menu Item') . ' -', 'id', 'text'));
        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'id', 'text', $value, $control_name . $name);
    }

}
