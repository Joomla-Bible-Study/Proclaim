<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// For some reason the autoloader is not finding this file so this is a temporary workaround
include_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/translated.php';

/**
 * Topics List Form Field class for the Proclaim component
 * Displays a topics list of ALL published topics
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldTopicslist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'Topics';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since 9.0.0
	 */
	protected function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params as topic_params')
			->from('#__bsms_studies')
			->leftJoin('#__bsms_studytopics ON #__bsms_studies.id = #__bsms_studytopics.study_id')
			->leftJoin('#__bsms_topics ON #__bsms_topics.id = #__bsms_studytopics.topic_id')
			->where('#__bsms_topics.published = 1')
			->order('#__bsms_topics.topic_text ASC');
		$db->setQuery($query);
		$topics = $db->loadObjectList();
		$options = array();

		if ($topics)
		{
			foreach ($topics as $topic)
			{
				$text      = JBSMTranslated::getTopicItemTranslated($topic);
				$options[] = JHtml::_('select.option', $topic->id, $text);
			}
		}

		// Sort the Topics after Translation to Alphabetically
		usort($options, array($this, "order_new"));
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Order New using strcmp
	 *
	 * @param   object  $a  Start.
	 * @param   object  $b  End.
	 *
	 * @return int Used to place in new sort.
	 *
	 * @since 7.0
	 */
	private function order_new($a, $b)
	{
		$a = (array) $a;
		$b = (array) $b;

		return strcmp($a["text"], $b["text"]);
	}
}
