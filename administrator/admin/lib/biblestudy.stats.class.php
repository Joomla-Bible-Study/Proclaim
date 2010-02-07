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
class jbStats {

	/**
	 * Total messages in Bible Study
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

		return count($results) > 0 ? $results : array();
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
	function get_top_categories() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT booknumber,COUNT(id) as totalmsg FROM #__bsms_studies' .
				' GROUP BY c.id ORDER BY booknumber LIMIT 5');
		$results=$biblestudy_db->loadObjectList();
		        check_dberror("Unable to load books.");

		if (count($results)>0) {
				$ids=implode(',',$results);
				$biblestudy_db->setQuery('SELECT bookname FROM #__bsms_books WHERE id IN ('.$ids.') ORDER BY booknumber');
				$names=$biblestudy_db->loadResultArray();
				$i=0;
				foreach ($results as $result)
					$result->name=$names[$i++];
		}
		else
			$results=array();
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

	/**
	 * Latest Joomla members
	 * @return string
	 */
/*	function get_latest_member() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT username FROM #__users WHERE block=0 AND activation=\'\' ORDER BY id DESC LIMIT 1');
		return $biblestudy_db->loadResult();
	}
*/
	/**
	 * Total joomla members
	 * @return int
	 */
/*	function get_total_members() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT COUNT(*) FROM #__users');
		return intval($biblestudy_db->loadresult());
	}
*/
	/**
	 * Top posters
	 * @return array
	 */
/*	function get_top_posters() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT s.userid,s.posts,u.username FROM #__fb_users as s ' .
				"\n INNER JOIN  #__users as u ON s.userid=u.id" .
				"\n WHERE s.posts > 0 ORDER BY s.posts DESC LIMIT 10");
		return count($biblestudy_db->loadObjectList()) > 0 ? $biblestudy_db->loadObjectList() : array();
	}
*/
	/**
	 * Top profiles
	 * @return array
	 */
/*	function get_top_profiles() {
		$biblestudy_db = &JFactory::getDBO();
		$biblestudy_db->setQuery('SELECT s.userid,s.uhits,u.username FROM #__fb_users as s ' .
				"\n INNER JOIN  #__users as u ON s.userid=u.id" .
				"\n WHERE s.uhits > 0 ORDER BY s.uhits DESC LIMIT 10");
		return count($biblestudy_db->loadObjectList()) > 0 ? $biblestudy_db->loadObjectList() : array();
	}
*/
	/**
	 * CB top profiles
	 * @return array
	 */
/*	 function get_top_cbprofiles() {
	 	$biblestudy_db = &JFactory::getDBO();
 		$biblestudy_db->setQuery("SELECT u.username AS user, p.hits FROM #__users AS u"
			. "\n LEFT JOIN #__comprofiler AS p ON p.user_id = u.id"
			. "\n WHERE p.hits > 0 ORDER BY p.hits DESC LIMIT 10");
		return count($biblestudy_db->loadObjectList()) > 0 ? $biblestudy_db->loadObjectList() : array();
	 }*/
}

?>