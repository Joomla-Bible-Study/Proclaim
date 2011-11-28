<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

class updatejbs701
{

	function do701update()
	{

		$db = JFactory::getDBO();

		$query = "ALTER Table `#__bsms_admin` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_books` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_comments` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_folders` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_locations` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_media` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_mediafiles` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_message_type` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_mimetype` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_podcast` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_search` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_series` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_servers` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_share` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_studies` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_studytopics` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_teachers` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_templates` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_topics` MODIFY id int(10) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		$query = "ALTER Table `#__bsms_version` MODIFY id int(3) unsigned NOT NULL AUTO_INCREMENT";
		$result = $this->performdb($query);
		if (!$result){
			return false;
		}

		// modify table topics
		$tables = $db->getTableFields('#__bsms_topics');
		$languagetag = 0;
		$paramstag = 0;
		//print_r($tables);
		foreach ($tables as $table)
		{
			foreach ($table as $key=>$value)
			{
				if (substr_count($key,'languages'))
				{
					$languagetag = 1;
					$query = 'ALTER TABLE #__bsms_topics CHANGE `languages` `params` varchar(511) NULL';
					$db->setQuery($query);
					$db->query();
					$error = $db->getErrorNum();
					if ($error){
						return false;
					}
					 
				}
				elseif(substr_count($key,'params'))
				{
					$paramstag = 1;

				}

			}
			if (!$languagetag && !$paramstag)
			{
				$query = 'ALTER TABLE #__bsms_topics ADD `params` varchar(511) NULL';
				$db->setQuery($query);
				$db->query();
				$error = $db->getErrorNum();
				if ($error){
					return false;
				}
			}
		}

		$fixtopics = $this->updatetopics();
		if (!$fixtopics){
			return false;
		}

		return true;
	}

	function updatetopics()
	{
		$db = JFactory::getDBO();
		$query = 'INSERT INTO #__bsms_studytopics (study_id, topic_id) SELECT #__bsms_studies.id, #__bsms_studies.topics_id FROM #__bsms_studies WHERE #__bsms_studies.topics_id > 0';
		$db->setQuery($query);
		$db->query();
		if ($db->getErrorNum() != 0)
		{
			return false;
		}
		return true;
	}

	function performdb($query)
	{
		$db = JFactory::getDBO();
		$results = false;
		$db->setQuery($query);
		$db->query();
		if ($db->getErrorNum() != 0)
		{
			return false;
		}
		return true;
	}

}