<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php function com_uninstall()
{
?>
<div class="header">Bible Study Message Manager has been uninstalled successfully. </div>
<p>
<?php 
global $mainframe, $option;
$params = &JComponentHelper::getParams('com_biblestudy');
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
                                    print '<li >Successful - Bible Study Tables Dropped</li>';
                                    }
                                else {
                                    print '<li >Attempt to drop tables not successfull. They must be manually removed.</li>';
                                    }
	}
	else
	{
		print '<li >Database tables have not been removed<p> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p></li>';
	}

 } //end of com_uninstall?>