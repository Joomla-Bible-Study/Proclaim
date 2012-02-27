<?php
/**
 * @version $Id: biblestudy.script.php 1 $
 * Bible Study Component
 * @package Bible Study
 *
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//
//No Direct Access
defined('_JEXEC') or die;
?>
<script type="text/javascript">
    window.addEvent('domready', function(){ new Accordion($$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler'), $$('div#content-sliders-1.pane-sliders > .panel > div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_content-sliders-1',$$('div#content-sliders-1.pane-sliders > .panel > h3').indexOf(toggler));},onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');i.removeClass('pane-down');if($$('div#content-sliders-1.pane-sliders > .panel > h3').length==$$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler').length) Cookie.write('jpanesliders_content-sliders-1',-1);},duration: 300,display: 1,show: 1,opacity: false,alwaysHide: true}); });
</script>
<?php

class com_biblestudyInstallerScript {
    /*
     * The release value to be displayed and check against throughout this file.
     */

    private $release = '7.0.3';

    /*
     * Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
     */
    private $minimum_joomla_release = '1.6.0';

    /*
     * $parent is the class calling this method.
     * $type is the type of change (install, update or discover_install, not uninstall).
     * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
     * If preflight returns false, Joomla will abort the update and undo everything already done.
     */

    function preflight($type, $parent) {
        $rel = null;
        // this component does not work with Joomla releases prior to 1.6
        // abort if the current Joomla release is older
        $jversion = new JVersion();

        // Extract the version number from the manifest. This will overwrite the 1.0 value set above
        $this->release = $parent->get("manifest")->version;

        // check to see if we are dealing with version 7.0.0 and create the update table if needed
        $db = JFactory::getDBO();

        //Set the #__schemas version_id to the correct number so the update will occur
        $query = 'SELECT extension_id from #__extensions where name LIKE "%com_biblestudy%"';
        $db->setQuery($query);
        $db->query();
        $extensionid = $db->loadResult();
        if ($extensionid) {
            $query = 'SELECT version_id FROM #__schemas WHERE extension_id = ' . $extensionid;
            $db->setQuery($query);
            $db->query();
            $jbsversion = $db->loadResult();
            if ($jbsversion == '20100101') {
                $query = 'UPDATE #__schemas SET version_id = "7.0.0" WHERE extension_id = ' . $extensionid;
                $db->setQuery($query);
                $db->query();
            }
        }
        // First see if there is an update table
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $updatetable = $prefix . 'bsms_update';
        $updatefound = false;
        $this->is700 = false;
        foreach ($tables as $table) {
            if ($table == $updatetable) {
                $updatefound = true;
            }
        }
        if (!$updatefound) {
            //Do the query here to create the table. This will tell Joomla to update the db from this version on
            $query = 'CREATE TABLE IF NOT EXISTS #__bsms_update (
                              id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                              version VARCHAR(255) DEFAULT NULL,
                              PRIMARY KEY (id)
                            ) DEFAULT CHARSET=utf8';
            $db->setQuery($query);
            $db->query();
            $query = "INSERT INTO #__bsms_update (id,version) VALUES(1,'7.0.0')";
            $db->setQuery($query);
            $db->query();

            $this->is700 = true;
        }


        // Find mimimum required joomla version
        $this->minimum_joomla_release = $parent->get("manifest")->attributes()->version;

        if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt')) {
            Jerror::raiseWarning(null, 'Cannot install com_biblestudy in a Joomla release prior to ' . $this->minimum_joomla_release);
            return false;
        }

        // abort if the component being installed is not newer than the currently installed version
        if ($type == 'update') {
            // @todo need to fix will need to find out whey this is not working on packages upgrades.
//            $oldRelease = $this->getParam('version');
//            $rel = $oldRelease . ' to ' . $this->release;
//            if (version_compare($this->release, $oldRelease, 'le')) {
//                Jerror::raiseWarning(null, 'Incorrect version sequence. Cannot upgrade ' . $rel);
//                return false;
//            }
//        } else {
            $rel = $this->release;
        }
    }

    function install($parent) {
        $db = JFactory::getDBO();
        $query = file_get_contents(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'install-defaults.sql');
        $queries = $db->splitSql($query);
        foreach ($queries as $querie) {
            $db->setQuery($querie);
            $db->query();
        }
        require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
        echo JHtml::_('sliders.panel', JText::_('JBS_INS_INSTALLING_VERSION_TO_') . ' ' . $this->release, 'publishing-details');
    }

    function uninstall($parent) {
        $admin = null;

        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
        require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'params.php');

        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM #__bsms_admin WHERE id = 1");
        $db->query();
        $admin = $db->loadObject();

        $drop_tables = $admin->drop_tables;

        if ($drop_tables > 0) {
            //We must remove the assets manually each time
            $db = JFactory::getDBO();
            $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
            $db->setQuery($query);
            $db->query();
            $parent_id = $db->loadResult();
            $query = "DELETE FROM #__assets WHERE parent_id = " . $parent_id;
            $db->setQuery($query);
            $db->query();
            $query = 'DELETE FROM #__assets WHERE name like "%com_biblestudy%" and parent_id < 1';
            $db->setQuery($query);
            $db->query();
            $query = file_get_contents(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'uninstall-dbtables.sql');
            $db->setQuery($query);
            $db->queryBatch();
            $drop_result = '';
            $drop_result .= '<p>db Error: ' . $db->stderr() . '</p>';
            $drop_result .= '<H3>' . JText::_('JBS_INS_CUSTOM_UNINSTALL_SCRIPT') . '</H3>';
        } else {
            $drop_result = '<H3>' . JText::_('JBS_INS_NO_DATABASE_REMOVED') . '</H3>';
        }
        echo '<h2>' . JText::_('JBS_INS_UNINSTALLED_') . ' ' . $this->release . '</h2> <div>' . $drop_result . '</div>';
    }

//end of function uninstall()

    function update($parent) {

        echo '<p>' . JText::_('JBS_INS_CUSTOM_UPDATE_SCRIPT_TO') . ' ' . $this->release . '</p>';
    }

// End Update

    function postflight($type, $parent) {
        jimport('joomla.filesystem.file');
        //Set the #__schemas version_id to the correct number for error from 7.0.0
        $db = JFactory::getDBO();
        $query = 'SELECT extension_id from #__extensions where name LIKE "%com_biblestudy%"';
        $db->setQuery($query);
        $db->query();
        $extensionid = $db->loadResult();
        if ($extensionid) {
            $query = 'SELECT version_id FROM #__schemas WHERE extension_id = ' . $extensionid;
            $db->setQuery($query);
            $db->query();
            $jbsversion = $db->loadResult();
            if ($jbsversion == '20100101') {
                $query = 'UPDATE #__schemas SET version_id = "' . $this->release . '" WHERE extension_id = ' . $extensionid;
                $db->setQuery($query);
                $db->query();
            }
        }
        $params = null;
        $fixassets = null;
        $imagesuccess = null;

        // set initial values for component parameters
        $params['my_param0'] = 'Component version ' . $this->release;
        $params['my_param1'] = 'Start';
        $params['my_param2'] = '1';
        $this->setParams($params);

        //We need to check on the topics table. There were changes made between the migration component 1.08 and 1.011 that might differ so it is best to address here
        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update701.php');
        $update = new updatejbs701();
        $update701 = $update->do701update();
        if (!$update701) {
            echo JText::sprintf('JBS_INS_UPDATE_FAILURE', '7.0.1', '7.0.2');
        }


        // Sart Sliders
        echo JHtml::_('sliders.start', 'content-sliders-1', array('useCookie' => 1));
        ?>
        <fieldset class="panelform">
            <legend>
            <?php echo JText::sprintf('JBS_INS_INSTALLATION_RESULTS', $type . '_TEXT'); ?></legend>

            <?php
            //Import filesystem libraries. Perhaps not necessary, but does not hurt
            // jimport('joomla.filesystem.file');
            //Remove Old Language Files Administrator
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_biblestudy.sys.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/cs-CZ/cs-CZ.com_biblestudy.sys.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/de-DE/de-DE.com_biblestudy.sys.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/es-ES/es-ES.com_biblestudy.sys.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/hu-HU/hu-HU.com_biblestudy.sys.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/nl-NL/no-NO.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/nl-NL/no-NO.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ADMINISTRATOR . '/language/no-NO/no-NO.com_biblestudy.sys.ini') == TRUE):
                JFile::delete(JPATH_ADMINISTRATOR . '/language/no-NO/no-NO.com_biblestudy.sys.ini');
            endif;

            // Language files for Site
            if (JFile::exists(JPATH_ROOT . '/language/en-GB/en-GB.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/en-GB/en-GB.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/cs-CZ/cs-CZ.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/cs-CZ/cs-CZ.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/de-DE/de-DE.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/de-DE/de-DE.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/es-ES/es-ES.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/es-ES/es-ES.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/hu-HU/hu-HU.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/hu-HU/hu-HU.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/nl-NL/nl-NL.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/nl-NL/nl-NL.com_biblestudy.ini');
            endif;
            if (JFile::exists(JPATH_ROOT . '/language/no-NO/no-NO.com_biblestudy.ini') == TRUE):
                JFile::delete(JPATH_ROOT . '/language/no-NO/no-NO.com_biblestudy.ini');
            endif;
            //create an index.html file in the media folders if not there already
            $index = '<html><body bgcolor="#FFFFFF"></body></html>';
            JFile::write('media/com_biblestudy/index.html',$index);
            JFile::write('media/com_biblestudy/backup/index.html',$index);
            JFile::write('media/com_biblestudy/database/index.html',$index);

            //Check for presence of css or backup or other things for upgrade to 7.1.0
            require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'update710.php');
            $JBS710 = JBS710Update::update710();
            if (!$JBS710){echo '<br />'. JText::_('JBS_CSS_FAILURE');}
            else {echo '<br />' . JText::_('JBS_CSS_SUCCESS');}
            
            //Check for default details text link image and copy if not present
            $src = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'textfile24.png';
            $dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'textfile24.png';
            $imageexists = JFile::exists($dest);
            if (!$imageexists) {
                echo JHtml::_('sliders.panel', 'Image', 'image-details');
                echo '<br /><br />' . JText::_('JBS_INS_COPYING_IMAGE');
                //@todo need to move the copy funtions out of the if call
                if ($imagesuccess = JFile::copy($src, $dest)) {
                    echo '<br />' . JText::_('JBS_INS_COPYING_SUCCESS');
                } else {
                    echo '<br />' . JText::_('JBS_INS_COPYING_PROBLEM_FOLDER1') . '/components/com_biblestudy/images/textfile24.png' . JText::_('JBS_INS_COPYING_PROBLEM_FOLDER2');
                }
            }

            echo JHtml::_('sliders.end');
            ?>
        </fieldset>
        </div>
        <!--end of div for panelform -->

        <!-- Rest of footer -->
        <p>
        <div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
            <img src="components/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" class="float: left" />
            <strong><?php echo JText::_('JBS_INS_THANK_YOU'); ?></strong>
        </p>

        <p>
            <?php echo JText::_('JBS_INS_STATEMENT1'); ?> </p>
        <p>
            <?php echo JText::_('JBS_INS_STATEMENT2'); ?></p>
        <p>
            <?php echo JText::_('JBS_INS_STATEMENT3'); ?></p>

        <p><a href="http://www.joomlabiblestudy.org/forum.html" target="_blank"><?php echo JText::_('JBS_INS_VISIT_FORUM'); ?></a></p>
        <p><a href="http://www.joomlabiblestudy.org" target="_blank"><?php echo JText::_('JBS_INS_GET_MORE_HELP'); ?></a></p>
        <p><a href="http://www.joomlabiblestudy.org/jbs-documentation/user-guide-7-0.html" target="_blank"><?php echo JText::_('JBS_INS_VISIT_DOCUMENTATION'); ?></a></p>
        <p><?php echo JText::_('JBS_INS_TITLE'); ?> &copy; by <a
                href="http://www.JoomlaBibleStudy.org" target="_blank">www.JoomlaBibleStudy.org</a>.
            All rights reserved.</p>
        </div>
        <?php
        // An example of setting a redirect to a new location after the install is completed
        //$parent-&gt;getParent()-&gt;set('redirect_url', 'http://www.google.com');
    }

    /*
     * get a variable from the manifest file (actually, from the manifest cache).
     */

    function getParam($name) {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_biblestudy"');
        $manifest = json_decode($db->loadResult(), true);
        return $manifest[$name];
    }

    /*
     * sets parameter values in the component's row of the extension table
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
            $db->query();
        }
    }

}

// end of class
