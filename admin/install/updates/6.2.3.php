<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

defined('_JEXEC') or die;

/**
 * Update for 6.2.3 class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Migration623
{
	/**
	 * Start of upgrade
	 *
	 * @param   JDatabaseDriver  $db  Data bass driver
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function up($db)
	{
		// We adjust those rows that have internal_popup set to 0 and we change it to 2
		$query = $db->getQuery(true);
		$query
			->select('id, params')
			->from('#__bsms_mediafiles');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results)
		{
			foreach ($results AS $result)
			{
				$isplayertype = substr_count($result->params, 'internal_popup=0');

				if ($isplayertype)
				{
					$oldparams = $result->params;
					$newparams = str_replace('internal_popup=0', 'internal_popup=2', $oldparams);
					$query     = $db->getQuery(true);
					$query
						->update('#__bsms_mediafiles')
						->set($db->qn('params') . ' = ' . $db->q($newparams))
						->where('id = ' . (int) $db->q($result->id));
					$db->setQuery($query);

					if (!$db->execute())
					{
						JFactory::getApplication()
							->enqueueMessage(
								"Build 623: " . JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

						return false;
					}
				}
			}
		}

		$data              = new stdClass;
		$data->version     = '6.2.3';
		$data->installdate = '2010-11-03';
		$data->build       = '623';
		$data->versionname = '1Samuel';
		$data->versiondate = '2010-11-03';

		if (!$db->insertObject('#__bsms_version', $data))
		{
			return false;
		}

		$data1              = new stdClass;
		$data1->version     = '6.2.4';
		$data1->installdate = '2010-11-09';
		$data1->build       = '623';
		$data1->versionname = '2Samuel';
		$data1->versiondate = '2010-11-09';

		if (!$db->insertObject('#__bsms_version', $data1))
		{
			return false;
		}

		return true;
	}
}
