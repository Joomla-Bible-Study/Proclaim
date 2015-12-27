<?php
/**
 * BibleStudy Component
 *
 * @package       BibleStudy.Installer
 *
 * @copyright (C) 2008 - 2015 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

class BibleStudyVersion
{
	/**
	 * Get warning for unstable releases
	 *
	 * @param    string $msg Message to be shown containing two %s parameters for version (2.0.0RC) and version type (GIT, RC, BETA etc)
	 *
	 * @return    string    Warning message
	 * @since    1.6
	 */
	public function getVersionWarning($msg = 'COM_BIBLESTUDY_VERSION_WARNING')
	{
		if (strpos(JBSM::version(), 'GIT') !== false)
		{
			$kn_version_type    = JText::_('COM_BIBLESTUDY_VERSION_GIT');
			$kn_version_warning = JText::_('COM_BIBLESTUDY_VERSION_GIT_WARNING');
		}
		else
		{
			if (strpos(JBSM::version(), 'DEV') !== false)
			{
				$kn_version_type    = JText::_('COM_BIBLESTUDY_VERSION_DEV');
				$kn_version_warning = JText::_('COM_BIBLESTUDY_VERSION_DEV_WARNING');
			}
			else
			{
				if (strpos(JBSM::version(), 'RC') !== false)
				{
					$kn_version_type    = JText::_('COM_BIBLESTUDY_VERSION_RC');
					$kn_version_warning = JText::_('COM_BIBLESTUDY_VERSION_RC_WARNING');
				}
				else
				{
					if (strpos(JBSM::version(), 'BETA') !== false)
					{
						$kn_version_type    = JText::_('COM_BIBLESTUDY_VERSION_BETA');
						$kn_version_warning = JText::_('COM_BIBLESTUDY_VERSION_BETA_WARNING');
					}
					else
					{
						if (strpos(JBSM::version(), 'ALPHA') !== false)
						{
							$kn_version_type    = JText::_('COM_BIBLESTUDY_VERSION_ALPHA');
							$kn_version_warning = JText::_('COM_BIBLESTUDY_VERSION_ALPHA_WARNING');
						}
					}
				}
			}
		}

		if (!empty($kn_version_warning) && !empty($kn_version_type))
		{
			return JText::sprintf($msg, JBSM::version(), $kn_version_type) . ' ' . $kn_version_warning;
		}

		return '';
	}

	function checkVersion()
	{
		$version = $this->getDBVersion();

		if (!isset($version->version))
		{
			return false;
		}

		if ($version->state)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get version information from database
	 *
	 * @param    string    Kunena table prefix
	 *
	 * @return    object    Version table
	 * @since    1.6
	 */
	public function getDBVersion($prefix = 'bsms_')
	{
		$db    = JFactory::getDBO();
		$query = "SHOW TABLES LIKE {$db->quote($db->getPrefix().$prefix.'version')}";
		$db->setQuery($query);

		if ($db->loadResult())
		{
			$db->setQuery("SELECT * FROM " . $db->quoteName($db->getPrefix() . $prefix . 'version') . " ORDER BY `id` DESC", 0, 1);
			$version = $db->loadObject();
		}

		if (!isset($version) || !is_object($version) || !isset($version->state))
		{
			$version        = new stdClass();
			$version->state = '';
		}
		elseif (!empty($version->state))
		{
			if ($version->version != JBSM::version())
			{
				$version->state = '';
			}
		}

		return $version;
	}

	/**
	 * Retrieve installed Kunena version as string.
	 *
	 * @return string "Kunena X.Y.Z | YYYY-MM-DD [versionname]"
	 */
	static function getVersionHTML()
	{
		return 'Kunena ' . JBSM::version() . ' | ' . JBSM::versionDate() . ' [ ' . JBSM::versionName() . ' ]';
	}

	/**
	 * Retrieve copyright information as string.
	 *
	 * @return string "© 2008 - 2015 Copyright: BibleStudy Team. All rights reserved. | License: GNU General Public License"
	 */
	static function getCopyrightHTML()
	{
		return ': &copy; 2008 - 2015 ' . JText::_('COM_BIBLESTUDY_VERSION_COPYRIGHT') . ': <a href = "http://www.joomlabiblestudy.org" target = "_blank">'
		. JText::_('COM_BIBLESTUDY_VERSION_TEAM') . '</a>  | ' . JText::_('COM_BIBLESTUDY_VERSION_LICENSE')
		. ': <a href = "http://www.gnu.org/copyleft/gpl.html" target = "_blank">'
		. JText::_('COM_BIBLESTUDY_VERSION_GPL') . '</a>';
	}

	/**
	 * Retrieve installed Kunena version, copyright and license as string.
	 *
	 * @return string "Kunena X.Y.Z | YYYY-MM-DD | © 2008 - 2015 Copyright: BibleStudy Team. All rights reserved. | License: GNU General Public License"
	 */
	static function getLongVersionHTML()
	{
		return self::getVersionHTML() . ' | ' . self::getCopyrightHTML();
	}

}

class BibleStudyVersionException extends Exception
{
}
