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

@error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Help get past php timeouts if we made it that far
// Joomla 1.5 installer can be very slow and this helps avoid timeouts
@set_time_limit(300);
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

// Kunena wide defines
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');

include_once(BIBLESTUDY_PATH_ADMIN_LIB .DS. 'fx.upgrade.class.php');

function com_install()
{
	$biblestudy_db = JFactory::getDBO();

	// Determine MySQL version from phpinfo
	$biblestudy_db->setQuery("SELECT VERSION() as mysql_version");
	$mysqlversion = $biblestudy_db->loadResult();

	//before we do anything else we want to check for minimum system requirements
	if (version_compare(phpversion(), BIBLESTUDY_MIN_PHP, ">=") && version_compare($mysqlversion, BIBLESTUDY_MIN_MYSQL, ">"))
	{
				
		//Change the admin menu icon
		$biblestudy_db->setQuery("SELECT id FROM #__components WHERE admin_menu_link = 'option=com_biblestudy'");
		$id = $biblestudy_db->loadResult(); 
		//add new admin menu images
		$biblestudy_db->setQuery("UPDATE #__components SET admin_menu_img  = '../components/com_biblestudy/images/biblemenu.png', admin_menu_link = 'option=com_biblestudy' WHERE id='".$id."'");
		$biblestudy_db->query();


		//install & upgrade class
		$bsmsupgrade = new fx_Upgrade("com_biblestudy", "biblestudy.install.upgrade.xml", "bsms_", "install", false);

		// Start Installation/Upgrade
		$bsmsupgrade->doUpgrade();


	?>

<style>
.fbscs {
	margin: 0;
	padding: 0;
	list-style: none;
}

.fbscslist {
	list-style: none;
	padding: 5px 10px;
	margin: 3px 0;
	border: 1px solid #66CC66;
	background: #D6FEB8;
	display: block;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333;
}

.fbscslisterror {
	list-style: none;
	padding: 5px 10px;
	margin: 3px 0;
	border: 1px solid #FF9999;
	background: #FFCCCC;
	display: block;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333;
}
</style>

<div style="border: 1px solid #ccc; background: #FBFBFB; padding: 10px; text-align: left; margin: 10px 0;">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
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

</div>		<?php
	}
	else
	{
		// Minimum version requirements not satisfied
		?>
<style>
.fbscs {
	margin: 0;
	padding: 0;
	list-style: none;
}

.fbscslist {
	list-style: none;
	padding: 5px 10px;
	margin: 3px 0;
	border: 1px solid #66CC66;
	background: #D6FEB8;
	display: block;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333;
}

.fbscslisterror {
	list-style: none;
	padding: 5px 10px;
	margin: 3px 0;
	border: 1px solid #FF9999;
	background: #FFCCCC;
	display: block;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333;
}
</style>

<div style="border: 1px solid #ccc; background: #FBFBFB; padding: 10px; text-align: left; margin: 10px 0;">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="20%" valign="top" style="padding: 10px;"><a
			href="index2.php?option=com_biblestudy"><img
			src="components/com_biblestudy/images/openbible.png" alt="Bible Study Management System"
			border="0"></a></td>

		<td width="80%" valign="top" style="padding: 10px;">

		<div
			style="border: 1px solid #FFCC99; background: #FFFFCC; padding: 20px; margin: 20px; clear: both;">
		<strong>I N S T A L L : <font color="red">F A I L E D - Minimum Version Requirements not satisfied</font> </strong>
		<br />
		<br />
		<strong>php version: <font color="<?php echo version_compare(phpversion(), BIBLESTUDY_MIN_PHP, '>=')?'green':'red'; ?>"><?php echo phpversion(); ?></font> (Required &gt;= <?php echo BIBLESTUDY_MIN_PHP; ?>)</strong>
		<br />
		<strong>mysql version: <font color="<?php echo version_compare($mysqlversion, BIBLESTUDY_MIN_MYSQL, '>')?'green':'red'; ?>"><?php echo $mysqlversion; ?></font> (Required &gt; <?php echo BIBLESTUDY_MIN_MYSQL; ?>)</strong>
		</div>

		<?php
	}

	// Rest of footer
	?>
		<div
			style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
		<strong>Thank you for using Joomla Bible Study!</strong> <br />
<?php 
global $mainframe; ?>

<img src = "<?php echo $mainframe->getCfg("live_site"); ?>/components/com_biblestudy/images/openbible.png" alt = "Joomla Bible Study" title="Joomla Bible Study" border = "0" /> Congratulations, Bible Study Message Manager has been installed successfully. </div>
<p>

<p>
Welcome to the Bible Study Message System. Please note if there are any error messages above. This component is designed to help your church communicate the gospel and teachings in the Word of God. com_biblestudy allows you to enter detailed information about the studies given and links to multimedia content you have uploaded to your server. You can also display full text or notes. All this is searchable in many different ways and you have a lot of control over how much information is displayed on the front end. </p>
<p>
It is very important that you do a couple of things when you first install the component. </p>
<p>
 1. Go to Components | Bible Study Manager. There should be a sample study there. Now click on the Administration tab. There you will find a few settings you can use. Next click on Templates. There should be a default template listed. This is where the display settings are kept. Take some time to look through the drop downs and see how they affect the various views you set up. You can create new templates if you like. </p>
 <p>
 2. Go back to Components | Bible Study. Click on the Servers and add your server, then go to the Folders link and add a folder under that server. Now you are set to add your first real study (be sure to delete the samples once you are familiar with how the component works). </p>
 <p>
 3. Click on Studies. Just put in some text to add a new study. Then go to the Media Files tab and enter a media file associated with that study. </p>

 <p><a href="http://www.joomlabiblestudy.org/forums.html" target="_blank">Visit our forum with your questions</a></p>
 <p><a href="http://www.joomlabiblestudy.org" target="_blank">Get more help and information at JoomlaBibleStudy.org</a></p>
		Bible Study Component <em>for Joomla! </em> &copy; by <a
			href="http://www.JoomlaBibleStudy.org" target="_blank">www.JoomlaBibleStudy.org</a>.
		All rights reserved.</div>
		</td>
	</tr>
</table>
</div>
	<?php

}
?>
