<?php
/**
 * @version $Id: biblestudy.install.php 1 $
 * Bible Study Component
 * @package Bible Study
 *
 * @Copyright (C) 2007 - 2010 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 *
 * Install Based on Kunena Component
 **/
 //
 // Dont allow direct linking
 defined( '_JEXEC' ) or die('Restricted access');
?>
<?php
@error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Help get past php timeouts if we made it that far
// Joomla 1.5 installer can be very slow and this helps avoid timeouts
@set_time_limit(3600);
$kn_maxTime = @ini_get('max_execution_time');
$maxMem = trim(@ini_get('memory_limit'));
if ($maxMem) {
	$unit = strtolower($maxMem{strlen($maxMem) - 1});
	switch($unit) {
		case 'g':
			$maxMem	*=	1024;
		case 'm':
			$maxMem	*=	1024;
		case 'k':
			$maxMem	*=	1024;
	}
	if ($maxMem < 16000000) {
		@ini_set('memory_limit', '16M');
	}
	if ($maxMem < 32000000) {
		@ini_set('memory_limit', '32M');
	}
	if ($maxMem < 48000000) {
		@ini_set('memory_limit', '48M');
	}
}
ignore_user_abort(true);
// Bible Study wide defines
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
//include_once(BIBLESTUDY_PATH_ADMIN_LIB .DS. 'fx.upgrade.class.php');
include_once(BIBLESTUDY_PATH_ADMIN_INSTALL .DS. 'biblestudy.upgrade.php');
// Install Bible Study Component
function com_install()
{
	$biblestudy_db = JFactory::getDBO();
	// Determine MySQL version from phpinfo
//	$biblestudy_db->setQuery("SELECT VERSION() as mysql_version");
//	$mysqlversion = $biblestudy_db->loadResult();
    $jbsupgrade = new JBSUpgrade();
    //Check to be sure JBS is the correct version for upgrade
    $message = $jbsupgrade->version();
  //  if (!message) {$doupgrade = false;}else {$doupgrade = true;}
	
	
?><div><table><tr><td><?php	
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
	?></td><tr></table></div>
<div style="border: 1px solid #ccc; background: #FBFBFB; padding: 10px; text-align: left; margin: 10px 0;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" summary="">
	<tr>
		<td width="10%" valign="top" style="padding: 10px;"><a
			href="index2.php?option=com_biblestudy"><img
			src="components/com_biblestudy/images/openbible.png" alt="Bible Study"
			border="0"></a></td>
		<td width="90%" valign="top" style="padding: 10px;">
		<div
			style="border: 1px solid #FFCC99; background: #FFFFCC; padding: 20px; margin: 20px; clear: both;">
		<strong>I N S T A L L : <font color="green">Successful</font> </strong>
		<br />
		<br />
		<strong>php version: <font color="green"><?php echo phpversion(); ?></font> (Required &gt;= <?php echo BIBLESTUDY_MIN_PHP; ?>)</strong>
		<br />
		<strong>mysql version: <font color="green"><?php echo $mysqlversion; ?></font> (Required &gt; <?php echo BIBLESTUDY_MIN_MYSQL; ?>)</strong>
		</div>
        <div><?php echo $messsage;?></div>
		</td>
	</tr>
	</table>
</div>
<?php
	

		// Minimum version requirements not satisfied
		
if (!$message)
{ 
?>
<div style="border: 1px solid #ccc; background: #FBFBFB; padding: 10px; text-align: left; margin: 10px 0;">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="20%" valign="top" style="padding: 10px;"><a
			href="index2.php?option=com_biblestudy"><img
			src="components/com_biblestudy/images/openbible.png" alt="Bible Study Management System"
			border="0"></a></td>
		<td width="80%" valign="top" style="padding: 10px;"></div>
		<div
			style="border: 1px solid #FFCC99; background: #FFFFCC; padding: 20px; margin: 20px; clear: both;">
		<strong>I N S T A L L : <font color="red">F A I L E D - Minimum Version Requirements not satisfied. Joomla Bible Study 6.2.4 minimum</font> </strong>
		<br />
		<br />
		<strong>php version: <font color="<?php echo version_compare(phpversion(), BIBLESTUDY_MIN_PHP, '>=')?'green':'red'; ?>"><?php echo phpversion(); ?></font> (Required &gt;= <?php echo BIBLESTUDY_MIN_PHP; ?>)</strong>
		<br />
		<strong>mysql version: <font color="<?php echo version_compare($mysqlversion, BIBLESTUDY_MIN_MYSQL, '>')?'green':'red'; ?>"><?php echo $mysqlversion; ?></font> (Required &gt; <?php echo BIBLESTUDY_MIN_MYSQL; ?>)</strong>
		</td>
	</tr>
</table>
 	</div>
<?php
}	  
	// Rest of footer
?>
<div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
<table><tr><tr><td>
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
?>