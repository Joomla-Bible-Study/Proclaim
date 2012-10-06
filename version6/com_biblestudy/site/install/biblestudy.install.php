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

// get right Language file
if (file_exists(BIBLESTUDY_PATH_ADMIN_LANGUAGE .DS. 'biblestudy.' . BIBLESTUDY_LANGUAGE . '.php')) {
    include_once (BIBLESTUDY_PATH_ADMIN_LANGUAGE .DS. 'biblestudy.' . BIBLESTUDY_LANGUAGE . '.php');
    }
else {
    include_once (BIBLESTUDY_PATH_ADMIN_LANGUAGE .DS. 'biblestudy.english.php');
    }

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
		//change menu icon
		$biblestudy_db->setQuery("SELECT id FROM #__components WHERE admin_menu_link = 'option=com_biblestudy'");
		$id = $biblestudy_db->loadResult();
		check_dberror("Unable to find component.");

		//add new admin menu images
		$biblestudy_db->setQuery("UPDATE #__components SET admin_menu_img  = 'components/com_biblestudy/images/biblemenu.png'" . ",   admin_menu_link = 'option=com_biblestudy' " . "WHERE id='".$id."'");
		$biblestudy_db->query();
		check_dbwarning("Unable to set admin menu image.");

		//install & upgrade class
		$bsmsupgrade = new fx_Upgrade("com_biblestudy", "biblestudy.install.upgrade.xml", "bsms_", "install", false);

		// Start Installation/Upgrade
		$bsmsupgrade->doUpgrade();

		// THIS PROCEDURE IS UNTRANSLATED!
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
			src="components/com_biblestudy/images/openbible.png" alt="Bible Study"
			border="0"></a></td>

		<td width="80%" valign="top" style="padding: 10px;">
		<div style="clear: both; text-align: left; padding: 0 20px;">
		<ul class="fbscs">
		<?php /*

			// Tom is commenting this out as we don't need this
			// We might want to make the file copy below part of the install as well
			//

			jimport('joomla.filesystem.folder');
		    $ret = JFolder::copy(JPATH_ROOT .DS. "components" .DS. "com_biblestudy" .DS. "biblestudy.files.distribution",
		    				JPATH_ROOT .DS. "images" .DS. "bsmsfiles", '', true);

			if ($ret !== true)
			{
			?>
<!-- Tom is commenting this out as we don't need it.
			<li class="fbscslisterror">
			<div
				style="border: 1px solid #FF6666; background: #FFCC99; padding: 10px; text-align: left; margin: 10px 0;">
			<img src='images/publish_x.png' align='absmiddle' />
			Creation/permission setting of the following directories failed: <br />
			<pre> <?php echo JPATH_ROOT; ?>/images/fbfiles/
			<?php echo JPATH_ROOT;?>/images/fbfiles/avatars
			<?php echo JPATH_ROOT;?>/images/fbfiles/avatars/gallery (you have to put avatars inside if you want to use it)
			<?php echo JPATH_ROOT;?>/images/fbfiles/category_images
			<?php echo JPATH_ROOT;?>/images/fbfiles/files
			<?php echo JPATH_ROOT;?>/images/fbfiles/images
</pre> a) You can copy the contents of _biblestudy.files.distribution under
			components/com_biblestudy to your Joomla root, under images/ folder.

			<br />
			b) If you already have the contents there, but Bible Study installation
			was not able to make them writable, then please do it manually.</div>

			</li>

			<?php
			}
	*/	?>
		</ul>
		</div>
-->
		<div
			style="border: 1px solid #FFCC99; background: #FFFFCC; padding: 20px; margin: 20px; clear: both;">
		<strong>I N S T A L L : <font color="green">Successful</font> </strong>
		<br />
		<br />
		<strong>php version: <font color="green"><?php echo phpversion(); ?></font> (Required &gt;= <?php echo BIBLESTUDY_MIN_PHP; ?>)</strong>
		<br />
		<strong>mysql version: <font color="green"><?php echo $mysqlversion; ?></font> (Required &gt; <?php echo BIBLESTUDY_MIN_MYSQL; ?>)</strong>
		</div>

		<?php
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
		<strong>Thank you for using Bible Study!</strong> <br />

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
