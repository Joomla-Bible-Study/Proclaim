<?php

/**
 * Series Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Series Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementseries extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'series';

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

        $query = 'SELECT DISTINCT #__bsms_studies.series_id, #__bsms_series.series_text, #__bsms_series.id AS sid' .
                ' FROM #__bsms_studies' .
                ' LEFT JOIN #__bsms_series ON (#__bsms_series.id = #__bsms_studies.series_id)' .
                ' WHERE #__bsms_series.published = 1' .
                ' ORDER BY #__bsms_series.series_text ASC';
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Series') . ' -', 'series_id', 'series_text'));

        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'series_id', 'series_text', $value, $control_name . $name);
    }

}