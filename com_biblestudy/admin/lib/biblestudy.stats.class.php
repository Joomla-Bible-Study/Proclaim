<?php

/**
 * System to Display Stats
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMParams', BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php');

/**
 * Bible Study stats support class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class jbStats
{

    /**
     * Total plays of media files per study
     * @param int $id Id number of study
     * @return int Total plays form the media
     */
    public static function totalplays($id)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT sum(m.plays), m.study_id, m.published, s.id FROM #__bsms_mediafiles AS m'
            . ' LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id)'
            . ' WHERE m.study_id = ' . $id;
        $db->setQuery($query);
        $plays = $db->loadResult();
        return (int)$plays;
    }

    /**
     * Total messages in Bible Study
     * @param  string date start
     * @param string date end
     * @return int
     */
    public static function get_total_messages($start = '', $end = '')
    {
        $biblestudy_db = JFactory::getDBO();
        $where = array();
        if (!empty($start))
            $where[] = 'time > UNIX_TIMESTAMP(\'' . $start . '\')';
        if (!empty($end))
            $where[] = 'time < UNIX_TIMESTAMP(\'' . $end . '\')';
        $query = 'SELECT COUNT(*) FROM #__bsms_studies WHERE published = "1"';
        if (count($where) > 0)
            $query .= ' AND ' . implode(' AND ', $where);
        $biblestudy_db->setQuery($query);
        $results = $biblestudy_db->loadResult();
        return intval($results);
    }

    /**
     * Total topics in Bible Study
     * @param  string date start
     * @param string date end
     * @return int
     */
    public static function get_total_topics($start = '', $end = '')
    {
        $biblestudy_db = JFactory::getDBO();
        $where = array();
        if (!empty($start))
            $where[] = 'time > UNIX_TIMESTAMP(\'' . $start . '\')';
        if (!empty($end))
            $where[] = 'time < UNIX_TIMESTAMP(\'' . $end . '\')';
        $query = 'SELECT COUNT(*) '
            . 'FROM #__bsms_studies '
            . 'LEFT JOIN #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id) '
            . 'LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id) '
            . 'WHERE #__bsms_topics.published = "1"';
        if (count($where) > 0)
            $query .= ' AND ' . implode(' AND ', $where);
        $biblestudy_db->setQuery($query);
        return intval($biblestudy_db->loadResult());
    }

    /**
     * Get top studies
     * @return array
     */
    public static function get_top_studies()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT * FROM #__bsms_studies WHERE published = 1 ' .
            'AND hits > 0  ORDER BY hits DESC LIMIT 5');
        $results = $biblestudy_db->loadObjectList();
        $top_studies = null;
        foreach ($results as $result) {
            $top_studies .= $result->hits . ' ' . JText::_('JBS_CMN_HITS') . ' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->id . '">' . $result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
        }
        return $top_studies;
    }

    /**
     * Total media files in Bible Study
     * @return int
     */
    public static function get_total_categories()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_mediafiles WHERE published = 1');
        return intval($biblestudy_db->loadResult());
    }

    /**
     * Get top books
     * @return array
     */
    public static function get_top_books()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT booknumber, COUNT( hits ) AS totalmsg FROM jos_bsms_studies GROUP BY booknumber ORDER BY totalmsg DESC LIMIT 5');
        $results = $biblestudy_db->loadObjectList();
        $results = $biblestudy_db->query();

        if (count($results) > 0) {
            $ids = implode(',', $results);
            $biblestudy_db->setQuery('SELECT bookname FROM #__bsms_books WHERE booknumber IN (' . $ids . ') ORDER BY booknumber');
            $names = $biblestudy_db->loadResultArray();
            $i = 0;
            foreach ($results as $result) {
                $result->bookname = $names[$i++];
            }
        } else {
            $results = array();
        }

        return $results;
    }

    /**
     * Total comments
     * @return int
     */
    public static function get_total_comments()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_comments WHERE published = 1');
        return intval($biblestudy_db->loadResult());
    }

    /**
     * Get top thirty days
     * @return string
     */
    public static function get_topthirtydays()
    {
        $month = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
        $lastmonth = date("Y-m-d 00:00:01", $month);
        $biblestudy_db = JFactory::getDBO();
        $query = 'SELECT * FROM #__bsms_studies WHERE published = "1" AND hits >0 AND UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( "' . $lastmonth . '" )ORDER BY hits DESC LIMIT 5 ';
        $biblestudy_db->setQuery($query);
        $results = $biblestudy_db->loadObjectList();
        $top_studies = null;
        if (!$results) {
            $top_studies = JText::_('JBS_CPL_NO_INFORMATION');
        } else {
            foreach ($results as $result) {
                $top_studies .= $result->hits . ' ' . JText::_('JBS_CMN_HITS') . ' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->id . '">' . $result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
            }
        }
        return $top_studies;
    }

    /**
     * Get Total Meida Files
     * @return array Don't know
     */
    public static function total_mediafiles()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_mediafiles WHERE published = 1');
        return intval($biblestudy_db->loadResult());
    }

    /**
     * Get Top Downloads
     * @return string List of links to the downloads
     */
    public static function get_top_downloads()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT #__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished, #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate FROM #__bsms_mediafiles LEFT JOIN #__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id) WHERE #__bsms_mediafiles.published = 1 ' .
            'AND downloads > 0  ORDER BY downloads DESC LIMIT 5');
        $results = $biblestudy_db->loadObjectList();
        $top_studies = null;
        foreach ($results as $result) {
            $top_studies .= $result->downloads . ' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;d=' . $result->sid . '">' . $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) . '<br>';
        }
        return $top_studies;
    }

    /**
     * Get Downloads ninety
     * @return array list of download links
     */
    public static function get_downloads_ninety()
    {
        $month = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
        $lastmonth = date("Y-m-d 00:00:01", $month);
        $biblestudy_db = JFactory::getDBO();
        $query = 'SELECT #__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished, #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate FROM #__bsms_mediafiles LEFT JOIN #__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id) WHERE #__bsms_mediafiles.published = "1" AND downloads >0 AND UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( "' . $lastmonth . '" )ORDER BY downloads DESC LIMIT 5 ';
        $biblestudy_db->setQuery($query);
        $results = $biblestudy_db->loadObjectList();
        $top_studies = null;
        if (!$results) {
            $top_studies = JText::_('JBS_CPL_NO_INFORMATION');
        } else {
            foreach ($results as $result) {
                $top_studies .= $result->downloads . ' ' . JText::_('JBS_CMN_HITS') . ' - <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $result->sid . '">' . $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) . '<br>';
            }
        }
        return $top_studies;
    }

    /**
     * Total Downloads
     * @return array
     */
    public static function total_downloads()
    {
        $biblestudy_db = JFactory::getDBO();
        $biblestudy_db->setQuery('SELECT SUM(downloads) FROM #__bsms_mediafiles WHERE published = 1 AND downloads > 0');
        return intval($biblestudy_db->loadResult());
    }

    /**
     * Top Score ???
     * @return int number of scors
     */
    public static function top_score()
    {
        $final = array();
        $final2 = array();
        $admin_params = JBSMParams::getAdmin();
        $format = $admin_params->params->get('format_popular', '0');
        $db = JFactory::getDBO();
        $db->setQuery('SELECT study_id, sum(downloads + plays) as added FROM #__bsms_mediafiles where published = 1 GROUP BY study_id');
        $results = $db->loadObjectList();
        foreach ($results as $result) {
            $db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id) WHERE #__bsms_mediafiles.study_id = ' . $result->study_id);
            $hits = $db->loadObject();
            if ($format < 1) {
                $total = $result->added + $hits->hits;
            } else
                $total = $result->added;
            $link = ' <a href="index.php?option=com_biblestudy&amp;task=message.edit&amp;id=' . $hits->id . '">' . $hits->studytitle . '</a> ' . date('Y-m-d', strtotime($hits->studydate)) . '<br>';
            $final2 = array('total' => $total, 'link' => $link);
            $final[] = $final2;
        }
        rsort($final);
        array_splice($final, 5);
        $topscoretable = '';
        foreach ($final as $value) {
            foreach ($value as $scores) {
                $topscoretable .= $scores;
            }
        }
        return $topscoretable;
    }

    /**
     * Returns a System of Player
     * @return string
     */
    public static function players()
    {
        $count_noplayer = 0;
        $count_globalplayer = 0;
        $count_internalplayer = 0;
        $count_avplayer = 0;
        $count_legacyplayer = 0;
        $count_embedcode = 0;
        $db = JFactory::getDBO();
        $query = 'SELECT `player` FROM #__bsms_mediafiles WHERE `published` = 1';
        $db->setQuery($query);
        $plays = $db->loadObjectList();
        $totalplayers = count($plays);
        foreach ($plays as $player) {
            switch ($player->player) {
                case 0:
                    $count_noplayer++;
                    break;
                case '100':
                    $count_globalplayer++;
                    break;
                case '1':
                    $count_internalplayer++;
                    break;
                case '3':
                    $count_avplayer++;
                    break;
                case '7':
                    $count_legacyplayer++;
                    break;
                case '8':
                    $count_embedcode++;
                    break;
            }
        }

        $mediaplayers = '<br /><strong>' . JText::_('JBS_CMN_TOTAL_PLAYERS') . ': ' . $totalplayers . '</strong>' .
            '<br /><strong>' . JText::_('JBS_CMN_INTERNAL_PLAYER') . ': </strong>' . $count_internalplayer .
            '<br /><strong><a href="http://extensions.joomla.org/extensions/multimedia/multimedia-players/video-players-a-gallery/11572" target="blank">' . JText::_('JBS_CMN_AVPLUGIN') . '</a>: </strong>' . $count_avplayer .
            '<br /><strong>' . JText::_('JBS_CMN_LEGACY_PLAYER') . ': </strong>' . $count_legacyplayer .
            '<br /><strong>' . JText::_('JBS_CMN_NO_PLAYER_TREATED_DIRECT') . ': </strong>' . $count_noplayer .
            '<br /><strong>' . JText::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $count_globalplayer .
            '<br /><strong>' . JText::_('JBS_CMN_EMBED_CODE') . ': </strong>' . $count_embedcode;
        return $mediaplayers;
    }

    /**
     * Popups for media files
     * @return string
     */
    public static function popups()
    {
        $noplayer = 0;
        $popcount = 0;
        $inlinecount = 0;
        $globalcount = 0;
        $db = JFactory::getDBO();
        $query = 'SELECT `popup` FROM #__bsms_mediafiles WHERE `published` = 1';
        $db->setQuery($query);
        $popups = $db->loadObjectList();
        $totalmediafiles = count($popups);
        foreach ($popups as $popup) {
            switch ($popup->popup) {
                case 0:
                    $noplayer++;
                    break;
                case 1:
                    $popcount++;
                    break;
                case 2:
                    $inlinecount++;
                    break;
                case 3:
                    $globalcount++;
                    break;
            }
        }

        $popups = '<br /><strong>' . JText::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $totalmediafiles . '</strong>' .
            '<br /><strong>' . JText::_('JBS_CMN_INLINE') . ': </strong>' . $inlinecount .
            '<br /><strong>' . JText::_('JBS_CMN_POPUP') . ': </strong>' . $popcount .
            '<br /><strong>' . JText::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $globalcount .
            '<br /><strong>' . JText::_('JBS_CMN_NO_OPTION_TREATED_GLOBAL') . ': </strong>' . $noplayer;
        return $popups;
    }

}