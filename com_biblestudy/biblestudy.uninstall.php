<?php
/**
* @package Redirect-On-Login (com_redirectonlogin)
* @version 1.1.0
* @copyright Copyright (C) 2008 Carsten Engel. All rights reserved.
* @license GPL versions free/trial/pro
* @author http://www.pages-and-items.com
* @joomla Joomla is Free Software
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

function com_uninstall()
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
?>