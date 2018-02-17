<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
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
		$query->select('id, topic_text, params AS topic_params')
			->from('#__bsms_topics')
			->where('published = ' . 1)
			->order('topic_text asc');
		$db->setQuery((string) $query);
		$topics  = $db->loadObjectList();
		$options = array();

		if ($topics)
		{
			foreach ($topics as $topic)
			{
				$text      = JBSMTranslated::getTopicItemTranslated($topic);
				$options[] = JHtml::_('select.option', $topic->id, $text);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
