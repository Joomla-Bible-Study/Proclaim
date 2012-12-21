<?php

/**
 * User SEF extension for Joomla!
 *
 * @author      $Author: Nick Fossen $
 * @package SefExt
 * @subpackage Pugin.BibleStudy
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * SefExt for BibleStudy class
 * @package SefExt
 * @subpackage Pugin.BibleStudy
 * @6.2.2
 */
class SefExt_com_biblestudy extends SefExt {

    /**
     * Get non-sef vars
     * @param string $uri
     * @return array
     */
    function getNonSefVars(&$uri) {
        $this->_createNonSefVars($uri);

        return array($this->nonSefVars, $this->ignoreVars);
    }

    /**
     * Create non-sef vars
     * @param string $uri
     * @return object
     */
    function _createNonSefVars(&$uri) {
        if (isset($this->nonSefVars) && isset($this->ignoreVars))
            return;

        $this->nonSefVars = array();
        $this->ignoreVars = array();

        if (!is_null($uri->getVar('return')))
            $this->nonSefVars['return'] = $uri->getVar('return');
    }

    /**
     * Create url
     * @param string $uri
     * @return string
     */
    function create(&$uri) {
        $vars = $uri->getQuery(true);
        extract($vars);

        $title = array();
        $title[] = JoomSEF::_getMenuTitle(@$option, null, @$Itemid);
        $title[] = "Biblestudy";  //start with Biblestudy

        $database = JFactory::getDBO();
        // Views
        switch ($view) {
            case 'studydetails':  // Need to keep the id because of the number of teachings
                if (isset($format)) {
                    $title[] = $view . $format . '-' . $id;
                }
                else
                    $title[] = $view . '-' . $id;
                break;
            case 'teacherdisplay':
                $title[] = 'teacher';

                $query_name = 'SELECT teachername FROM #__bsms_teachers WHERE #__bsms_teachers.id = ' . $id;
                $database->setQuery($query_name);
                $teacher = $database->loadResult();
                $title[] = $teacher;

                break;
            case 'seriesdetail':
                $title[] = $view;

                $query_name = 'SELECT series_text FROM #__bsms_series WHERE #__bsms_series.id = ' . $id;
                $database->setQuery($query_name);
                $series = $database->loadResult();
                $title[] = $series;
                break;
            default:
                $title[] = $view;
        }

        // ***** Filter section
        if ($filter_year) {
            $title[] = $filter_year;
        }
        if ($filter_book) {
            $query_name = 'SELECT bookname FROM #__bsms_books WHERE #__bsms_books.booknumber = ' . $filter_book;
            $database->setQuery($query_name);
            $book = $database->loadResult();
            $title[] = $book;
        }
        if ($filter_series) {
            $query_name = 'SELECT series_text FROM #__bsms_series WHERE #__bsms_series.id = ' . $filter_series;
            $database->setQuery($query_name);
            $series = $database->loadResult();
            $title[] = $series;
        }
        if ($filter_location) {
            $query_name = 'SELECT location_text FROM #__bsms_locations WHERE #__bsms_locations.id = ' . $filter_location;
            $database->setQuery($query_name);
            $location = $database->loadResult();
            $title[] = $location;
        }
        if ($filter_messagetype) {
            $query_name = 'SELECT message_type FROM #__bsms_message_type WHERE #__bsms_message_type.id = ' . $filter_messagetype;
            $database->setQuery($query_name);
            $messagetype = $database->loadResult();
            $title[] = $messagetype;
        }
        if ($filter_topic) {
            $query_name = 'SELECT topic_text FROM #__bsms_topics WHERE #__bsms_topics.id = ' . $filter_topic;
            $database->setQuery($query_name);
            $topic = $database->loadResult();
            $title[] = $topic;
        }
        // Download
        if (isset($task) == 'download') {
            $title[] = $id;
        }

        // Create URL
        $newUri = $uri;
        if (count($title) > 0) {
            $this->_createNonSefVars($uri);
            $newUri = JoomSEF::_sefGetLocation($uri, $title, @$task, null, null, @$lang, $this->nonSefVars);
        }

        return $newUri;
    }

}
