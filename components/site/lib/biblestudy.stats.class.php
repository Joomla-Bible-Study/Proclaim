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
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once ($path1.'helper.php');
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
			$top_studies .= $result->hits.' hits - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->id.'">'.$result->studytitle.'</a> - '.date('Y-m-d', strtotime($result->studydate)).'<br>';
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
		//$biblestudy_db->setQuery('SELECT booknumber, COUNT(hits) as totalmsg FROM #__bsms_studies' .
		//		' GROUP BY id ORDER BY booknumber LIMIT 5');

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
		$lastmonth = date("Y-m-d 00:00:01",$month); //echo $lastmonth;
		$biblestudy_db = &JFactory::getDBO();
		$query = 'SELECT * FROM #__bsms_studies WHERE published = "1" AND hits >0 AND UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( "'.$lastmonth.'" )ORDER BY hits DESC LIMIT 5 '; //echo $query;
			//$query = 'SELECT * FROM #__bsms_studies WHERE UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( "2009-02-10 00:00:00" )ORDER BY hits DESC LIMIT 5 ';

		$biblestudy_db->setQuery($query);
		$results = $biblestudy_db->loadObjectList(); //dump ($results, 'results: ');
		$top_studies = null;
		if (!$results)
		{
			$top_studies = 'No information available';
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->hits.' hits - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->id.'">'.$result->studytitle.'</a> - '.date('Y-m-d', strtotime($result->studydate)).'<br>';
			}
		}
		//return count($results) > 0 ? $results : array();
		//dump ($results, 'results: ');
		return  $top_studies;
		//return intval($biblestudy_db->loadResult());
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
		//return count($results) > 0 ? $results : array();
		return  $top_studies;
	}

	function get_downloads_ninety() {
		$month = mktime(0, 0, 0, date("m")-3 , date("d"), date("Y"));
		$lastmonth = date("Y-m-d 00:00:01",$month); //echo $lastmonth;
		$biblestudy_db = &JFactory::getDBO();
		$query = 'SELECT #__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished, #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate FROM #__bsms_mediafiles LEFT JOIN #__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id) WHERE #__bsms_mediafiles.published = "1" AND downloads >0 AND UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( "'.$lastmonth.'" )ORDER BY downloads DESC LIMIT 5 '; //echo $query;
			//$query = 'SELECT * FROM #__bsms_studies WHERE UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( "2009-02-10 00:00:00" )ORDER BY hits DESC LIMIT 5 ';

		$biblestudy_db->setQuery($query);
		$results = $biblestudy_db->loadObjectList(); //dump ($results, 'results: ');
		$top_studies = null;
		if (!$results)
		{
			$top_studies = 'No information available';
		}
		else
		{
			foreach ($results as $result)
			{
				$top_studies .= $result->downloads.' hits - <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$result->sid.'">'.$result->stitle.'</a> - '.date('Y-m-d', strtotime($result->sdate)).'<br>';
			}
		}
		//return count($results) > 0 ? $results : array();
		//dump ($results, 'results: ');
		return  $top_studies;
		//return intval($biblestudy_db->loadResult());
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
	$db = &JFactory::getDBO();
	$db->setQuery('SELECT study_id, sum(downloads + plays) as added FROM #__bsms_mediafiles where published = 1 GROUP BY study_id');
	$db->query();
	$results = $db->loadObjectList();
	foreach ($results as $result)
		{
			$db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id) WHERE #__bsms_mediafiles.study_id = '.$result->study_id);
			$db->query();
			$hits = $db->loadObject();
			$total = $result->added + $hits->hits;
			$link =' <a href="index.php?option=com_biblestudy&view=studiesedit&task=edit&layout=form&cid[]='.$hits->id.'">'.$hits->studytitle.'</a> '.date('Y-m-d', strtotime($hits->studydate)).'<br>';
			$final2 = array('total'=> $total, 'link'=> $link);
			$final[] = $final2;
		}
	rsort($final);
	array_splice($final,5);
	//$topscoretable = '<table cellspacing="0" cellpadding="0">';
    $topscoretable = '';
	foreach ($final as $key=>$value)
		{
		//	$topscoretable .= '<tr><td>';
			foreach ($value as $scores)
			{
				$topscoretable .= $scores;
			}
		//	$topscoretable .= '</td></tr>';
		}
//	$topscoretable .= '</table>';
	return $topscoretable;
}

function top_score_site()
	{
	$t = JRequest::getInt('templatemenuid',1,'get');
	$admin_params = getAdminsettings();
	$limit = $admin_params->get('popular_limit','25');
	$top = '<select onchange="goTo()" id="urlList"><option value="">- '.JText::_('Select A Popular Study').' -</option>';
 	$final = array();
    $final2 = array();
    $user =& JFactory::getUser();
	$level_user = $user->get('gid');
	$db = &JFactory::getDBO();
	$db->setQuery('SELECT study_id, sum(downloads + plays) as added FROM #__bsms_mediafiles where published = 1 GROUP BY study_id');

	
	$db->query();
	$results = $db->loadObjectList();
	foreach ($results as $result)
		{
			$db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id) WHERE #__bsms_studies.show_level <= '.$level_user.' AND #__bsms_mediafiles.study_id = '.$result->study_id);
			$db->query();
			$hits = $db->loadObject();
			if (!$hits->studytitle){$name = $hits->id;}else{$name = $hits->studytitle;}
			$total = $result->added + $hits->hits;
			$selectvalue = JRoute::_(JURI::base().'index.php?option=com_biblestudy&view=studydetails&id='.$hits->id.'&templatemenuid='.$t);
			$selectdisplay = '<strong>'.$name.'</strong> - '.JText::_('Score').': '.$total;
			$final2 = array('score'=>$total,'select'=> $selectvalue, 'display'=> $selectdisplay);
			$final[] = $final2;
		}
	rsort($final);
	array_splice($final,$limit);

	foreach ($final as $topscore)
	{

		$top .= '<option value="'.$topscore['select'].'">'.$topscore['display'].'</option>';

	}
$top .= '</select>';
	return $top;

}

function players()
{
    $db = &JFactory::getDBO();
    $query = 'SELECT id, params FROM #__bsms_mediafiles';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $direct = 'player=0';
    $internal = 'player=1';
    $avr = 'player=2';
    $av = 'player=3';
    $add = 0;
    $directcount = 0;
    $internalcount = 0;
    $avrcount = 0;
    $avcount = 0;
    foreach ($results AS $result)
    {
        $param = $result->params;
        $isdirect = substr_count($param,$direct);
        $directcount = $directcount + $isdirect;
        $isinternal = substr_count($param,$internal);
        $internalcount = $internalcount + $isinternal;
        $isavr = substr_count($param,$avr);
        $avrcount = $avrcount + $isavr;
        $isav = substr_count($param,$av);
        $avcount = $avcount + $isav;
        $total = $directcount + $internalcount + $avrcount + $avcount;
        $noplayer = $num_rows - $total;
    }
    $mediaplayers = '<strong>'.JText::_('Direct Link').': </strong>'.$directcount.
    '<br /><strong>'.JText::_('Internal Player').': </strong>'.$internalcount.
    '<br /><strong>'.JText::_('All Videos Reloaded').': </strong>'.$avrcount.
    '<br /><strong>'.JText::_('All Videos Plugin').': </strong>'.$avcount.
    '<br /><strong>'.JText::_('No Player - treated as direct').': </strong>'.$noplayer; //dump ($mediaplayers, 'mediaplayers: ');
    return $mediaplayers;
}

function popups()
{
    $db = &JFactory::getDBO();
    $query = 'SELECT id, params FROM #__bsms_mediafiles';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $results = $db->loadObjectList();
    $inline = 'internal_popup=0';
    $pop = 'internal_popup=1';
    $global = 'internal_popup=3';
 //   $avr = 'player=2';
//    $av = 'player=3';
    $add = 0;
//    $directcount = 0;
//    $internalcount = 0;
    $inlinecount = 0;
    $popcount = 0;
    $globalcount = 0;
  //  $avrcount = 0;
 //   $avcount = 0;
    foreach ($results AS $result)
    {
        $param = $result->params;
        $isinline = substr_count($param,$inline);
        $inlinecount = $inlinecount + $isinline;
        $ispop = substr_count($param,$pop);
        $popcount = $popcount + $ispop;
        $isglobal =substr_count($param,$global);
        $globalcount = $globalcount + $isglobal;
     //   $isavr = substr_count($param,$avr);
     //   $avrcount = $avrcount + $isavr;
     //   $isav = substr_count($param,$av);
     //   $avcount = $avcount + $isav;
        $total = $inlinecount + $popcount + $globalcount;
        $noplayer = $num_rows - $total;
    }
    $popups = '<strong>'.JText::_('Inline').': </strong>'.$inlinecount.
    '<br /><strong>'.JText::_('Popup').': </strong>'.$popcount.
    '<br /><strong>'.JText::_('Global Settings').': </strong>'.$globalcount.
    '<br /><strong>'.JText::_('No Option Set - treated as global').': </strong>'.$noplayer; //dump ($mediaplayers, 'mediaplayers: ');
    return $popups;
}
}

?>
