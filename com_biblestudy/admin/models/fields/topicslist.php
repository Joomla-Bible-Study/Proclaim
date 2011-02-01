<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a teacher list for the studieslist menu item
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Topics List Form Field class for the Joomla Bible Study component
 */
class JFormFieldTopicslist extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Topicslist';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                $db = JFactory::getDBO();
                $query = "SELECT DISTINCT #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_studies.topics_id, #__bsms_studytopics.topic_id, #__bsms_studytopics.study_id FROM #__bsms_studies LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id) LEFT JOIN #__bsms_studytopics ON (#__bsms_studytopics.id = #__bsms_studies.topics_id)";
                //$db->setQuery($query);
                //$query = $db->getQuery(true);
                //$query->select('id,teachername');
                //$query->from('#__bsms_teachers');
                $db->setQuery((string)$query);
                $messages = $db->loadObjectList();
                $options = array();
                if ($messages)
                {
                        foreach($messages as $message) 
                        {
                                $options[] = JHtml::_('select.option', $message->tid, $message->topic_text);
                        }
                }
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>