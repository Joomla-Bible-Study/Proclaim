<?php
/**
 * @version $Id: biblestudy.install.php 1 $
 * Bible Study Component
 * @package Bible Study
 *
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 *
 * Install Based on Kunena Component
 * Reference http://svn.joomla.org/project/cms/development/trunk/tests/_data/installer_packages/
 **/
 //
 // Dont allow direct linking
 defined( '_JEXEC' ) or die('Restricted access');
 ?>
      <script type="text/javascript">
window.addEvent('domready', function(){ new Accordion($$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler'), $$('div#content-sliders-1.pane-sliders > .panel > div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_content-sliders-1',$$('div#content-sliders-1.pane-sliders > .panel > h3').indexOf(toggler));},onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');i.removeClass('pane-down');if($$('div#content-sliders-1.pane-sliders > .panel > h3').length==$$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler').length) Cookie.write('jpanesliders_content-sliders-1',-1);},duration: 300,display: 1,show: 1,opacity: false,alwaysHide: true}); });
 </script>
 <?php
 
// Bible Study wide defines

class com_biblestudyInstallerScript {

	function install($parent) {
		echo '<p>'. JText::_('COM_BIBLESTUDY_16_CUSTOM_INSTALL_SCRIPT') . '</p>';
		$db =& JFactory::getDBO();
			$query = file_get_contents(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'sql' .DS. 'jbs7.0.0.sql');
			$db->setQuery($query);
			$db->queryBatch();
	}

	function uninstall($parent) {
		//echo '<p>'. JText::_('COM_BIBLESTUDY_16_CUSTOM_UNINSTALL_SCRIPT') .'</p>';
		require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
		require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'params.php');

		$db =& JFactory::getDBO();
				$db->setQuery ("SELECT * FROM #__bsms_admin WHERE id = 1");
				$db->query();
				$admin = $db->loadObject();
       

				$drop_tables = $admin->drop_tables;

	if ($drop_tables > 0)
	{
		$drop_result = '<table><tr><td><H3>Uninstall Results: Tables removed unless noted below</H3></td></tr>';
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_studies");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_teachers");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_topics");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_servers");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_series");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_message_type");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_folders");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_order");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_search");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_schemaversion");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_media");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_books");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_podcast");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_mimetype");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_mediafiles");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_templates");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_comments");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_admin");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_studytopics");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_version");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_share");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_locations");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
				}
    $db->setQuery ("DROP TABLE IF EXISTS #__bsms_timeset");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<tr><td>db Error: '.$db->stderr().' </td></tr> ';
					
					}
	$mainframe =& JFactory::getApplication(); ?>

	<h2>Joomla Bible Study Uninstalled</h2>
	<?php
		
		$drop_result .= '</table>';
		echo '</tr><tr><td>'.$drop_result.'</td></tr>'; //dump ($drop_result, 'drop_result: ');
	}
	else
	{
		print '<p>Database tables have not been removed <br /> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p>';
	}

 
	} //end of function uninstall()

	function update($parent) {
		echo '<p>'. JText::_('COM_BIBLESTUDY_16_CUSTOM_UPDATE_SCRIPT') .'</p>';

    ?>
 <?php
    

        $application = JFactory::getApplication();
        $db = JFactory::getDBO();
        
        //First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
        //check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version
        //
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $versiontype = '';
        $currentversion = false;
        $oldversion = false;
        $jbsexists = false;
         foreach ($tables as $table)
        {
            $studies = $prefix.'bsms_version';
            $currentversionexists = substr_count($table,$studies);
            if ($currentversionexists > 0){$currentversion = true; $versiontype = 1;}
        }    
        //Only move forward if a current version type is not found
        if (!$currentversion)      
        {
            //Now let's check to see if there is an older database type (prior to 6.2)
            $oldversion = false;
            foreach ($tables as $table)
            {
                $studies = $prefix.'bsms_schemaVersion';
                $oldversionexists = substr_count($table,$studies);
                if ($oldversionexists > 0){$oldversion = true; $olderversiontype = 1; $versiontype = 2;}
            }
            if (!$oldversion)
            {
                   foreach ($tables as $table)
                {
                    $studies = $prefix.'bsms_schemaversion';
                    $olderversionexists = substr_count($table,$studies);
                    if ($olderversionexists > 0){$oldversion = true; $olderversiontype = 2;$versiontype = 2;}
                }
            }
        }
        //Finally if both current version and old version are false, we double check to make sure there are no JBS tables in the database. 
        if (!$currentversion && !$oldversion )
        {
            foreach ($tables as $table)
            {
                $studies = $prefix.'bsms_studies';
                $jbsexists = substr_count($table,$studies);
                if (!$jbsexists){$versiontype = 4;}
                if ($jbsexists > 0){$versiontype = 3;}
            }
        }
        
        //Now we run a switch case on the versiontype and run an install routine accordingly
        switch ($versiontype)
        {
            case 1:
            //This is a current database version so we check to see which version. We query to get the highest build in the version table
            $query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
            $db->setQuery($query);
            $db->query();
            $version = $db->loadObject();
            switch ($version->build)
            {
                case '700':
                    $message = '<tr><td>JBS Version 7.0.0 already installed. Doing file refresh</td></tr>';
                    echo JHtml::_('sliders.panel','Re-installing Version 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                break;
                
                case '624':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message; 
                break;
                
                case '623':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;  
                break;
                
                case '622':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;   
                break;
                
                case '615':
                    $message = 'No special requirements for this version.';
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.5', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;   
                break;
                
                case '614':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                    $install = new jbs614Install();
                    $message = $install->upgrade614();
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.4', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    $message = 'No special requirements for this version.';
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.5', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;    
                break;
            }
           
            break;
            
            case 2:
            //This is an older version of the software so we check it's version
            if ($olderversiontype == 1)
            {
                $db->setQuery ("SELECT schemaVersion  FROM #__bsms_schemaVersion");
            }
            else
            {
                $db->setQuery ("SELECT schemaVersion FROM #__bsms_schemaversion");
            }
            $schema = $db->loadResult();
             switch ($schema)
    			{
    				case '600':
    				    $application->enqueueMessage( 'There was a problem with the install. Please contact customer service' ) ;
                        return false;
    				break;
    			
    				case '608':
    				                            
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.611.upgrade.php');
                        $install = new jbs611Install();
                        $message = $install->upgrade611();
                        echo JHtml::_('sliders.panel','Upgrade to JBS Version 6.0.11a', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                                                  
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel','Upgrade to JBS Version 6.1.4', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel','Upgrade to JBS Version 6.1.5', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel','Upgrade JBS to Version 6.2.1', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel','Upgrading to JBS Version 6.2.3', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel','Upgrade to JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;     
    				break;
    				
    				case '611':
    				      
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.4', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.5', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;      
    				break;
    		
    				case '613':
    				    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.613.upgrade.php');
                        $install = new jbs613Install();
                        $message = $install->upgrade613();
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.0', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                                              
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.0', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.5', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel','Upgrading JBS Version 6.2.3', 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4 to 7.0.0', 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;        
    				break;
    			}
            break;
            
            case 3:
                        //There is a version installed, but it is older than 6.0.8 and we can't upgrade it
                        $application->enqueueMessage( 'There was a problem with the install. You may be using a version older than 6.0.8. Please contact customer service at www.JoomlaBibleStudy.org' ) ;
                        return false;
            break;
	}

	function preflight($type, $parent) {
		echo '&lt;p&gt;'. JText::sprintf('COM_BIBLESTUDY_16_CUSTOM_PREFLIGHT', $type) .'&lt;/p&gt;';
	}

	function postflight($type, $parent) {
		echo '&lt;p&gt;'. JText::sprintf('COM_BIBLESTUDY_16_CUSTOM_POSTFLIGHT', $type) .'&lt;/p&gt;';?>
		<div class="width-100">

<fieldset class="panelform">
<legend><?php echo 'Installation/Upgrade Results'; ?></legend>  
    
<?php echo JHtml::_('sliders.start','content-sliders-1',array('useCookie'=>1)); ?>
    
<?php
echo JHtml::_('sliders.panel','CSS', 'publishing-details'); ?> 
                   
	<?php	
	//Check for presence of css or backup
    jimport('joomla.filesystem.file');
    $src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
    $dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
    $backup = JPATH_SITE.DS.'media'.DS.'com_biblestudy'.DS.'backup'.DS.'biblestudy.css';
    $cssexists = JFile::exists($dest);  
    $backupexists = JFile::exists($backup);
    if (!$cssexists)
    {
        echo '<p><font color="red"><strong>'.JText::sprintf('COM_BIBLESTUDY_16_CSS_FILE_NOT_FOUND').</strong> </font></p>';
        if ($backupexists)
        {
            echo '<p>Backup CSS file found at /images/biblestudy.css <a href="index.php?option=com_biblestudy&view=cssedit&controller=cssedit&task=copycss">Click here to copy from backup.</a></p>';
        }
    else
    {
        $copysuccess = JFile::copy($src, $dest);
        if ($copysuccess)
        {
            echo '<p>'. JText::sprintf('COM_BIBLESTUDY_16_CSS_COPIED') . 'CSS File copied from distribution source'.'</p>';
        }
        else
        {
            echo '<P>'.JText::sprintf('COM_BIBLESTUDY_16_CSS_COPIED_DISCRIPTION').'Problem writing file. Manually copy /components/com_biblestudy/assets/css/biblestudy.css.dist to biblestudy.css</p>';
        }
    }    
    }
    //Check for default details text link image and copy if not present
    $src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'images'.DS.'textfile24.png';
    $dest = JPATH_SITE.DS.'images'.DS.'textfile24.png';
    $imageexists = JFile::exists($dest);
    if (!$imageexists)
    {
        echo "<br /><br />Copying default details link image to images folder";
        if ($imagesuccess = JFile::copy($src, $dest))
        {
            echo '<br />Success copying image to images folder';
        }
        else
        {
            echo '<br />Problem copying details link image to images folder. Manually copy from /components/com_biblestudy/images/textfile24.png to images folder';
        }
    }
	?>
        
	
    <?php echo JHtml::_('sliders.end'); ?>
</fieldset>
</div> <!--end of div for panelform -->
<?php

	// Rest of footer
?>
<div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
<img src="components/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" class="flote: left" />
<strong>Thank you for using Joomla Bible Study!</strong>
<br />
<?php //$mainframe =& JFactory::getApplication(); ?>
<img src = "components/com_biblestudy/images/openbible.png" alt = "Joomla Bible Study" title="Joomla Bible Study" border = "0" align="left" /> Congratulations, Bible Study Message Manager has been installed successfully. 
<p>
Welcome to Joomla Bible Study. Please note if there are any error messages above signified by a red X. You can click on that line to see what went wrong. Please copy it into your clipboard for future reference. This component is designed to help your church communicate the gospel and teachings in the Word of God. Joomla Bible Study allows you to enter detailed information about the studies given and links to multimedia content you have uploaded to your server. You can also display full text or notes. All this is searchable in many different ways and you have a lot of control over how much information is displayed on the front end. </p>
<p>
It is very important that you do a couple of things when you first install the component. </p>
<p>
 1. Go to Components | Bible Study Manager. There should be a sample study there. Now click on the Administration tab. There you will find a few settings you can use. Next click on Templates. There should be a default template listed. This is where the display settings are kept. Take some time to look through the drop downs and see how they affect the various views you set up. You can create new templates if you like. When you create menu items linking to the various view of the component you will choose which template should be accessed. If you are going to only use the default template then just choose that one (the component will default to this template if it can't find one) </p>
 <p>
 2. Go back to Components | Bible Study. Click on the Servers and add your server. Remember, you are building a url that someone could paste into their browser - this isn't to your web root, but your web site address. Be sure to follow the instructions of how to put in that entry. Then go to the Folders link and add a folder under that server. Now you are set to add your first real study (be sure to delete the samples once you are familiar with how the component works). </p>
 <p>
 3. Click on Studies. Just put in some text to add a new study. Then go to the Media Files tab and enter a media file associated with that study. </p>
 <p><a href="http://www.joomlabiblestudy.org/forums.html" target="_blank">Visit our forum with your questions</a></p>
 <p><a href="http://www.joomlabiblestudy.org" target="_blank">Get more help and information at JoomlaBibleStudy.org</a></p>
 <p><a href="http://www.JoomlaBibleStudy.org/jbsdocs" target="_blank">Visit our Documentation Site</a></p>
		<p>Bible Study Component <em>for Joomla! </em> &copy; by <a
			href="http://www.JoomlaBibleStudy.org" target="_blank">www.JoomlaBibleStudy.org</a>.
		All rights reserved.</p>
</div>
<?php
		// An example of setting a redirect to a new location after the install is completed
		//$parent-&gt;getParent()-&gt;set('redirect_url', 'http://www.google.com');
	}
		
    } //end of function uninstall()
  
} // end of class
?>