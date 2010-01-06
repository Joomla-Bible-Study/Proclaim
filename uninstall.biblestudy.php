<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php function com_uninstall()
{
?>
<div class="header">Bible Study Message Manager has been uninstalled successfully. </div>
<p>
<?php 
global $mainframe, $option;
$params = &JComponentHelper::getParams('com_biblestudy');

//Here we reinstate the All Videos Reloaded view.html.php file to its original state
jimport('joomla.filesystem.file');
$dest = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view.html.php';
$avrbackup = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view2.html.php';
$avrexists = JFile::exists($dest);
if ($avrexists)
{
	$avrread = JFile::read($dest);
	$isbsms = substr_count($avrread,'JoomlaBibleStudy');
}
if ($isbsms)
{
	if (!JFile::delete($dest))
	{
		echo 'Unable to delete Joomla Bible Study All Videos Reloaded File in /components/com_avreloaded/views/popup/view.html.php<br>';
	}
	if (!JFile::copy($avrbackup, $dest))
	{
		echo 'Unable to copy original All Videos Reloaded File /components/com_avreloaded/components/views/popup/view2.html.php to view.html.php. Please copy manually<br>';
	}
	else
	{
		echo 'Successfully reinstated All Videos Reloaded file<br>';
	}
}
$database	= & JFactory::getDBO();
$database->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
$database->query();
$compat = $database->loadObject();
$admin_params = new JParameter($compat->params);
$drop_tables = $admin_params->get('drop_tables');

	if ($drop_tables >0)
	{
		
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_studies");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_teachers");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_topics");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_servers");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_series");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_message_type");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_folders");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_order");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_search");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_schemaVersion");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_media");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_books");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_podcast");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_mimetype");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_mediafiles");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_templates");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_comments");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_admin");
		$database->query();
		if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
		if ($database->query()) {
                                    print '<tr><td><li >Successful - Bible Study Tables Dropped</li></td></tr>';
                                    }
                                else {
                                    print '<tr><td><li >Attempt to drop tables not successfull. They must be manually removed.</li></td></tr>';
                                    }
	}
	else
	{
		print '<li >Database tables have not been removed<p> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p></li>';
	}

 } //end of com_uninstall?>