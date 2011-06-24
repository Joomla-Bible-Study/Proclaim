<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a topics list of all published topics
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
include_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'translated.php');

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
//                $query = "SELECT DISTINCT #__bsms_topics.id AS tid, #__bsms_topics.topic_text, #__bsms_studies.topics_id, #__bsms_studytopics.topic_id, #__bsms_studytopics.study_id FROM #__bsms_studies LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id) LEFT JOIN #__bsms_studytopics ON (#__bsms_studytopics.id = #__bsms_studies.topics_id)";
		$query = "SELECT id, topic_text, params AS topic_params FROM #__bsms_topics WHERE published = 1 ORDER by topic_text ASC";
		$db->setQuery((string)$query);
		$topics = $db->loadObjectList();
		$options = array();
		if ($topics) {
			foreach($topics as $topic) {
				$text = getTopicItemTranslated($topic);
				$options[] = JHtml::_('select.option', $topic->id, $text);
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}


?>