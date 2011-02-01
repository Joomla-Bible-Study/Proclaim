<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php function com_uninstall()
{
?>

<?php 
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
$params = &JComponentHelper::getParams('com_biblestudy');
$database	= & JFactory::getDBO();
$database->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
$database->query();
$compat = $database->loadObject();
$admin_params = new JParameter($compat->params);
$drop_tables = $admin_params->get('drop_tables');

	if ($drop_tables >0)
	{
		$drop_result = '<table><tr><td><H3>Uninstall Results: Tables removed unless noted below</H3></td></tr>';
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_studies");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_teachers");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_topics");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_servers");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_series");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_message_type");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_folders");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_order");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_search");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_schemaversion");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_media");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_books");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_podcast");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_mimetype");
		$database->query();
		if ($database->getErrorNum()) {
				$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_mediafiles");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_templates");
		$database->query();
		if ($database->getErrorNum()) {
				$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_comments");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_admin");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_studytopics");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_version");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_share");
		$database->query();
		if ($database->getErrorNum()) {
				$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
		$database->setQuery ("DROP TABLE IF EXISTS #__bsms_locations");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
    $database->setQuery ("DROP TABLE IF EXISTS #__bsms_timeset");
		$database->query();
		if ($database->getErrorNum()) {
					$drop_result .=  '<tr><td>Database Error: '.$database->stderr().' </td></tr> ';
					
				}
$mainframe =& JFactory::getApplication(); ?>

<tr><td><table><tr><td><img src = "<?php echo $mainframe->getCfg("live_site"); ?>/components/com_biblestudy/images/openbible.png" alt = "Joomla Bible Study" title="Joomla Bible Study" border = "0" /></td><td><h2>Joomla Bible Study Uninstalled</h2></td></tr></table></td>
<?php
		
		$drop_result .= '</table>';
		echo '</tr><tr><td>'.$drop_result.'</td></tr>'; //dump ($drop_result, 'drop_result: ');
	}
	else
	{
		print '<td>Database tables have not been removed<p> Be sure to uninstall the module and plugin as well. </p> <p> To complete remove Bible Study Management System, remove all database tables that start with #__bsms (or jos_bsms in most cases). </p></td></tr>';
	}

 } //end of com_uninstall
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
?>