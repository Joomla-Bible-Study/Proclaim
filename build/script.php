<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\MVC\Controller\BaseController;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    public function install($parent): void
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
    public function uninstall($parent): void
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
    public function update($parent): void
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
    public function preflight($type, $parent): void
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
     * @throws Exception
     * @since 9.0.2
     */
    public function postflight(string $type, string $parent): void
    {
        // An redirect to a new location after the installation is completed.
        $controller = BaseController::getInstance('Proclaim');
        $controller->setRedirect(
            JUri::base() .
            'index.php?option=com_proclaim&view=cwminstall&task=cwminstall.browse&scanstate=start&' .
            JSession::getFormToken() . '=1'
        );
        $controller->redirect();
    }
}
