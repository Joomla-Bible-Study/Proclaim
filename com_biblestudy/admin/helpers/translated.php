<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */


//No Direct Access
defined('_JEXEC') or die('Restricted access'); 


/*
   translate a topicItem to clear text
   topicItem: stdClass containing topic_text and topic_params
   return: translated string or null if topicItem is not initialised
*/
function getTopicItemTranslated($topicItem)
{
	if ($topicItem) {
		$itemparams = new JRegistry;
		$itemparams->loadJSON($topicItem->topic_params);
		$currentLanguage = JFactory::getLanguage()->getTag();
		// first choice: string in current language
		if ($currentLanguage) {
			if ($itemparams->get($currentLanguage)) {
				return ($itemparams->get($currentLanguage));
			}
		}
		// second choice: language file
		$jtextString = JText::_($topicItem->topic_text);
		if (strncmp($jtextString, 'JBS_TOP_', 8) == 0 || strncmp($jtextString, '??JBS_TOP_', 10) == 0 || strlen($jtextString) == 0 || strcmp($jtextString, '????') == 0) {
			// third choice: string in default language selected for site
			$defaultLanguage = JComponentHelper::getParams('com_languages')->get('site');
			if ($defaultLanguage) {
				if ($itemparams->get($defaultLanguage)) {
					return ($itemparams->get($defaultLanguage));
				}
			}
		}
		// fallback: second choice
		return ($jtextString);
	}
	return (null);
}


/*
   translate a list of topicItems to clear text each
   topicItems: array of stdClass containing topic_text and topic_params
   return: list of topicItems containing translated strings in topic_text
*/
function getTopicItemsTranslated($topicItems = array())
{
	$output = array();
	foreach ($topicItems as $topicItem)
	{
		$text = getTopicItemTranslated($topicItem);
		$topicItem->topic_text = $text;
		$output[] = $topicItem;
	}
	return $output;
}
