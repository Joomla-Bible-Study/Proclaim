<?php

/**
 * Install Script for JBSMigration
 * @package BibleStudy
 * @subpackage JBSMigration
 * @copyright           (C) 2007 - 2011 Joomla Bible Study Team All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

//the name of the class must be the name of your component + InstallerScript
//for example: com_contentInstallerScript for com_content.
/**
 * JBSMigration class
 * @package BibleStudy
 * @subpackage Com_JBSMigration
 * @since 7.0.2
 */
class com_jbsmigrationInstallerScript {

    /**
     * The release value to be displayed and check against throughout this file.
     * @var string
     */
    private $release = '1.3.1';

    /**
     * Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
     * @var string
     */
    private $minimum_joomla_release = '1.6.0';

    /**
     * $parent is the class calling this method.
     * $type is the type of change (install, update or discover_install, not uninstall).
     * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
     * If preflight returns false, Joomla will abort the update and undo everything already done.
     * @param string $type
     * @param string $parent
     * @return boolean
     */
    function preflight($type, $parent) {
        // this component does not work with Joomla releases prior to 1.6
        // abort if the current Joomla release is older
        $jversion = new JVersion();

        // Extract the version number from the manifest. This will overwrite the 1.0 value set above
        $this->release = $parent->get("manifest")->version;

        // Find mimimum required joomla version
        $this->minimum_joomla_release = $parent->get("manifest")->attributes()->version;


        Jerror::raiseWarning(null, 'Cannot install com_jbsmigration in a Joomla release 1.6+');
        return false;
    }

    /**
     * $parent is the class calling this method.
     * install runs after the database scripts are executed.
     * If the extension is new, the install method is run.
     * If install returns false, Joomla will abort the install and undo everything already done.
     * @param string $parent
     * @return boolean
     */
    function install($parent) {

    }

    /**
     * $parent is the class calling this method.
     * update runs after the database scripts are executed.
     * If the extension exists, then the update method is run.
     * If this returns false, Joomla will abort the update and undo everything already done.
     * @param string $parent
     * @return boolean
     */
    function update($parent) {

    }

    /**
     * $parent is the class calling this method.
     * $type is the type of change (install, update or discover_install, not uninstall).
     * postflight is run after the extension is registered in the database.
     * @param string $type
     * @param string $parent
     * @return boolean
     */
    function postflight($type, $parent) {

    }

    /**
     * $parent is the class calling this method
     * uninstall runs before any other action is taken (file removal or database processing).
     * @param string $type
     * @param string $parent
     * @return boolean
     */
    function uninstall($type, $parent) {

    }

}