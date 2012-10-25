<?php

/**
 * Bible Study Component
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @todo Need to redisign to show progress stages.
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * BibleStudy Install Script
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class Com_BiblestudyInstallerScript {

    /**
     * The release value to be displayed and check against throughout this file.
     * @var string
     */
    private $release = '7.1.2';

    /**
     * Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
     * @var string
     */
    private $minimum_joomla_release = '2.5.0';

    /**
     * The component's name
     *  @var string
     * */
    protected $_biblestudy_extension = 'com_biblestudy';

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

        // Start DB factory
        $db = JFactory::getDBO();

        //Set the #__schemas version_id to the correct number so the update will occur if out of seqence.
        $query = 'SELECT extension_id from #__extensions where name LIKE "%com_biblestudy%"';
        $db->setQuery($query);
        $extensionid = $db->loadResult();
        if ($extensionid) {
            $query = 'SELECT version_id FROM #__schemas WHERE extension_id = ' . $extensionid;
            $db->setQuery($query);
            $jbsversion = $db->loadResult();
            if ($jbsversion == '20100101') {
                $query = 'UPDATE #__schemas SET version_id = "7.0.0" WHERE extension_id = ' . $extensionid;
                $db->setQuery($query);
                $db->execute();
            }
        }

        // Find mimimum required joomla version
        $this->minimum_joomla_release = $parent->get("manifest")->attributes()->version;

        if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt')) {
            Jerror::raiseWarning(null, 'Cannot install com_biblestudy in a Joomla release prior to ' . $this->minimum_joomla_release);
            return false;
        }
        //copy the css file over to another location
        $src = JPATH_SITE . '/components/com_biblestudy/assets/css/biblestudy.css';
        if (JFile::exists($src)) {
            JFile::copy($src, JPATH_SITE . '/tmp/biblestudy.css');
        }
    }

    /**
     * Install
     * @param string $parent
     */
    function install($parent) {
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__bsms_admin";
        $db->setQuery($querie);
        if (!$db->loadResult()) {
            $query = file_get_contents(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'install-defaults.sql');
            $queries = $db->splitSql($query);
            foreach ($queries as $querie) {
                $querie = trim($querie);
                $db->setQuery($querie);
                $db->execute();
            }
            require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'biblestudy.install.special.php');
            $fresh = new JBSFreshInstall();
            if (!$fresh->installCSS()) {
                JError::raiseWarning(1, JText::_('JBS_INS_FAILURE'));
            }
        }
    }

    /**
     * Uninstall
     * @param string $parent
     */
    function uninstall($parent) {
        $admin = null;

        require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');

        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM #__bsms_admin WHERE id = 1");
        $admin = $db->loadObject();

        $drop_tables = $admin->drop_tables;

        if ($drop_tables > 0) {
            //We must remove the assets manually each time
            $db = JFactory::getDBO();
            $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
            $db->setQuery($query);
            $parent_id = $db->loadResult();
            $query = "DELETE FROM #__assets WHERE parent_id = " . $parent_id;
            $db->setQuery($query);
            $db->execute();
            $query = 'DELETE FROM #__assets WHERE name like "%com_biblestudy%" and parent_id < 1';
            $db->setQuery($query);
            $db->execute();
            $query = file_get_contents(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'uninstall-dbtables.sql');
            $queries = $db->splitSql($query);
            foreach ($queries as $querie) {
                $querie = trim($querie);
                $db->setQuery($querie);
                $db->execute();
            }
            $drop_result = '';
            $drop_result .= '<p>db Error: ' . $db->stderr() . '</p>';
        } else {
            $drop_result = '<H3>' . JText::_('JBS_INS_NO_DATABASE_REMOVED') . '</H3>';
        }
        echo '<h2>' . JText::_('JBS_INS_UNINSTALLED') . ' ' . $this->release . '</h2> <div>' . $drop_result . '</div>';
    }

    /**
     * Update
     * @param string $parent
     */
    function update($parent) {
        $this->fixMenus();
        $this->fixImagePaths();
        $this->fixemptyaccess();
        $this->fixemptylanguage();
    }

    /**
     * Post Flight
     * @param string $type
     * @param string $parent
     */
    function postflight($type, $parent) {
        //Set the #__schemas version_id to the correct number for error from 7.0.0
        $db = JFactory::getDBO();
        $query = 'SELECT extension_id from #__extensions where name LIKE "%com_biblestudy%"';
        $db->setQuery($query);
        $extensionid = $db->loadResult();
        if ($extensionid) {
            $query = 'SELECT version_id FROM #__schemas WHERE extension_id = ' . $extensionid;
            $db->setQuery($query);
            $jbsversion = $db->loadResult();
            if ($jbsversion == '20100101') {
                $query = 'UPDATE #__schemas SET version_id = "' . $this->release . '" WHERE extension_id = ' . $extensionid;
                $db->setQuery($query);
                if (!$db->execute()) {
                    JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
                }
            }
        }

        // Set initial values for component parameters
        $params['my_param0'] = 'Component version ' . $this->release;
        $params['my_param1'] = 'Start';
        $params['my_param2'] = '1';
        $this->setParams($params);

        // Set installstate
        $query1 = "UPDATE `#__bsms_admin` SET installstate = '{\"release\":\"" . $this->release . "\",\"jbsparent\":\"" . $parent . "\",\"jbstype\":\"" . $type . "\",\"jbsname\":\"com_biblestudy\"}' WHERE id = 1";
        $db->setQuery($query1);
        if (!$db->execute()) {
            JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
        }

        // An redirect to a new location after the install is completed.
        $parent->getParent()->set('redirect_url', JURI::base() . 'index.php?option=com_biblestudy');
    }

    /**
     * get a variable from the manifest file (actually, from the manifest cache).
     *
     * @param string $name
     * @return string
     */
    function getParam($name) {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_biblestudy"');
        $manifest = json_decode($db->loadResult(), true);
        return $manifest[$name];
    }

    /**
     * sets parameter values in the component's row of the extension table
     * @param array $param_array
     */
    function setParams($param_array) {
        if (count($param_array) > 0) {
            // read the existing component value(s)
            $db = JFactory::getDbo();
            $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_biblestudy"');
            $params = json_decode($db->loadResult(), true);
            // add the new variable(s) to the existing one(s)
            foreach ($param_array as $name => $value) {
                $params[(string) $name] = (string) $value;
            }
            // store the combined new and existing values back as a JSON string
            $paramsString = json_encode($params);
            $db->setQuery('UPDATE #__extensions SET params = ' .
                    $db->quote($paramsString) .
                    ' WHERE name = "com_biblestudy"');
            if (!$db->execute()) {
                JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
            }
        }
    }

    /**
     * Remove Old Files and Folders
     * @since 7.1.0
     */
    public function deleteUnexistingFiles() {
        $files = array(
            '/media/com_biblestudy/css/biblestudy.css.dist',
            '/images/textfile24.png',
            '/components/com_biblestudy/biblestudy.css',
            '/components/com_biblestudy/class.biblestudydownload.php',
            '/components/language/en-GB/en-GB.com_biblestudy.ini',
            '/administrator/language/en-GB/en-GB.com_biblestudy.ini',
            '/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
            '/administrator/components/com_biblestudy/Snoopy.class.php',
            '/administrator/components/com_biblestudy/admin.biblestudy.php',
            '/components/com_biblestudy/helpers/updatesef.php',
            '/components/com_biblestudy/helpers/image.php',
            '/components/com_biblestudy/helpers/helper.php',
            '/components/com_biblestudy/views/messages/tmpl/modal16.php',
            '/components/com_biblestudy/controllers/teacherlist.php',
            '/components/com_biblestudy/controllers/teacherdisplay.php',
            '/components/com_biblestudy/controllers/studydetails.php',
            '/components/com_biblestudy/controllers/studieslist.php',
            '/components/com_biblestudy/controllers/serieslist.php',
            '/components/com_biblestudy/controllers/seriesdetail.php',
            '/components/com_biblestudy/models/teacherlist.php',
            '/components/com_biblestudy/models/teacherdisplay.php',
            '/components/com_biblestudy/models/studydetails.php',
            '/components/com_biblestudy/models/studieslist.php',
            '/components/com_biblestudy/models/seriesdetail.php',
            '/components/com_biblestudy/models/serieslist.php',
            '/components/com_biblestudy/views/mediafile/tmpl/form.php',
            '/components/com_biblestudy/views/mediafile/tmpl/form.xml',
            '/language/en-GB/en-GB.com_biblestudy.ini',
            '/language/cs-CZ/cs-CZ.com_biblestudy.ini',
            '/language/de-DE/de-DE.com_biblestudy.ini',
            '/language/es-ES/es-ES.com_biblestudy.ini',
            '/language/hu-HU/hu-HU.com_biblestudy.ini',
            '/language/nl-NL/nl-NL.com_biblestudy.ini',
            '/language/no-NO/no-NO.com_biblestudy.ini',
            '/language/en-GB/en-GB.mod_biblestudy.ini',
            '/language/en-GB/en-GB.mod_biblestudy.sys.ini',
            '/administrator/components/com_biblestudy/install/biblestudy.assets.php',
            '/administrator/components/com_biblestudy/install/sql/jbs7.0.0.sql',
            '/administrator/components/com_biblestudy/install/sql/updates/mysql/20100101.sql',
            '/administrator/components/com_biblestudy/lib/biblestudy.podcast.class.php',
            '/administrator/components/com_biblestudy/controllers/commentsedit.php',
            '/administrator/components/com_biblestudy/controllers/commentslist.php',
            '/administrator/components/com_biblestudy/controllers/cssedit.php',
            '/administrator/components/com_biblestudy/controllers/folderslist.php',
            '/administrator/components/com_biblestudy/controllers/foldersedit.php',
            '/administrator/components/com_biblestudy/controllers/locationslist.php',
            '/administrator/components/com_biblestudy/controllers/locationsedit.php',
            '/administrator/components/com_biblestudy/controllers/mediaedit.php',
            '/administrator/components/com_biblestudy/controllers/mediafilesedit.php',
            '/administrator/components/com_biblestudy/controllers/mediafileslist.php',
            '/administrator/components/com_biblestudy/controllers/medialist.php',
            '/administrator/components/com_biblestudy/controllers/messagetypelist.php',
            '/administrator/components/com_biblestudy/controllers/messagetypeedit.php',
            '/administrator/components/com_biblestudy/controllers/mimetypelist.php',
            '/administrator/components/com_biblestudy/controllers/mimetypeedit.php',
            '/administrator/components/com_biblestudy/controllers/podcastlist.php',
            '/administrator/components/com_biblestudy/controllers/podcastedit.php',
            '/administrator/components/com_biblestudy/controllers/serieslist.php',
            '/administrator/components/com_biblestudy/controllers/seriesedit.php',
            '/administrator/components/com_biblestudy/controllers/serverslist.php',
            '/administrator/components/com_biblestudy/controllers/serversedit.php',
            '/administrator/components/com_biblestudy/controllers/sharelist.php',
            '/administrator/components/com_biblestudy/controllers/shareedit.php',
            '/administrator/components/com_biblestudy/controllers/studieslist.php',
            '/administrator/components/com_biblestudy/controllers/studiesedit.php',
            '/administrator/components/com_biblestudy/controllers/teacherlist.php',
            '/administrator/components/com_biblestudy/controllers/teacheredit.php',
            '/administrator/components/com_biblestudy/controllers/templateedit.php',
            '/administrator/components/com_biblestudy/controllers/templateslist.php',
            '/administrator/components/com_biblestudy/controllers/topicslist.php',
            '/administrator/components/com_biblestudy/controllers/topicsedit.php',
            '/administrator/components/com_biblestudy/models/forms/commentsedit.xml',
            '/administrator/components/com_biblestudy/models/forms/foldersedit.xml',
            '/administrator/components/com_biblestudy/models/forms/locationsedit.xml',
            '/administrator/components/com_biblestudy/models/forms/mediaedit.xml',
            '/administrator/components/com_biblestudy/models/forms/mediafilesedit.xml',
            '/administrator/components/com_biblestudy/models/forms/messagetypeedit.xml',
            '/administrator/components/com_biblestudy/models/forms/mimetypeedit.xml',
            '/administrator/components/com_biblestudy/models/forms/podcastedit.xml',
            '/administrator/components/com_biblestudy/models/forms/seriesedit.xml',
            '/administrator/components/com_biblestudy/models/forms/serversedit.xml',
            '/administrator/components/com_biblestudy/models/forms/shareedit.xml',
            '/administrator/components/com_biblestudy/models/forms/studiesedit.xml',
            '/administrator/components/com_biblestudy/models/forms/teacheredit.xml',
            '/administrator/components/com_biblestudy/models/forms/templateedit.xml',
            '/administrator/components/com_biblestudy/models/forms/topicsedit.xml',
            '/administrator/components/com_biblestudy/models/episodelist.php',
            '/administrator/components/com_biblestudy/models/commentsedit.php',
            '/administrator/components/com_biblestudy/models/commentslist.php',
            '/administrator/components/com_biblestudy/models/cssedit.php',
            '/administrator/components/com_biblestudy/models/folderslist.php',
            '/administrator/components/com_biblestudy/models/foldersedit.php',
            '/administrator/components/com_biblestudy/models/locationslist.php',
            '/administrator/components/com_biblestudy/models/locationsedit.php',
            '/administrator/components/com_biblestudy/models/mediaedit.php',
            '/administrator/components/com_biblestudy/models/mediafilesedit.php',
            '/administrator/components/com_biblestudy/models/mediafileslist.php',
            '/administrator/components/com_biblestudy/models/medialist.php',
            '/administrator/components/com_biblestudy/models/messagetypelist.php',
            '/administrator/components/com_biblestudy/models/messagetypeedit.php',
            '/administrator/components/com_biblestudy/models/mimetypelist.php',
            '/administrator/components/com_biblestudy/models/mimetypeedit.php',
            '/administrator/components/com_biblestudy/models/podcastlist.php',
            '/administrator/components/com_biblestudy/models/podcastedit.php',
            '/administrator/components/com_biblestudy/models/serieslist.php',
            '/administrator/components/com_biblestudy/models/seriesedit.php',
            '/administrator/components/com_biblestudy/models/serverslist.php',
            '/administrator/components/com_biblestudy/models/serversedit.php',
            '/administrator/components/com_biblestudy/models/sharelist.php',
            '/administrator/components/com_biblestudy/models/shareedit.php',
            '/administrator/components/com_biblestudy/models/studieslist.php',
            '/administrator/components/com_biblestudy/models/studiesedit.php',
            '/administrator/components/com_biblestudy/models/teacherlist.php',
            '/administrator/components/com_biblestudy/models/teacheredit.php',
            '/administrator/components/com_biblestudy/models/templateedit.php',
            '/administrator/components/com_biblestudy/models/templateslist.php',
            '/administrator/components/com_biblestudy/models/topicslist.php',
            '/administrator/components/com_biblestudy/models/topicsedit.php',
            '/administrator/components/com_biblestudy/tables/biblestudy.php',
            '/administrator/components/com_biblestudy/tables/booksedit.php',
            '/administrator/components/com_biblestudy/tables/commentsedit.php',
            '/administrator/components/com_biblestudy/tables/foldersedit.php',
            '/administrator/components/com_biblestudy/tables/locationsedit.php',
            '/administrator/components/com_biblestudy/tables/mediaedit.php',
            '/administrator/components/com_biblestudy/tables/mediafilesedit.php',
            '/administrator/components/com_biblestudy/tables/messagetypeedit.php',
            '/administrator/components/com_biblestudy/tables/mimetypeedit.php',
            '/administrator/components/com_biblestudy/tables/podcastedit.php',
            '/administrator/components/com_biblestudy/tables/seriesedit.php',
            '/administrator/components/com_biblestudy/tables/serversedit.php',
            '/administrator/components/com_biblestudy/tables/shareedit.php',
            '/administrator/components/com_biblestudy/tables/studiesedit.php',
            '/administrator/components/com_biblestudy/tables/teacheredit.php',
            '/administrator/components/com_biblestudy/tables/topicsedit.php',
            '/administrator/components/com_biblestudy/tables/templateedit.php',
            '/administrator/components/com_biblestudy/tables/topicsedit.php',
            '/administrator/components/com_biblestudy/helpers/version.php',
            '/administrator/language/en-GB/en-GB.com_biblestudy.ini',
            '/administrator/language/en-GB/en-GB.com_biblestudy.sys.ini',
            '/administrator/language/cs-CZ/cs-CZ.com_biblestudy.ini',
            '/administrator/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini',
            '/administrator/language/de-DE/de-DE.com_biblestudy.ini',
            '/administrator/language/de-DE/de-DE.com_biblestudy.sys.ini',
            '/administrator/language/es-ES/es-ES.com_biblestudy.ini',
            '/administrator/language/es-ES/es-ES.com_biblestudy.sys.ini',
            '/administrator/language/hu-HU/hu-HU.com_biblestudy.ini',
            '/administrator/language/hu-HU/hu-HU.com_biblestudy.sys.ini',
            '/administrator/language/nl-NL/nl-NL.com_biblestudy.ini',
            '/administrator/language/nl-NL/no-NO.com_biblestudy.ini',
            '/administrator/language/no-NO/no-NO.com_biblestudy.sys.ini',
        );

        $folders = array(
            '/components/com_biblestudy/assets',
            '/components/com_biblestudy/images',
            '/components/com_biblestudy/views/teacherlist',
            '/components/com_biblestudy/views/teacherdisplay',
            '/components/com_biblestudy/views/studieslist',
            '/components/com_biblestudy/views/studydetails',
            '/components/com_biblestudy/views/serieslist',
            '/components/com_biblestudy/views/seriesdetail',
            '/administrator/media',
            '/administrator/components/com_biblestudy/assets',
            '/administrator/components/com_biblestudy/images',
            '/administrator/components/com_biblestudy/css',
            '/administrator/components/com_biblestudy/js',
            '/administrator/components/com_biblestudy/views/commentsedit',
            '/administrator/components/com_biblestudy/views/commentslist',
            '/administrator/components/com_biblestudy/views/cssedit',
            '/administrator/components/com_biblestudy/views/folderslist',
            '/administrator/components/com_biblestudy/views/foldersedit',
            '/administrator/components/com_biblestudy/views/locationslist',
            '/administrator/components/com_biblestudy/views/locationsedit',
            '/administrator/components/com_biblestudy/views/mediaedit',
            '/administrator/components/com_biblestudy/views/mediafilesedit',
            '/administrator/components/com_biblestudy/views/mediafileslist',
            '/administrator/components/com_biblestudy/views/medialist',
            '/administrator/components/com_biblestudy/views/messagetypelist',
            '/administrator/components/com_biblestudy/views/messagetypeedit',
            '/administrator/components/com_biblestudy/views/mimetypelist',
            '/administrator/components/com_biblestudy/views/mimetypeedit',
            '/administrator/components/com_biblestudy/views/podcastlist',
            '/administrator/components/com_biblestudy/views/podcastedit',
            '/administrator/components/com_biblestudy/views/serieslist',
            '/administrator/components/com_biblestudy/views/seriesedit',
            '/administrator/components/com_biblestudy/views/serverslist',
            '/administrator/components/com_biblestudy/views/serversedit',
            '/administrator/components/com_biblestudy/views/sharelist',
            '/administrator/components/com_biblestudy/views/shareedit',
            '/administrator/components/com_biblestudy/views/studieslist',
            '/administrator/components/com_biblestudy/views/studiesedit',
            '/administrator/components/com_biblestudy/views/teacherlist',
            '/administrator/components/com_biblestudy/views/teacheredit',
            '/administrator/components/com_biblestudy/views/templateedit',
            '/administrator/components/com_biblestudy/views/templateslist',
            '/administrator/components/com_biblestudy/views/topicslist',
            '/administrator/components/com_biblestudy/views/topicsedit',
        );

        foreach ($files as $file) {
            if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file)) {
                echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br />';
            }
        }

        foreach ($folders as $folder) {
            if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder)) {
                echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br />';
            }
        }
    }

    /**
     * Fix Menus
     * @since 7.1.0
     */
    public function fixMenus() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
                ->from('#__menu')
                ->where("`menutype` != 'main'")
                ->where("`link` LIKE '%com_biblestudy%'");
        $db->setQuery($query);
        $menus = $db->loadObjectList();
        if (count($menus) !== 0) :
            foreach ($menus AS $menu):
                $menu->link = str_replace('teacherlist', 'teachers', $menu->link);
                $menu->link = str_replace('teacherdisplay', 'teacher', $menu->link);
                $menu->link = str_replace('studydetails', 'sermon', $menu->link);
                $menu->link = str_replace('serieslist', 'seriesdisplays', $menu->link);
                $menu->link = str_replace('seriesdetail', 'seriesdisplay', $menu->link);
                $menu->link = str_replace('studieslist', 'sermons', $menu->link);
                $query = $db->getQuery(TRUE);
                $query->update('#__menu')
                        ->set("`link` = '" . $db->escape($menu->link) . "'")
                        ->where('id = ' . $menu->id);
                $db->setQuery($query);
                if (!$db->execute()) {
                    JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
                }
            endforeach;
        endif;
    }

    /**
     * Fix Image paths
     * @since 7.1.0
     */
    public function fixImagePaths() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
                ->from('#__bsms_media');
        $db->setQuery($query);
        $images = $db->loadObjectList();
        foreach ($images as $image) {
            if (!empty($image->media_image_path)) {
                $image->media_image_path = str_replace('components', 'media', $image->media_image_path);
                $query = $db->getQuery(TRUE);
                $query->update('#__bsms_media')
                        ->set("`media_image_path` = '" . $db->escape($image->media_image_path) . "'")
                        ->where('id = ' . $image->id);
                $db->setQuery($query);
                if (!$db->execute()) {
                    JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
                }
            }
        }
        $query = $db->getQuery(true);
        $query->select('*')
                ->from('#__bsms_share');
        $db->setQuery($query);
        $datas = $db->loadObjectList();
        foreach ($datas as $data) {
            //Need to adjust the params and write back
            $registry = new JRegistry();
            $registry->loadJSON($data->params);
            $params = $registry;
            $shareimage = $params->get('shareimage');
            $shareimage = str_replace('components', 'media', $shareimage);
            $params->set('shareimage', $shareimage);
            //Now write the params back into the $table array and store.
            $data->params = (string) $params->toString();
            $qeery = $db->getQuery(true);
            $qeery->update('#__bsms_share')
                    ->set('`params` =' . $data->params)
                    ->where('id = ' . $data->id);
            $db->setQuery($query);
            if (!$db->execute()) {
                JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
            }
        }
    }

    /**
     * Funciton to find empty language field and set them to "*"
     * @since 7.1.0
     * @todo need to compleat
     */
    public function fixemptylanguage() {
        //tables to fix
        $tables = array(
            array('table' => '#__bsms_comments'),
            array('table' => '#__bsms_mediafiles'),
            array('table' => '#__bsms_series'),
            array('table' => '#__bsms_studies'),
            array('table' => '#__bsms_teachers'),
        );
        //correct blank records
        foreach ($tables as $table):
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->update($table['table'])
                    ->set("`language` = '*'")
                    ->where("`language` = ''");
            $db->setQuery($query);
            if (!$db->execute()) {
                JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
            }
        endforeach;
    }

    /**
     * Function to Find empty access in the db and set them to Public
     * @since 7.1.0
     * @todo need to compleat
     */
    public function fixemptyaccess() {
        //tables to fix
        $tables = array(
            array('table' => '#__bsms_admin'),
            array('table' => '#__bsms_mediafiles'),
            array('table' => '#__bsms_message_type'),
            array('table' => '#__bsms_mimetype'),
            array('table' => '#__bsms_order'),
            array('table' => '#__bsms_podcast'),
            array('table' => '#__bsms_series'),
            array('table' => '#__bsms_servers'),
            array('table' => '#__bsms_share'),
            array('table' => '#__bsms_studies'),
            array('table' => '#__bsms_studytopics'),
            array('table' => '#__bsms_teachers'),
            array('table' => '#__bsms_templates'),
            array('table' => '#__bsms_topics'),
        );
        //Get Public id
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id')
                ->from('#__viewlevels')
                ->where("`title` = 'Public'");
        $db->setQuery($query);
        $id = $db->loadResult();
        //correct blank or not set records
        foreach ($tables as $table):
            $query = $db->getQuery(true);
            $query->update($table['table'])
                    ->set('`access` = ' . $id)
                    ->where("(`access` = '0' or `access` = '')");
            $db->setQuery($query);
            if (!$db->execute()) {
                JError::raiseWarning(1, JText::sprintf('JBS_INS_SQL_ERRORS', $db->stderr(true)));
            }
        endforeach;
    }

}
