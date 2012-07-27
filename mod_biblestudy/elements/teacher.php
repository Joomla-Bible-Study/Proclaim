<?php

/**
 * Element Teacher
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Element Teacher
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementteacher extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'teacher';

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
        $language = JFactory::getLanguage();
        $language->load('com_biblestudy');

        $query = 'SELECT DISTINCT #__bsms_studies.teacher_id, #__bsms_teachers.teachername, #__bsms_teachers.id AS tid' .
                ' FROM #__bsms_studies' .
                ' LEFT JOIN #__bsms_teachers ON (#__bsms_teachers.id = #__bsms_studies.teacher_id)' .
                ' WHERE #__bsms_teachers.published = 1' .
                ' ORDER BY #__bsms_teachers.teachername ASC';
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Teacher') . ' -', 'teacher_id', 'teachername'));

        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'teacher_id', 'teachername', $value, $control_name . $name);
    }

}