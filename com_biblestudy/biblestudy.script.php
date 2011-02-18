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
                    $message = JText::_('JBS_VERSION_700_MESSAGE');
                    echo JHtml::_('sliders.panel', JText::_('RE_INSTALLING_VERSION_700') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                break;
                
                case '624':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message; 
                break;
                
                case '623':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;  
                break;
                
                case '622':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_621') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;   
                break;
                
                case '615':
                    $message = 'No special requirements for this version.';
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_621') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
        			</fieldset>
                    <!-- <fieldset class="panelform"> -->
                    <?php //echo $message;   
                break;
                
                case '614':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                    $install = new jbs614Install();
                    $message = $install->upgrade614();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_614') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    $message = 'No special requirements for this version.';
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_621') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623') , 'publishing-details'); ?>
        			</fieldset>
                    <fieldset class="panelform">
                    <?php //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700'), 'publishing-details'); ?>
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
    				    $application->enqueueMessage( ''. JText::_('UPGRADE_JBS_VERSION_PROBLEM') .'' ) ;
                        return false;
    				break;
    			
    				case '608':
    				                            
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.611.upgrade.php');
                        $install = new jbs611Install();
                        $message = $install->upgrade611();
                        echo JHtml::_('sliders.panel', JHtml::_('UPGRADE_JBS_VERSION_6011A') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                                                  
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_614') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADEING_JBS_VERSION_621') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADEING_JBS_VERSION_623') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;     
    				break;
    				
    				case '611':
    				      
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_614') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_621') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623'), 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;      
    				break;
    		
    				case '613':
    				    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.613.upgrade.php');
                        $install = new jbs613Install();
                        $message = $install->upgrade613();
                        echo JHtml::_('sliders.panel', JText::_('Upgrade JBS Version 610') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                                              
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_620') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        $message = 'No special requirements for this version.';
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_621') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_623') , 'publishing-details'); ?>
            			</fieldset>
                        <fieldset class="panelform">
                        <?php //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_624_TO_700') , 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;        
    				break;
    			}
            break;
            
            case 3:
                        //There is a version installed, but it is older than 6.0.8 and we can't upgrade it
                        $application->enqueueMessage( '' . JText::_('UPGRADE_JBS_VERSION_CANT_UPGRADE') . '') ;
                        return false;
            break;
			
			case 4:
			$db =& JFactory::getDBO();
			$query = file_get_contents(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'sql' .DS. 'jbs7.0.0.sql');
			$db->setQuery($query);
			$db->queryBatch();
			echo JHtml::_('sliders.panel', JText::_('INSTALLING_VERSION_700') , 'publishing-details'); ?>
            			</fieldset>
                        <!-- <fieldset class="panelform"> -->
                        <?php //echo $message;
			break;
		}
	}

	function uninstall($parent) {
		require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
		require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'params.php');

		$db =& JFactory::getDBO();
				$db->setQuery ("SELECT * FROM #__bsms_admin WHERE id = 1");
				$db->query();
				$admin = $db->loadObject();
       

				$drop_tables = $admin->drop_tables;

	if ($drop_tables > 0)
	{
		$drop_result = '<div><H3>'. JText::_('COM_BIBLESTUDY_16_CUSTOM_UNINSTALL_SCRIPT') .'</H3>';
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_studies");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_teachers");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_topics");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_servers");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_series");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_message_type");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </tp> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_folders");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_order");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_search");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_schemaversion");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_media");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_books");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_podcast");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_mimetype");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_mediafiles");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_templates");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_comments");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_admin");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_studytopics");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_version");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_share");
		$db->query();
		if ($db->getErrorNum()) {
				$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
		$db->setQuery ("DROP TABLE IF EXISTS #__bsms_locations");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
				}
    $db->setQuery ("DROP TABLE IF EXISTS #__bsms_timeset");
		$db->query();
		if ($db->getErrorNum()) {
					$drop_result .=  '<p>db Error: '.$db->stderr().' </p> ';
					
					}
	$mainframe =& JFactory::getApplication(); ?>

	<h2><?php echo JText::_('Joomla_Bible_Study_Uninstalled'); ?></h2>
	<?php
		
		$drop_result .= '</div>';
		echo '<div><p>'.$drop_result.' </p></div> '; //dump ($drop_result, 'drop_result: ');
	}
	else
	{
		print '<div><p>Database tables have not been removed <br /> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p></div>';
	}

 
	} //end of function uninstall()

	function update($parent) {
		echo '<p>'. JText::_('COM_BIBLESTUDY_16_CUSTOM_UPDATE_SCRIPT') .'</p>';
 
	} // End Update

	function preflight($type, $parent) {
		echo '<p>'. JText::sprintf('COM_BIBLESTUDY_16_CUSTOM_PREFLIGHT', $type) .'</p>';
	}

	function postflight($type, $parent) { ?>
		<div class="width-100">

		<fieldset class="panelform">
		<legend><?php echo JText::sprintf('Installation/Upgrade Results', $type); ?></legend>  
    
		<?php echo JHtml::_('sliders.start','content-sliders-1',array('useCookie'=>1)); 

		echo JHtml::_('sliders.panel','CSS', 'publishing-details');
	
		//Check for presence of css or backup
		jimport('joomla.filesystem.file');
		$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
		$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$backup = JPATH_SITE.DS.'media'.DS.'com_biblestudy'.DS.'backup'.DS.'biblestudy.css';
		$cssexists = JFile::exists($dest);  
		$backupexists = JFile::exists($backup);
		if (!$cssexists)
		{
			echo '<p><font color="red"><strong>'.JText::_('COM_BIBLESTUDY_16_CSS_FILE_NOT_FOUND').'</strong> </font></p>';
			if ($backupexists)
			{
				echo '<p>' . JText::_('COM_BIBLESTUDY_16_BACKUPCSS') .' /images/biblestudy.css <a href="index.php?option=com_biblestudy&view=cssedit&controller=cssedit&task=copycss">'. JText::_('COM_BIBLESTUDY_16_CSS_BACKUP') . '</a></p>';
			}
			else
			{
				$copysuccess = JFile::copy($src, $dest);
				if ($copysuccess)
				{
					echo '<p>'. JText::_('COM_BIBLESTUDY_16_CSS_COPIED_SOURCE') . '</p>';
				}
				else
				{
					echo '<P>'. JText::_('COM_BIBLESTUDY_16_CSS_COPIED_DISCRIPTION1') . '&frasl;components&frasl;com_biblestudy&frasl;assets&frasl;css&frasl;biblestudy.css.dist' . JText::_('COM_BIBLESTUDY_16_CSS_COPIED_DISCRIPTION2') . '</p>';
				}
			}
		}    
		
		//Check for default details text link image and copy if not present
		$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'images'.DS.'textfile24.png';
		$dest = JPATH_SITE.DS.'images'.DS.'textfile24.png';
		$imageexists = JFile::exists($dest);
		if (!$imageexists)
		{
			echo '<br /><br />' . JText::_('COM_BIBLESTUDY_16_COPYING_IMAGE');
			if ($imagesuccess = JFile::copy($src, $dest))
			{
				echo '<br />' . JText::_('COM_BIBLESTUDY_16_COPYING_SUCCESS');
			}
			else
			{
				echo '<br />' . JText::_('COM_BIBLESTUDY_16_COPYING_PROBLEM_FOLDER1') . '/components/com_biblestudy/images/textfile24.png' . JText::_('COM_BIBLESTUDY_16_COPYING_PROBLEM_FOLDER2');
			}
		}
	?>
        
	
		<?php echo JHtml::_('sliders.end'); ?>
		</fieldset>
		</div> <!--end of div for panelform -->

			<!-- Rest of footer -->
		<div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
		<img src="components/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" class="flote: left" />
		<strong><?php echo JText::_('COM_BIBLESTUDY_16_THANK_YOU'); ?></strong>
		<br />
		<?php //$mainframe =& JFactory::getApplication(); ?>
		<img src = "components/com_biblestudy/images/openbible.png" alt = "Joomla Bible Study" title="Joomla Bible Study" border = "0" align="left" /> <?php echo JText::_('COM_BIBLESTUDY_16_CONGRATULATIONS'); ?> 
		<p>
		<?php echo JText::_('COM_BIBLESTUDY_16_STATEMENT1'); ?> </p>
		<p>
		<?php echo JText::_('COM_BIBLESTUDY_16_STATEMENT2'); ?></p>
		<p>
		<?php echo JText::_('COM_BIBLESTUDY_16_STATEMENT3'); ?></p>
		<p>
		<?php echo JText::_('COM_BIBLESTUDY_16_STATEMENT4'); ?></p>
		<p>
		</p>
		<p><a href="http://www.joomlabiblestudy.org/forums.html" target="_blank"><?php echo JText::_('COM_BIBLESTUDY_16_VISIT_FORUM'); ?></a></p>
		<p><a href="http://www.joomlabiblestudy.org" target="_blank"><?php echo JText::_('COM_BIBLESTUDY_16_GET_MORE_HELP'); ?></a></p>
		<p><a href="http://www.JoomlaBibleStudy.org/jbsdocs" target="_blank"><?php echo JText::_('COM_BIBLESTUDY_16_VISIT_DOCUMENTATION'); ?></a></p>
		<p>Bible Study Component <em>for Joomla! </em> &copy; by <a
			href="http://www.JoomlaBibleStudy.org" target="_blank">www.JoomlaBibleStudy.org</a>.
		All rights reserved.</p>
		</div>
		<?php
		// An example of setting a redirect to a new location after the install is completed
		//$parent-&gt;getParent()-&gt;set('redirect_url', 'http://www.google.com');
	}
  
} // end of class
?>