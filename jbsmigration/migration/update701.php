<?php

/**
 * @version $Id: update701.php 2085 2011-11-11 21:10:18Z bcordis $
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
					$msg = $this->performdb($query);
            		if (!$msg)
            		{
            			$messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
            		}
            		else
            		{
            			$messages[] = $msg;
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
				$msg = $this->performdb($query);
            		if (!$msg)
            		{
            			$messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
            		}
            		else
            		{
            			$messages[] = $msg;
            		}
			}
		}

		$messages[] = $this->updatetopics();
		

	
        $results = array('build'=>'701','messages'=>$messages);

		return $results;
	}

	function updatetopics()
	{
		$db = JFactory::getDBO();
		$query = 'INSERT INTO #__bsms_studytopics (study_id, topic_id) SELECT #__bsms_studies.id, #__bsms_studies.topics_id FROM #__bsms_studies WHERE #__bsms_studies.topics_id > 0';
		$msg = $this->performdb($query);
            		if (!$msg)
            		{
            			$messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
            		}
            		else
            		{
            			$messages[] = $msg;
            		}
        return $messages;
	}
    
    function performdb($query)
	{
		$db = JFactory::getDBO();
		$results = false;
		$db->setQuery($query);
		$db->query();
		if ($db->getErrorNum() != 0)
		{
			$results = JText::_('JBS_EI_DB_ERROR').': '.$db->getErrorNum()."<br /><font color=\"red\">";
			$results .= $db->stderr(true);
			$results .= "</font>";
			return $results;
		}
		else
		{
			$results = false; return $results;
		}
	}
}