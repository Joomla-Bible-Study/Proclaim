<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Migration Class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBSMigrate
{

	/**
	 * Migrate versions
	 *
	 * @return boolean
	 */
	public function migrate()
	{
		$app              = JFactory::getApplication();
		$db               = JFactory::getDBO();
		$olderversiontype = 0;

		// First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
		// Check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version

		$tables         = $db->getTableList();
		$prefix         = $db->getPrefix();
		$versiontype    = '';
		$currentversion = false;
		$oldversion     = false;
		$jbsexists      = false;

		// Check to see if version is newer then 7.0.2
		foreach ($tables as $table)
		{
			$studies              = $prefix . 'bsms_update';
			$currentversionexists = substr_count($table, $studies);

			if ($currentversionexists > 0)
			{
				$currentversion = true;
				$versiontype    = 1;
			}
		}
		if ($versiontype !== 1)
		{
			foreach ($tables as $table)
			{
				$studies              = $prefix . 'bsms_version';
				$currentversionexists = substr_count($table, $studies);

				if ($currentversionexists > 0)
				{
					$currentversion = true;
					$versiontype    = 2;
				}
			}
		}

		// Only move forward if a current version type is not found
		if (!$currentversion)
		{
			// Now let's check to see if there is an older database type (prior to 6.2)
			$oldversion = false;

			foreach ($tables as $table)
			{
				$studies          = $prefix . 'bsms_schemaVersion';
				$oldversionexists = substr_count($table, $studies);

				if ($oldversionexists > 0)
				{
					$oldversion       = true;
					$olderversiontype = 1;
					$versiontype      = 3;
				}
			}
			if (!$oldversion)
			{
				foreach ($tables as $table)
				{
					$studies            = $prefix . 'bsms_schemaversion';
					$olderversionexists = substr_count($table, $studies);

					if ($olderversionexists > 0)
					{
						$oldversion       = true;
						$olderversiontype = 2;
						$versiontype      = 3;
					}
				}
			}
		}

		// Finally if both current version and old version are false, we double check to make sure there are no JBS tables in the database.
		if (!$currentversion && !$oldversion)
		{
			foreach ($tables as $table)
			{
				$studies   = $prefix . 'bsms_studies';
				$jbsexists = substr_count($table, $studies);

				if (!$jbsexists)
				{
					$versiontype = 5;
				}
				if ($jbsexists > 0)
				{
					$versiontype = 4;
				}
			}
		}

		// Since we are going to install something, let's check to see if there is a version table, and create it if it isn't there
		foreach ($tables as $table)
		{
			$studies    = $prefix . 'bsms_version';
			$jbsexists1 = substr_count($table, $studies);

			if (!$jbsexists1)
			{
				$query = "CREATE TABLE IF NOT EXISTS `#__bsms_version` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `version` varchar(20) NOT NULL,
                      `versiondate` date NOT NULL,
                      `installdate` date NOT NULL,
                      `build` varchar(20) NOT NULL,
                      `versionname` varchar(40) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ";
				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

					return false;
				}
			}
			$update     = $prefix . 'bsms_update';
			$jbsexists2 = substr_count($table, $update);

			if ($jbsexists2 === 0)
			{
				$query = "CREATE TABLE IF NOT EXISTS `#__bsms_update` (
                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `version` VARCHAR(255) DEFAULT NULL,
                        PRIMARY KEY (id)
                        ) DEFAULT CHARSET=utf8";
				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

					return false;
				}
			}
		}

		// Now we run a switch case on the versiontype and run an install routine accordingly
		// @todo need to rewright this like we do the sql update on allupdate.php but for now will work it this way.
		switch ($versiontype)
		{
			case 1:
				self::corectversions();
				/* Find Last updated Version in Update table */
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__bsms_update')
					->order($db->qn('version') . ' desc');
				$db->setQuery($query);
				$updates = $db->loadObject();
				$version = $updates->version;

				switch ($version)
				{
					case '7.0.1':
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
					case '7.0.1.1':
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
					case '7.0.2':
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
					case '7.0.3':
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
					case '7.0.4':
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
				}
				break;
			case 2:

				// This is a current database version so we check to see which version. We query to get the highest build in the version table
				$query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
				$db->setQuery($query);
				$db->query();
				$version = $db->loadObject();

				switch ($version->build)
				{
					case '700':
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '624':
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '623':
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '622':
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '615':
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '614':
						if (!$this->update614())
						{
							return false;
						}
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
					case null:
						$app->enqueueMessage('SOME_ERROR_CODE', 'Bad DB Upload', 'notice');

						return false;
						break;
				}
				break;

			case 3:

				// This is an older version of the software so we check it's version
				if ($olderversiontype == 1)
				{
					$db->setQuery("SELECT schemaVersion  FROM #__bsms_schemaVersion");
				}
				else
				{
					$db->setQuery("SELECT schemaVersion FROM #__bsms_schemaversion");
				}
				$schema = $db->loadResult();

				switch ($schema)
				{
					case '600':
						$app->enqueueMessage('' . JText::_('JBS_IBM_VERSION_TOO_OLD') . '', 'notice');

						return false;
						break;

					case '608':
						if (!$this->update611())
						{
							return false;
						}
						if (!$this->update613())
						{
							return false;
						}
						if (!$this->update614())
						{
							return false;
						}
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '611':
						if (!$this->update613())
						{
							return false;
						}
						if (!$this->update614())
						{
							return false;
						}
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;

					case '613':
						if (!$this->update614())
						{
							return false;
						}
						if (!$this->update622())
						{
							return false;
						}
						if (!$this->update623())
						{
							return false;
						}
						if (!$this->update700())
						{
							return false;
						}
						if (!$this->update701())
						{
							return false;
						}
						if (!$this->allupdate())
						{
							return false;
						}
						if (!$this->update710())
						{
							return false;
						}
						break;
				}
				break;

			case 4:

				// There is a version installed, but it is older than 6.0.8 and we can't upgrade it
				$app->enqueueMessage('SOME_ERROR_CODE', 'JBS_IBM_VERSION_TOO_OLD', 'notice');

				return false;
				break;
		}

		return true;
	}

	/**
	 * Update for 6.1.1
	 *
	 * @return string
	 */
	private function update611()
	{
		JLoader::register('jbs611Install', dirname(__FILE__) . '/migration/biblestudy.611.upgrade.php');
		$install = new jbs611Install;

		if (!$install->upgrade611())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 6.1.3
	 *
	 * @return string
	 */
	private function update613()
	{
		JLoader::register('jbs613Install', dirname(__FILE__) . '/migration/biblestudy.613.upgrade.php');
		$install = new jbs613Install;

		if (!$install->upgrade613())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 6.1.4
	 *
	 * @return string
	 */
	private function update614()
	{
		JLoader::register('jbs614Install', dirname(__FILE__) . '/migration/biblestudy.614.upgrade.php');
		$install = new jbs614Install;

		if (!$install->upgrade614())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 6.2.2
	 *
	 * @return string
	 */
	private function update622()
	{
		JLoader::register('jbs622Install', dirname(__FILE__) . '/migration/biblestudy.622.upgrade.php');
		$install = new jbs622Install;

		if (!$install->upgrade622())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 6.2.3
	 *
	 * @return string
	 */
	private function update623()
	{
		JLoader::register('jbs623Install', dirname(__FILE__) . '/migration/biblestudy.623.upgrade.php');
		$install = new jbs623Install;

		if (!$install->upgrade623())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 7.0.0
	 *
	 * @return string
	 */
	private function update700()
	{
		JLoader::register('jbs700Install', dirname(__FILE__) . '/migration/biblestudy.700.upgrade.php');
		$install = new jbs700Install;

		if (!$install->upgrade700())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 7.0.1
	 *
	 * @return string
	 */
	private function update701()
	{
		JLoader::register('updatejbs701', dirname(__FILE__) . '/migration/update701.php');
		$install = new updatejbs701;

		if (!$install->do701update())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update for 7.1.0
	 *
	 * @return string|boolean
	 */
	private function update710()
	{
		JLoader::register('JBS710Update', dirname(__FILE__) . '/install/updates/update710.php');
		$migrate = new JBS710Update;

		if (!$migrate->update710())
		{
			return false;
		}

		return true;
	}

	/**
	 * Update that is applyed to all migrations
	 *
	 * @return string
	 */
	private function allupdate()
	{
		JLoader::register('updatejbsALL', dirname(__FILE__) . '/migration/updateALL.php');
		$install = new updatejbsALL;

		if (!$install->doALLupdate())
		{
			return false;
		}

		return true;
	}

	/**
	 * Correct problem in are update table under 7.0.2 systems
	 *
	 * @return boolean
	 */
	public static function corectversions()
	{
		$db = JFactory::getDBO();
		/* Find Last updated Version in Update table */
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_update');
		$db->setQuery($query);
		$updates = $db->loadObjectlist();

		foreach ($updates AS $value)
		{
			/* Check to see if Bad version is in key 3 */

			if (($value->id === '3') && ($value->version !== '7.0.1.1'))
			{
				/* Find Last updated Version in Update table */
				$query = "INSERT INTO `#__bsms_update` (id,version) VALUES (3,'7.0.1.1')
                            ON DUPLICATE KEY UPDATE version= '7.0.1.1';";
				$db->setQuery($query);

				if (!$db->execute())
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_OPERATION_FAILED'), 'error');

					return false;
				}
			}
		}

		return true;
	}

}
