<?php
/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
defined('_JEXEC') or die();

class Tablestudytopics extends JTable
{
	var $id = null;
	var $study_id = null;
	var $topic_id = null;

	function __construct(&$db)
	{
		parent::__construct( '#__bsms_studytopics', 'id', $db );
	}
}