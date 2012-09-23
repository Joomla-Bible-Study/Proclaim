<?php

/**
 * Translated Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * translate a topicItem to clear text
 * topicItem: stdClass containing topic_text and topic_params
 * return: translated string or null if topicItem is not initialised
 *
 *
 * @param string $topicItem
 * @return string|NULL
 */
function getTopicItemTranslated($topicItem) {
    //If there is no topic to translate, just return
    if ($topicItem) {
        // first choice: evaluate language strings
        $itemparams = new JRegistry;
        @$itemparams->loadJSON($topicItem->topic_params);
        $currentLanguage = JFactory::getLanguage()->getTag();
        // first choice: string in current language
        if ($currentLanguage) {
            if ($itemparams->get($currentLanguage)) {
                return ($itemparams->get($currentLanguage));
            }
        }
        // second choice: language file
        $jtextString = @JText::_($topicItem->topic_text);
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

/**
 * translate a list of topicItems to clear text each
 * topicItems: array of stdClass containing topic_text and topic_params
 * return: list of topicItems containing translated strings in topic_text
 *
 *
 * @param array $topicItems
 * @return array
 */
function getTopicItemsTranslated($topicItems = array()) {
    $output = array();
    foreach ($topicItems as $topicItem) {
        $text = getTopicItemTranslated($topicItem);
        $topicItem->topic_text = $text;
        $output[] = $topicItem;
    }
    return $output;
}

/**
 * translate a concatenated list of topics to clear text
 * topicItem: stdClass containing the studies id and tp_id (i.e. concatenated topic ids)
 * return: translated string with format '<text>[, <text>[, <text>]]' or null if topicItem is not initialised
 *
 *
 * @param type $topicItem
 * @return type
 */
function getConcatTopicItemTranslated($topicItem) {
    if ($topicItem) {
        // Check if there should be topics at all to save time
        if ($topicItem->tp_id) {
            $db = JFactory::getDBO();
            $query = 'SELECT #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params '
                    . 'FROM #__bsms_topics '
                    . 'LEFT JOIN #__bsms_studytopics ON (#__bsms_studytopics.study_id = ' . $topicItem->id . ') '
                    . 'WHERE published = 1 and #__bsms_topics.id = #__bsms_studytopics.topic_id';
            $db->setQuery($query);
            $results = $db->loadObjectList();
            $output = '';
            $count = count($results);
            for ($i = 0; $i < $count; $i++) {
                if ($i > 0) {
                    $output .= ', ';
                }
                $output .= getTopicItemTranslated($results[$i]);
            }
            return $output;
        }
    }
    return (null);
}