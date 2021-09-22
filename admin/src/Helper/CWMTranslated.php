<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseFactory;

/**
 * class for Translated Helper
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTranslated
{
	/**
	 * Extension Name
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public static $extension = 'com_proclaim';

	/**
	 * Translate a list of topicItems to clear text each
	 *
	 * @param   array  $topicItems  array of stdClass containing topic_text and topic_params
	 *
	 * @return  array  list of topicItems containing translated strings in topic_text
	 *
	 * @since    7.0.0
	 */
	public static function getTopicItemsTranslated($topicItems = array())
	{
		$output = array();

		foreach ($topicItems as $topicItem)
		{
			$text                  = self::getTopicItemTranslated($topicItem);
			$topicItem->topic_text = $text;
			$output[]              = $topicItem;
		}

		return $output;
	}

	/**
	 * Translate a topicItem to clear text
	 *
	 * @param   object  $topicItem  stdClass containing topic_text and topic_params
	 *
	 * @return string|NULL  translated string or null if topicItem is not initialised
	 *
	 * @since    7.0.0
	 */
	public static function getTopicItemTranslated($topicItem)
	{
		// If there is no topic to translate, just return
		if ($topicItem)
		{
			// First choice: evaluate language strings
			$itemparams = new Registry;
			$itemparams->loadString($topicItem->topic_params);
			$currentLanguage = Factory::getLanguage()->getTag();

			// First choice: string in current language
			if ($currentLanguage)
			{
				if ($itemparams->get($currentLanguage))
				{
					return ($itemparams->get($currentLanguage));
				}
			}

			// Second choice: language file
			$jtextString = Text::_($topicItem->topic_text);

			$string1 = strncmp($jtextString, 'JBS_TOP_', 8) === 0 || strncmp($jtextString, '??JBS_TOP_', 10) === 0;
			$string2 = $jtextString === '' || strcmp($jtextString, '????') === 0;

			if ($string1 || $string2)
			{
				// Third choice: string in default language selected for site
				$defaultLanguage = ComponentHelper::getParams('com_languages')->get('site');

				if ($defaultLanguage)
				{
					if ($itemparams->get($defaultLanguage))
					{
						return ($itemparams->get($defaultLanguage));
					}
				}
			}

			// Fallback: second choice
			return ($jtextString);
		}

		return null;
	}

	/**
	 * Translate a concatenated list of topics to clear text
	 *
	 * @param   object  $topicItem  stdClass containing the studies id and tp_id (i.e. concatenated topic ids)
	 *
	 * @return string:null  translated string with format '<text>[, <text>[, <text>]]' or null if topicItem is not initialised
	 *
	 * @since    7.0.0
	 */
	public static function getConcatTopicItemTranslated($topicItem)
	{
		if ($topicItem)
		{
			// Check if there should be topics at all to save time
			if ($topicItem->tp_id)
			{
                $db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select('#__bsms_topics.topic_text, #__bsms_topics.params AS topic_params')
					->from('#__bsms_topics')
					->leftJoin('#__bsms_studytopics ON (#__bsms_studytopics.study_id = ' . $db->q($topicItem->id) . ') ')
					->where('published = ' . 1)
					->where('#__bsms_topics.id = #__bsms_studytopics.topic_id');
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$output  = '';

				foreach ($results as $i => $iValue)
				{
					if ($i > 0)
					{
						$output .= ', ';
					}

					$output .= self::getTopicItemTranslated($iValue);
				}

				return $output;
			}
		}

		return null;
	}
}
