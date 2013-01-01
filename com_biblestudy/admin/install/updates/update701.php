<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Update for 7.0.1 class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class Updatejbs701
{

	/**
	 * Upgrade for 7.0.1
	 *
	 * @return boolean
	 */
	public function do701update()
	{

		$db = JFactory::getDBO();

		// Modify table topics
		// FIXME Tom
		$tables      = $db->getTableFields('#__bsms_topics');
		$languagetag = 0;
		$paramstag   = 0;

		foreach ($tables as $table)
		{
			foreach ($table as $key => $value)
			{
				if (substr_count($key, 'languages'))
				{
					$languagetag = 1;
					$query       = 'ALTER TABLE #__bsms_topics CHANGE `languages` `params` varchar(511) NULL';
					$db->setQuery($query);

					if (!$db->execute())
					{
						JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

						return false;
					}
				}
				elseif (substr_count($key, 'params'))
				{
					$paramstag = 1;
				}
			}
			if (!$languagetag && !$paramstag)
			{
				$query = 'ALTER TABLE #__bsms_topics ADD `params` varchar(511) NULL';
				$db->setQuery($query);

				if (!$db->execute())
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

					return false;
				}
			}
		}

		$fixtopics = $this->updatetopics();

		if (!$fixtopics)
		{
			return false;
		}

		return true;
	}

	/**
	 * Update the Topics
	 *
	 * @return boolean
	 */
	public function updatetopics()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->insert('#__bsms_studytopics (study_id, topic_id)')
				->select('#__bsms_studies.id, #__bsms_studies.topics_id ')
				->from('__bsms_studies')
				->where('#__bsms_studies.topics_id > 0');
		/* Need to test this out.
		* $query = 'INSERT INTO #__bsms_studytopics (study_id, topic_id) SELECT #__bsms_studies.id,
		* #__bsms_studies.topics_id FROM #__bsms_studies WHERE #__bsms_studies.topics_id > 0'; */
		$db->setQuery($query);

		if (!$db->execute())
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'error');

			return false;
		}

		return true;
	}

}
