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
    require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');

   // include_once(BIBLESTUDY_PATH_ADMIN_INSTALL .DS. 'biblestudy.upgrade.php');
        $application = JFactory::getApplication();
        $db = JFactory::getDBO();
        $build = '';
        $start = 1;
        //check to be sure a really early version is not installed 1 = older version 2 = no version 3 = correct version
            $query = "SELECT * FROM #__bsms_studies";
            $db->setQuery($query);
            $db->query();
            $version = $db->loadObjectList();
            if (!$version){$build = 'fresh';}
            
        $query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
        $db->setQuery($query);
        $db->query();
        $versions = $db->loadObject();
        //If there are no versions then it must be an older version of the component
        if (!$versions)
        {
     		$db->setQuery ("SELECT schemaVersion  FROM #__bsms_schemaVersion");
    		$schema = $db->loadResult(); //dump ($schema, 'schema: ');
            if (!schema)
            {
                $db->setQuery ("SELECT schemaVersion FROM #__bsms_schemaversion");
                $schema = $db->loadResult(); 
            }
    		if ($schema)
    		{
    			switch ($schema)
    			{
    				case '600':
    				$build = '600';
                    $start = 2;
    				break;
    			
    				case '608':
    				$build = '608';
                    $start = 3;
    				break;
    				
    				case '611':
    				$build = '611';
                    $start = 4;
    				break;
    				
    				case '612':
                    $build = '613';
                    $start = 6;
    				break;
    				
    				case '613':
    				$build = '613';
                    $start = 6;
    				break;
    			}
            }
        }
        else
        {
            foreach ($versions AS $version)
            {
                if ($version->build == '700')
                {
                    $build = '700';
                    $start = 12;
                }
                if ($version->build == '624')
                {
                    $build = '624';
                    $start = 11; 
                }
                if ($version->build == '623')
                {
                    $build = '623';
                    $start = 10;
                }
                if ($version->build == '622')
                {
                    $build = '622';
                    $start = 9;
                }
                if ($version->build == '615')
                {
                    $build = '615';
                    $start = 8;
                }
                if ($version->build == '614')
                {
                    $build = '614';
                    $start = 7;
                }
            }
        }
    
// Install Bible Study Component
    // $parent is the class calling this method
 //   $parent->getParent()->setRedirectURL('index.php?option=com_biblestudy');

   
	?>

<?php
    $biblestudy_db = JFactory::getDBO();
	
  //  $jbsupgrade = new JBSUpgrade();
    //Check to be sure JBS is the correct version for upgrade
  
  //  $message = $jbsupgrade->version();
   
   
if (!$message)
{
    $application->enqueueMessage( 'Joomla Bible Study version 6.2.4 required as minimum for install of 7.0.0' ) ;
    return false;
}	

?>
<div class="width-100">

<fieldset class="panelform">
<legend><?php echo 'Installation/Upgrade Results'; ?></legend>  
    
<?php echo JHtml::_('sliders.start','content-sliders-1',array('useCookie'=>1)); ?>
    
    <?php //Here is where we start processing each $build to see which files to include and report
    for($counter = $start; $counter < 13; $counter++)
    {
        switch ($counter)
        {
            case 1:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.fresh.install.php');
            $message = jbsFresh();
            echo JHtml::_('sliders.panel','JBS Fresh Installation Version 7.0.0', 'publishing-details'); ?>
            <fieldset class="panelform">
             <?php echo $message; 
            break;
            
            case 2:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.607.upgrade.php');
            $message = upgrade607();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.0.7', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message;     
            break;
            
            case 3:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.608.upgrade.php');
            $message = upgrade608();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.0.8', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 4:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.611.upgrade.php');
            $message = upgrade611();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.0.11a', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 6:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.613.upgrade.php');
            $message = upgrade613();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.1.0', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 7:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.614.upgrade.php');
            $message = upgrade614();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.0', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 8:
            $message = 'No special requirements for this version.';
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.5.5', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            
            case 9:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.622.upgrade.php');
            $message = upgrade622();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.1', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 10:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.623.upgrade.php');
            $message = upgrade623();
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.3', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 11:
            $message = '<tr><td>No special database changes required for this version</td></tr>';
            echo JHtml::_('sliders.panel','Upgrade JBS Version 6.2.4', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;
            
            case 11:
            require_once (JPATH_ADMINISTRATOR .DS. 'component' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.700.upgrade.php');
            $message = upgrade700(); 
            echo JHtml::_('sliders.panel','Upgrade JBS Version 7.0.0', 'publishing-details'); ?>
            <fieldset class="panelform">
            <?php echo $message; 
            break;

            case 12:
            $message = '<tr><td>JBS Version 7.0.0 already installed. Doing file refresh</td></tr>';
            break;
            
        }
    }
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

</div> <!--end of div for panelform -->
<?php

	// Rest of footer
?>
<div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
<table><tr><td><img src="components/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" /></td></tr>
<tr><td>
<strong>Thank you for using Joomla Bible Study!</strong>
<br />
<?php $mainframe =& JFactory::getApplication(); ?>
<img src = "<?php echo JPATH_ROOT ?>/components/com_biblestudy/images/openbible.png" alt = "Joomla Bible Study" title="Joomla Bible Study" border = "0" /> Congratulations, Bible Study Message Manager has been installed successfully. 
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
		</td>
	</tr>
</table>
</div>
	<?php

}


    
function uninstall($parent)
{
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

<tr><td><table><tr><td></td><td><h2>Joomla Bible Study Uninstalled</h2></td></tr></table></td>
<?php
		
		$drop_result .= '</table>';
		echo '</tr><tr><td>'.$drop_result.'</td></tr>'; //dump ($drop_result, 'drop_result: ');
	}
	else
	{
		print '<td>Database tables have not been removed<p> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p></td></tr>';
	}

 
    } //end of function uninstall()
  
} // end of class
?>