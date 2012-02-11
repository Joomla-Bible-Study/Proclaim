<?php

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of JBSBACKUP component
 */
class plgSystemjbsbackupInstallerScript {

    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) {

    }

    /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) {
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_JBSBACKUP_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * method to update the component
     *
     * @return void
     */
    function update($parent) {
        // $parent is the class calling this method
        echo '<p>' . JText::_('PLG_JBSBACKUP_UPDATE_TEXT') . '</p>';
    }

    /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
    function preflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('PLG_JBSBACKUP_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) {
        // $parent is the class calling this method
        // $type is the type of change (install, update or discover_install)
        //echo '<p>' . JText::_('PLG_JBSBACKUP_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }

}
