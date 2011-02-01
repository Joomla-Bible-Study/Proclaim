<?php
/**
* @version $Id:biblestudy.stats.class.php  $
* Joomla Bible Study Component
* @package biblestudy
*
* @Copyright (C) 2010 Joomla Bible Study Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.JoomlaBibleStudy.org
*
* Based on Kunena & FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/

// Dont allow direct linking
defined( '_JEXEC' ) or die('Restricted access');

/**
* Bible Study stats support class
* @package com_biblestudy
*/
require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_HELPERS .DS. 'helper.php');
class jbStats {

    /**
     * Total plays of media files per study
     *
	*/
    function totalplays($id)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT sum(m.plays), m.study_id, m.published, s.id FROM #__bsms_mediafiles AS m'
        .' LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id)'
        .' WHERE m.study_id = '.$id;
        $db->setQuery($query);
        $db->query();
        $plays = $db->loadResult();
        return (int)$plays;
    }

	 /** Total messages in Bible Study
	 * @param  string date start
	 * @param string date end
	 * @return int
	 */
	function get_total_messages($start='',$end='') {
		$biblestudy_db = &JFactory::getDBO();
		$where=array();
		if (!empty($start))
			$where[]='time > UNIX_TIMESTAMP(\'' . $start. '\')';
		if (!empty($end))
			$where[]='time < UNIX_TIMESTAMP(\'' . $end . '\')';
		$query='SELECT COUNT(*) FROM #__bsms_studies WHERE published = "1"';
		if (count($where)>0)
			$query.=' AND '.implode(' AND ',$where);
		$biblestudy_db->setQuery($query);
		return intval($biblestudy_db->loadResult());
	}

	/**
	 * Total topics in Bible Study
	 * @param  string date start
	 * @param string date end
	 * @return int
	 */
	function get_total_topics($start='',$end='') {
		$biblestudy_db = &JFactory::getDBO();
		$where=array();
		if (!empty($start))
			$where[]='time > UNIX_TIMESTAMP(\'' . $start. '\')';
		if (!empty($end))
			$where[]='time < UNIX_TIMESTAMP(\'' . $end . '\')';
		$query='SELECT COUNT(*) FROM #__bsms_studies WHERE published = "1" and topics_id > 0';
		if (count($where)>0)
			$query.=' AND '.implode(' AND ',$where);
		$biblestudy_db->setQuery($query);
		return intval($biblestudy_db->loadResult());
	}

	/**
	 * Get top studies
	 * @return array
	 */
	function get_top_studies() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT * FROM #__bsms_studies WHERE published = 1 ' .
				'AND hits > 0  ORDER BY hits DESC LIMIT 5');
		$results=$biblestudy_db->loadObjectList();
		        check_dberror("Unable to load messages.");
  		$top_studies = null;
		foreach ($results as $result)
		{
			$top_studies .= $result->hits.' '.JText::_('JBS_CMN_HITS').' - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->id.'">'.$result->studytitle.'</a> - '.date('Y-m-d', strtotime($result->studydate)).'<br>';
		}
		//return count($results) > 0 ? $results : array();
		return  $top_studies;
	}

	/**
	 * Total media files in Bible Study
	 * @return int
	 */
	function get_total_categories() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_mediafiles WHERE published = 1');
		return intval($biblestudy_db->loadResult());
	}
	/**
	 * Get top books
	 * @return array
	 */
	function get_top_books() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT booknumber, COUNT( hits ) AS totalmsg FROM jos_bsms_studies GROUP BY booknumber ORDER BY totalmsg DESC LIMIT 5');
		$results=$biblestudy_db->loadObjectList();
		$results=$biblestudy_db->query();
		        check_dberror("Unable to load books.");

		if (count($results)>0) {
				$ids=implode(',',$results);
				$biblestudy_db->setQuery('SELECT bookname FROM #__bsms_books WHERE booknumber IN ('.$ids.') ORDER BY booknumber');
				$names=$biblestudy_db->loadResultArray();
				$i=0;
				foreach ($results as $result)
					{$result->bookname=$names[$i++];}
		}
		else
		{$results=array();}

		return $results;
	}
	/**
	 * Total comments
	 * @return int
	 */
	function get_total_comments() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_comments WHERE published = 1');
		return intval($biblestudy_db->loadResult());
	}


	function get_topthirtydays() {
		$month = mktime(0, 0, 0, date("m")-3 , date("d"), date("Y"));
		$lastmonth = date("Y-m-d 00:00:01",$month);
		$biblestudy_db = &JFactory::getDBO();
		$query = 'SELECT * FROM #__bsms_studies WHERE published = "1" AND hits >0 AND UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( "'.$lastmonth.'" )ORDER BY hits DESC LIMIT 5 ';
		$biblestudy_db->setQuery($query);
		$results = $biblestudy_db->loadObjectList();
		$top_studies = null;
		if (!$results)
		{
			$top_studies = JText::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->hits.' '.JText::_('JBS_CMN_HITS').' - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->id.'">'.$result->studytitle.'</a> - '.date('Y-m-d', strtotime($result->studydate)).'<br>';
			}
		}
		return  $top_studies;
		}
	function total_mediafiles()
	{
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT COUNT(*) FROM #__bsms_mediafiles WHERE published = 1');
		return intval($biblestudy_db->loadResult());
	}
function get_top_downloads() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT #__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished, #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate FROM #__bsms_mediafiles LEFT JOIN #__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id) WHERE #__bsms_mediafiles.published = 1 ' .
				'AND downloads > 0  ORDER BY downloads DESC LIMIT 5');
		$results=$biblestudy_db->loadObjectList();
		        check_dberror("Unable to load messages.");
  		$top_studies = null;
		foreach ($results as $result)
		{
			$top_studies .= $result->downloads.' - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->sid.'">'.$result->stitle.'</a> - '.date('Y-m-d', strtotime($result->sdate)).'<br>';
		}
		return  $top_studies;
	}

	function get_downloads_ninety() {
		$month = mktime(0, 0, 0, date("m")-3 , date("d"), date("Y"));
		$lastmonth = date("Y-m-d 00:00:01",$month);
		$biblestudy_db = &JFactory::getDBO();
		$query = 'SELECT #__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished, #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate FROM #__bsms_mediafiles LEFT JOIN #__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id) WHERE #__bsms_mediafiles.published = "1" AND downloads >0 AND UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( "'.$lastmonth.'" )ORDER BY downloads DESC LIMIT 5 '; 
		$biblestudy_db->setQuery($query);
		$results = $biblestudy_db->loadObjectList();
		$top_studies = null;
		if (!$results)
		{
			$top_studies = JText::_('JBS_CPL_NO_INFORMATION');
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->downloads.' '.JText::_('JBS_CMN_HITS').' - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->sid.'">'.$result->stitle.'</a> - '.date('Y-m-d', strtotime($result->sdate)).'<br>';
			}
		}
		return  $top_studies;
	}

function total_downloads()
	{
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT SUM(downloads) FROM #__bsms_mediafiles WHERE published = 1 AND downloads > 0');
		return intval($biblestudy_db->loadResult());
	}

function top_score()
	{
 	$final = array();
    $final2 = array();
    $admin_params = getAdminsettings();
	$format = $admin_params->get('format_popular','0');
	$db = &JFactory::getDBO();
	$db->setQuery('SELECT study_id, sum(downloads + plays) as added FROM #__bsms_mediafiles where published = 1 GROUP BY study_id');
	$db->query();
	$results = $db->loadObjectList();
	foreach ($results as $result)
		{
			$db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id) WHERE #__bsms_mediafiles.study_id = '.$result->study_id);
			$db->query();
			$hits = $db->loadObject();
			if ($format < 1){$total = $result->added + $hits->hits;}
            else $total = $result->added;
			$link =' <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$hits->id.'">'.$hits->studytitle.'</a> '.date('Y-m-d', strtotime($hits->studydate)).'<br>';
			$final2 = array('total'=> $total, 'link'=> $link);
			$final[] = $final2;
		}
	rsort($final);
	array_splice($final,5);
    $topscoretable = '';
	foreach ($final as $key=>$value)
		{
			foreach ($value as $scores)
			{
				$topscoretable .= $scores;
			}
		}
	return $topscoretable;
}

function players()
{
    $db = &JFactory::getDBO();
    
    //No Player
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` IS NULL';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_noplayer = $db->loadObjectList();
    $count_noplayer = count($results_noplayer);
    
    //100 = Global
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` = 100';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_globalplayer = $db->loadObjectList();
    $count_globalplayer = count($results_globalplayer);
    
    //0 = Direct Link
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` = 0';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_directplayer = $db->loadObjectList();
    $count_directplayer = count($results_directplayer);
    
    //1 = internal player
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` = 1';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_internalplayer = $db->loadObjectList();
    $count_internalplayer = count($results_internalplayer);
    
    
    //3 = All Videos
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` = 3';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_avplayer = $db->loadObjectList();
    $count_avplayer = count($results_avplayer);
    
    //7 = legacy mp3 player
    $query = 'SELECT `id`, `player` FROM #__bsms_mediafiles WHERE `player` = 7';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results_legacyplayer = $db->loadObjectList();
    $count_legacyplayer = count($results_legacyplayer);
    
    $mediaplayers = '<strong>'.JText::_('JBS_CMN_DIRECT_LINK').': </strong>'.$count_directplayer.
    '<br /><strong>'.JText::_('JBS_CMN_INTERNAL_PLAYER').': </strong>'.$count_internalplayer.
    '<br /><strong><a href="http://extensions.joomla.org/extensions/multimedia/multimedia-players/video-players-a-gallery/11572" target="blank">'.JText::_('JBS_CMN_AVPLUGIN').'</a>: </strong>'.$count_avplayer.
    '<br /><strong>'.JText::_('JBS_CMN_LEGACY_PLAYER').': </strong>'.$count_legacyplayer.
    '<br /><strong>'.JText::_('JBS_CMN_NO_PLAYER_TREATED_DIRECT').': </strong>'.$count_noplayer; //dump ($mediaplayers, 'mediaplayers: ');
    return $mediaplayers;
}

function popups()
{
    $db = &JFactory::getDBO();
    
    //1 popup
    $query = 'SELECT id, params FROM #__bsms_mediafiles WHERE `popup` = 1';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $popcount = count($results);
    
    //2 inline
    $query = 'SELECT id, params FROM #__bsms_mediafiles WHERE `popup` = 2';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $inlinecount = count($results);
    
    
    //3 use Global Settings
    $query = 'SELECT id, params FROM #__bsms_mediafiles WHERE `popup` = 3';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $globalcount = count($results);
    
    //NULL is no player listed
    $query = 'SELECT id, params FROM #__bsms_mediafiles WHERE `popup` IS NULL';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $noplayer = count($results);
   
    $popups = '<strong>'.JText::_('JBS_CMN_INLINE').': </strong>'.$inlinecount.
    '<br /><strong>'.JText::_('JBS_CMN_POPUP').': </strong>'.$popcount.
    '<br /><strong>'.JText::_('JBS_CMN_GLOBAL_SETTINGS').': </strong>'.$globalcount.
    '<br /><strong>'.JText::_('JBS_CMN_NO_OPTION_TREATED_GLOBAL').': </strong>'.$noplayer;
    return $popups;
}
}

?>
