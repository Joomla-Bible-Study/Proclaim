<?php

/**
 * @version     $Id: studiesedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
include_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'translated.php');

/**
 * Topics List Form Field class for the Joomla Bible Study component
 * Displays a topics list of ALL published topics
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
