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
 **/
 //
 // Dont allow direct linking
 defined( '_JEXEC' ) or die('Restricted access');

// Bible Study wide defines


class com_biblestudyInstallerScript
{

 
    function install($parent)
{
    ?>
     <script type="text/javascript">
window.addEvent('domready', function(){ new Accordion($$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler'), $$('div#content-sliders-1.pane-sliders > .panel > div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_content-sliders-1',$$('div#content-sliders-1.pane-sliders > .panel > h3').indexOf(toggler));},onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');i.removeClass('pane-down');if($$('div#content-sliders-1.pane-sliders > .panel > h3').length==$$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler').length) Cookie.write('jpanesliders_content-sliders-1',-1);},duration: 300,display: 1,show: 1,opacity: false,alwaysHide: true}); });
 </script>
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
            case 4:
            //There is no version installed so we run the fresh installation routine
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.fresh.install.php');
                        $install = new jbsFreshInstall();
                        $message = $install->jbsFresh();
                        echo JHtml::_('sliders.panel','JBS Fresh Installation Version 7.0.0', 'publishing-details'); ?>
                        <fieldset class="panelform">
                         <?php echo $message; 
            break;
        }
?>
<div class="width-100">

<fieldset class="panelform">
<legend><?php echo 'Installation/Upgrade Results'; ?></legend>  
    
<?php echo JHtml::_('sliders.start','content-sliders-1',array('useCookie'=>1)); ?>
    
<?php
echo JHtml::_('sliders.panel','CSS', 'publishing-details'); ?>                     
<fieldset class="panelform">
 
    <table><tr><td><?php	
	//Check for presence of css or backup
    jimport('joomla.filesystem.file');
    $src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
    $dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
    $backup = JPATH_SITE.DS.'images'.DS.'biblestudy.css';
    $cssexists = JFile::exists($dest);  
    $backupexists = JFile::exists($backup);
    if (!$cssexists)
    {
        echo '<br /><font color="red"><strong>CSS File not found.</strong> </font>';
        if ($backupexists)
        {
            echo '<br />Backup CSS file found at /images/biblestudy.css <a href="index.php?option=com_biblestudy&view=cssedit&controller=cssedit&task=copycss">Click here to copy from backup.</a>';
        }
    else
    {
        $copysuccess = JFile::copy($src, $dest);
        if ($copysuccess)
        {
            echo '<br />CSS File copied from distribution source';
        }
        else
        {
            echo '<br />Problem writing file. Manually copy /components/com_biblestudy/assets/css/biblestudy.css.dist to biblestudy.css';
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
	?></td><tr></table>
        
	
    <?php echo JHtml::_('sliders.end'); ?>
</fieldset>
</div> <!--end of div for panelform -->
<?php

	// Rest of footer
}
  
} // end of class
?>