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
		$currentLanguage = & JFactory::getLanguage()->getTag();
		if ($currentLanguage) {
			if ($itemparams->get($currentLanguage)) {
				return ($itemparams->get($currentLanguage));
			}
		}
		return (JText::_($topicItem->topic_text));
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


/*
   translate a topic given topic_text and params to clear text
   topic_text: string topic_text out of db
   params: string params out of db
   return: translated string
*/
function getTopicTranslated($topic_text, $topic_params)
{
	if ($params) {
		$itemparams = new JRegistry;
		$itemparams->loadJSON($topic_params);
		$currentLanguage = & JFactory::getLanguage()->getTag();
		if ($currentLanguage) {
			if ($itemparams->get($currentLanguage)) {
				return ($itemparams->get($currentLanguage));
			}
		}
	}
	return (JText::_($topic_text));
}




