<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
include_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

/**
 * Topics List Form Field class for the Joomla Bible Study component
 * Displays a topics list of ALL published topics
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class JFormFieldTopicslist extends JFormFieldList {

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
    protected function getOptions() {
        $db = JFactory::getDBO();
        $query = "SELECT id, topic_text, params AS topic_params FROM #__bsms_topics WHERE published = 1 ORDER by topic_text ASC";
        $db->setQuery((string) $query);
        $topics = $db->loadObjectList();
        $options = array();
        if ($topics) {
            foreach ($topics as $topic) {
                $text = getTopicItemTranslated($topic);
                $options[] = JHtml::_('select.option', $topic->id, $text);
            }
        }
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }

}