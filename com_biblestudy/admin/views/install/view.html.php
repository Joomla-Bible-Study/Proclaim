<?php

/**
 * View html
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * JView class for Install
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class biblestudyViewInstall extends JView {

    /**
     * Display
     * @param string $tpl
     */
    public function display($tpl = null) {

        $this->msg = JRequest::getVar('msg', '', 'post');
        $this->jbsname = JRequest::getVar('jbsname');
        $this->jbstype = JRequest::getVar('jbstype');

        if ($this->jbsname === NULL || $this->jbstype === NULL):
            JError::raiseWarning(500, JText::_('JBS_ERR_WARNING_INSTALL'));
            JRequest::setVar('hidemainmenu', TRUE);
            return FALSE;
        endif;

        // install systems
        $this->installscripts();
        $this->installsetup();

        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add Toolbar to page
     *
     * @since 7.0.0
     */
    protected function addToolbar() {
        JRequest::setVar('hidemainmenu', TRUE);
        JToolBarHelper::help('biblestudy', true);
        JToolBarHelper::title(JText::_('JBS_CMN_INSTALL'), 'administration');
    }

    /**
     * Add the page title to browser.
     *
     * @since	7.1.0
     */
    protected function setDocument() {
        $document = JFactory::getDocument();
        $document->setTitle(JText::sprintf('JBS_TITLE_INSTALL', $this->jbstype, $this->jbsname));
    }

    /**
     * Install Script PostFlight
     * @param boolean $msg
     * @return string
     * @since 7.1.0
     */
    protected function installscripts($msg = null) {
        //We need to check on the topics table. There were changes made between the migration component 1.08 and 1.011 that might differ so it is best to address here
        require_once(BIBLESTUDY_PATH_ADMIN_INSTALL . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update701.php');
        $update = new updatejbs701();
        $update701 = $update->do701update();
        if (!$update701) {
            $update701msg = JText::sprintf('JBS_INS_UPDATE_FAILURE', '7.0.1', '7.0.2');
        } else {
            $msg[] = null;
        }

        //Check for presence of css or backup or other things for upgrade to 7.1.0
        require_once(BIBLESTUDY_PATH_ADMIN_INSTALL . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update710.php');
        $JBS710Update = new JBS710Update();
        $JBS710 = $JBS710Update->update710();
        if (!$JBS710) {
            $msg[] = '<br />' . JText::sprintf('JBS_INS_UPDATE_FAILURE', '7.0.1', '7.1');
        }


        //Check for default details text link image and copy if not present
        $src = BIBLESTUDY_MEDIA_PATH . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'textfile24.png';
        $dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'textfile24.png';
        $imageexists = JFile::exists($dest);
        if (!$imageexists) {
            $msg[] = '<br /><br />' . JText::_('JBS_INS_COPYING_IMAGE');
            $imagesuccess = JFile::copy($src, $dest);
            if ($imagesuccess) {
                $msg[] = '<br />' . JText::_('JBS_INS_COPYING_SUCCESS');
            } else {
                $msg[] = '<br />' . JText::_('JBS_INS_COPYING_PROBLEM_FOLDER1') . '/media/com_biblestudy/images/textfile24.png' . JText::_('JBS_INS_COPYING_PROBLEM_FOLDER2');
            }
        }
        return $msg;
    }

    /**
     * Setup Array for install System
     * @since 7.1.0
     */
    protected function installsetup() {
        $installation_queue = array(
            // modules => { (folder) => { (module) => { (position), (published) } }* }*
            'modules' => array(
                'admin' => array(
                ),
                'site' => array(
                    'biblestudy' => 0,
                    'biblestudy_podcast' => 0,
                )
            ),
            // plugins => { (folder) => { (element) => (published) }* }*
            'plugins' => array(
                'finder' => array(
                    'biblestudy' => 1,
                ),
                'search' => array(
                    'biblestudysearch' => 0,
                ),
                'system' => array(
                    'jbsbackup' => 0,
                    'jbspodcast' => 0,
                )
            )
        );
        // -- General settings

        jimport('joomla.installer.installer');
        $db = JFactory::getDBO();
        $this->status = new JObject();
        $this->status->modules = array();
        $this->status->plugins = array();

        // Modules installation
        if (count($installation_queue['modules'])) {
            foreach ($installation_queue['modules'] as $folder => $modules) {
                if (count($modules))
                    foreach ($modules as $module => $modulePreferences) {
                        // Was the module already installed?
                        $sql = 'SELECT COUNT(*) FROM #__modules WHERE `module`=' . $db->Quote('mod_' . $module);
                        $db->setQuery($sql);
                        $result = $db->loadResult();
                        $this->status->modules[] = array('name' => 'mod_' . $module, 'client' => $folder, 'result' => $result);
                    }
            }
        }
        // Plugins installation
        if (count($installation_queue['plugins'])) {
            foreach ($installation_queue['plugins'] as $folder => $plugins) {
                if (count($plugins))
                    foreach ($plugins as $plugin => $published) {
                        $query = "SELECT COUNT(*) FROM  #__extensions WHERE element=" . $db->Quote($plugin) . " AND folder=" . $db->Quote($folder);
                        $db->setQuery($query);
                        $result = $db->loadResult();
                        $this->status->plugins[] = array('name' => 'plg_' . $plugin, 'group' => $folder, 'result' => $result);
                    }
            }
        }
    }

}