<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of JBSM Package component
 *
 * @package     Proclaim
 * @subpackage  Package.JBSPodcast
 * @since       9.0.2
 */
class Pkg_Biblestudy_PackageInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @param   string  $parent  is the class calling this method
	 *
	 * @return void
	 *
	 * @since 9.0.2
	 */
	public function install($parent)
	{
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   string  $parent  is the class calling this method
	 *
	 * @return void
	 *
	 * @since 9.0.2
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * Method to update the component
	 *
	 * @param   string  $parent  is the class calling this method
	 *
	 * @return void
	 *
	 * @since 9.0.2
	 */
	public function update($parent)
	{
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    is the type of change (install, update or discover_install)
	 * @param   string  $parent  is the class calling this method
	 *
	 * @return void
	 *
	 * @since 9.0.2
	 */
	public function preflight($type, $parent)
	{
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @param   string  $type    is the type of change (install, update or discover_install)
	 * @param   string  $parent  is the class calling this method
	 *
	 * @return void
	 *
	 * @since 9.0.2
	 * @throws Exception
	 */
	public function postflight($type, $parent)
	{
		// An redirect to a new location after the install is completed.
		$controller = JControllerLegacy::getInstance('Biblestudy');
		$controller->setRedirect(
			JUri::base() .
			'index.php?option=com_proclaim&view=install&task=install.browse&scanstate=start&' .
			JSession::getFormToken() . '=1');
		$controller->redirect();
	}
}
