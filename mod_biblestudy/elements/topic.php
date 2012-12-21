<?php

/**
 * Topic Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Tobic Element
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @since 7.0.0
 */
class JElementtopic extends JElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'topic';

    /**
     * Elment function
     * @param string $name
     * @param string $value
     * @param string $node
     * @param string $control_name
     * @return string
     */
    function fetchElement($name, $value, &$node, $control_name) {
        $db = JFactory::getDBO();

        $query = 'SELECT DISTINCT #__bsms_studies.topics_id, #__bsms_topics.topic_text, #__bsms_topics.id AS tid' .
                ' FROM #__bsms_studies' .
                ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)' .
                ' WHERE #__bsms_topics.published = 1' .
                ' ORDER BY #__bsms_topics.topic_text ASC';
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- ' . JText::_('Select a Topic') . ' -', 'topics_id', 'topic_text'));

        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'topics_id', 'topic_text', $value, $control_name . $name);
    }

}