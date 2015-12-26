<?php
/**
 * BibleStudy Component
 *
 * @package       BibleStudy.Installer
 *
 * @copyright (C) 2008 - 2014 BibleStudy Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.joomlabiblestudy.org
 **/
defined('_JEXEC') or die ();

class KunenaMigratorJoomlaboard
{
	protected $versions = array(
		array('version' => '1.0', 'date' => '0000-00-00', 'table' => 'sb_messages', 'column' => 'id'),
	);

	public static function getInstance()
	{
		static $instance = null;
		if (!$instance)
		{
			$instance = new KunenaMigratorJoomlaboard();
		}

		return $instance;
	}

	/**
	 * Detect JoomlaBoard version.
	 *
	 * @return  string  JoomlaBoard version or null.
	 */
	public function detect()
	{
		foreach ($this->versions as $version)
		{
			if (KunenaInstaller::getTableColumn($version['table'], $version['column']))
			{
				return $version->version;
			}
		}

		return null;
	}
}
