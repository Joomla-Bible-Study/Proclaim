<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php function com_install()
{
?>
<table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
            <tr>
            	<td width = "100%" valign = "top" style = "padding:10px;">
                
<?php 

$database	= & JFactory::getDBO();

//Change the admin menu icon
$database->setQuery("SELECT id FROM #__components WHERE admin_menu_link = 'option=com_biblestudy'");
$id = $database->loadResult();
//add new admin menu images
$database->setQuery("UPDATE #__components " . "SET admin_menu_img  = '../components/com_biblestudy/images/biblemenu.png'" . ",   admin_menu_link = 'option=com_biblestudy' " . "WHERE id='$id'");
$database->query();

//First we check to see if the database has been installed at all
$tables = $database->getTableList();
	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$bsms = false;
	$bsms	= isset( $fields[$tn]['id'] );
	$isdb = '';
	if ($bsms) { $isdb =  'Database is installed. <br />'; }
	else { $isdb = 'Database not installed. Upgrade halted. Please uninstall and check MySQL.<br />'; }
if ($bsms) { //this is the beginninng of the install block. It won't go if the database isn't installed at all

// Sample Data install
	$database->setQuery ("SELECT id FROM #__bsms_studies");
	$database->query();
	$isitnew = $database->loadResult();
	if (!$isitnew){
	$database->setQuery ("INSERT INTO #__bsms_studies VALUES (1, '2008-06-20 00:00:00', 1, '2008-001', 101, 01, 01, 01, 31, 'Sample Study Title', 'Sample text you can use as an introduction to your study', '1', 1, 1, 'This is where you would put study notes or other information. This could be the full text of your study as well.', 1,1,1,1,1,1,0,1,1,1,1,1,1,0,1,1,1,1,1,1,0,1,1,1,1,1,1,0,1,1,1,1,1,1,0,1,1,1,1,1,1,0, 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_servers VALUES (1, 'Your Server Name', 'www.mywebsite.com', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_series VALUES (1, 'Worship Series', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_teachers VALUES (1, 'Billy Graham', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_message_type VALUES (1, 'Sunday', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_folders VALUES (1, 'My Folder Name', '/media/', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_mediafiles VALUES (7, 1, 2, 1, 1, '','myfile.mp3', 12332, 1, 1, 0, '', 0, '2008-06-20 00:00:00', 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_podcast VALUES (1, 'My Podcast', 'www.mywebsite.com', 'Podcast Description goes here', 'http://www.mywebsite/myimage.jpg', 30, 30, 'Pastor Billy', 'http://www.mywebsite/myimage.jpg', 'jesus', 'mypodcast.xml', 'en-us', 'Jim Editor', 'jim@mywebsite.com', 50, 1)");
	$database->query();
	$database->setQuery ("INSERT INTO #__bsms_locations VALUES (1, 'My Location', 1)");
	$database->query();
	}
	
	//end sample data
	
// Check schema version
	$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
	$schema = $database->loadResult();
	if (!$schema) {
		$database->setQuery ("CREATE TABLE IF NOT EXISTS #__bsms_schemaVersion(id int(3) NOT NULL auto_increment, schemaVersion int(10), PRIMARY KEY (id) ) TYPE=MyISAM CHARACTER SET utf8");
		$database->query();
		$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 502)");
		$database->query();
	}
	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$hours = false;
	$hours	= isset( $fields[$tn]['media_hours'] );
	$check = $hours;	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_hours VARCHAR(2) NULL AFTER studyintro;");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['media_minutes'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_minutes VARCHAR(2) NULL AFTER media_hours;");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['media_seconds'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN media_seconds VARCHAR(2) NULL AFTER media_minutes");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['secondary_reference'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN secondary_reference TEXT NULL AFTER chapter_end");
			$database->query();}
	/*$hours = false;
	$hours	= isset( $fields[$tn]['show_level'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN show_level INT(2) NOT NULL default '0' AFTER secondary_reference");
			$database->query();}
	$hours = false;
	$hours	= isset( $fields[$tn]['location_id'] );	
			if (!$hours) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level");
			$database->query();}*/
	///$studies = isset($fields[$tn] ['media_seconds']);
	//if ($check != $studies) { echo 'Added Media duration columns to studies table <br />'; }
	$tn = '#__bsms_teachers';
	$fields = $database->getTableFields( array( $tn ) );
	$title = false;
	$title	= isset( $fields[$tn]['title'] );
	//$check = $title;
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN title VARCHAR(50) NULL AFTER teachername");	$database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['phone'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN phone VARCHAR(50) NULL AFTER title"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['email'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN email VARCHAR(100) NULL AFTER phone"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['website'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN website VARCHAR(250) NULL AFTER email"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['information'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN information TEXT NULL AFTER website"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['image'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN image TEXT NULL AFTER information"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['imageh'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN imageh TEXT NULL AFTER image"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['imagew'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN imagew TEXT NULL AFTER imageh"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumb'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumb TEXT NULL AFTER imagew"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumbw'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumbw TEXT NULL AFTER thumb"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['thumbh'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN thumbh TEXT NULL AFTER thumbw"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['short'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN short TEXT NULL AFTER thumbh"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['ordering'] );	
			if (!$title ) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN ordering INT(3) NULL AFTER short"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['catid'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN catid INT(3) NULL default '1' AFTER ordering"); $database->query();}
	$title = false;
	$title	= isset( $fields[$tn]['list_show'] );	
			if (!$title) {$database->setQuery ("ALTER TABLE #__bsms_teachers ADD COLUMN list_show TINYINT(1) NOT NULL default '1' AFTER catid"); $database->query();}
	//$teachers	= isset( $fields[$tn]['list_show'] );
			//if ($check != $teachers) { echo 'Success in upgrading teachers table <br />'; }
			
			

//Here we check to see if there are already rows in the mediafiles table. If not, we assume this is upgrading from RC5 to 6.0.0 and we move the mediafiles from the studies table	
$database->setQuery ('SELECT COUNT(*) FROM #__bsms_mediafiles');
$database->query();
$mediacheck = $database->loadResult();
if ($mediacheck < 1) {
	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$size = false;
	$size	= isset( $fields[$tn]['media1_size'] );	
			if ($size = false) {  // here we assume that this is an old version without media size fields 
			//Now we move any old media records into their own table
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media1_id, media1_server, media1_path, media1_special, media1_filename, studydate, published FROM #__bsms_studies WHERE media1_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media2_id, media2_server, media2_path, media2_special, media2_filename, studydate, published FROM #__bsms_studies WHERE media2_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media3_id, media3_server, media3_path, media3_special, media3_filename, studydate, published FROM #__bsms_studies WHERE media3_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media4_id, media4_server, media4_path, media4_special, media4_filename, studydate, published FROM #__bsms_studies WHERE media4_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media5_id, media5_server, media5_path, media5_special, media5_filename, studydate, published FROM #__bsms_studies WHERE media5_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, createdate, published) SELECT id, media6_id, media6_server, media6_path, media6_special, media6_filename, studydate, published FROM #__bsms_studies WHERE media6_show = 1');
			$database->query();
			if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().' Error ';
					return false;
				}
			//Now let's drop those media columns from the studies table. We don't need them anymore
			$database->setQuery ("ALTER TABLE #__bsms_studies, DROP media1_id, DROP media1_server, DROP media1_path, DROP media1_special, DROP media1_filename, DROP media1_show, DROP media2_id, DROP media2_server, DROP media2_path, DROP media2_special, DROP media2_filename, DROP media2_show, DROP media3_id, DROP media3_server, DROP media3_path, DROP media3_special, DROP media3_filename, DROP media3_show, DROP media4_id, DROP media4_server, DROP media4_path, DROP media4_special, DROP media4_filename, DROP media4_show, DROP media5_id, DROP media5_server, DROP media5_path, DROP media5_special, DROP media5_filename, DROP media5_show, DROP media6_id, DROP media6_server, DROP media6_path, DROP media6_special, DROP media6_filename, DROP media6_show");
			$database->query(); 
				if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().'. Error dropping columns';
					return false;
				}
			} //end of !$schema
			
			
		if ($schema == 502) { // Here we assume the existance of media_hours and other fields
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media1_id, media1_server, media1_path, media1_special, media1_filename, media1_size, studydate, published FROM #__bsms_studies WHERE media1_show = 1');
			$database->query();
			
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media2_id, media2_server, media2_path, media2_special, media2_filename, media2_size, studydate, published FROM #__bsms_studies WHERE media2_show = 1');
			$database->query();
			
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media3_id, media3_server, media3_path, media3_special, media3_filename, media3_size, studydate, published FROM #__bsms_studies WHERE media3_show = 1');
			$database->query();
			
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media4_id, media4_server, media4_path, media4_special, media4_filename, media4_size, studydate, published FROM #__bsms_studies WHERE media4_show = 1');
			$database->query();
			
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media5_id, media5_server, media5_path, media5_special, media5_filename, media5_size, studydate, published FROM #__bsms_studies WHERE media5_show = 1');
			$database->query();
			
			$database->setQuery ('INSERT INTO #__bsms_mediafiles (study_id, media_image, server, path, special, filename, size, createdate, published) SELECT id, media6_id, media6_server, media6_path, media6_special, media6_filename, media6_size, studydate, published FROM #__bsms_studies WHERE media6_show = 1');
			$database->query();
			
$database->setQuery ("ALTER TABLE #__bsms_studies, DROP media1_id, DROP media1_server, DROP media1_path, DROP media1_special, DROP media1_filename, DROP media1_show, DROP media2_id, DROP media2_server, DROP media2_path, DROP media2_special, DROP media2_filename, DROP media2_show, DROP media3_id, DROP media3_server, DROP media3_path, DROP media3_special, DROP media3_filename, DROP media3_show, DROP media4_id, DROP media4_server, DROP media4_path, DROP media4_special, DROP media4_filename, DROP media4_show, DROP media5_id, DROP media5_server, DROP media5_path, DROP media5_special, DROP media5_filename, DROP media5_show, DROP media6_id, DROP media6_server, DROP media6_path, DROP media6_special, DROP media6_filename, DROP media6_show, DROP media1_size, DROP media2_size, DROP media3_size, DROP media4_size, DROP media5_size, DROP media6_size");
			$database->query(); 
				if ($database->getErrorNum()) {
					echo 'Database Error: '.$database->stderr().'. Error dropping columns';
					return false;
				}
		} // end if schema == 502
			
} // end of if $mediacheck < 1
			
		
		$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
		$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 600)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$schema_version3 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$schema_version3.'<br>';
		
//Begin installation for version 6.0.08

//if ($schema_version3 == 600) {

	$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['booknumber2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN booknumber2 VARCHAR(4) NULL AFTER secondary_reference;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['chapter_begin2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN chapter_begin2 VARCHAR(4) NULL AFTER booknumber2;");
		$database->query();}
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['verse_begin2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN verse_begin2 VARCHAR(4) NULL AFTER chapter_begin2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['chapter_end2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN chapter_end2 VARCHAR(4) NULL AFTER verse_begin2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['verse_end2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN verse_end2 VARCHAR(4) NULL AFTER chapter_end2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['prod_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN prod_dvd VARCHAR(100) NULL AFTER verse_end2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['prod_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN prod_cd VARCHAR(100) NULL AFTER prod_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['server_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN server_cd VARCHAR(10) NULL AFTER prod_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['server_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN server_dvd VARCHAR(10) NULL AFTER server_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['image_cd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN image_cd VARCHAR(10) NULL AFTER server_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['image_dvd'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN image_dvd VARCHAR(10) NULL default '0' AFTER image_cd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['studytext2'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN studytext2 TEXT NULL AFTER image_dvd;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['comments'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN comments TINYINT(1) NULL default '1' AFTER studytext2;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['hits'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN hits INT(10) NOT NULL default '0' AFTER comments;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['user_id'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN user_id INT(10) NULL AFTER hits;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['user_name'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN user_name VARCHAR(50) NULL AFTER user_id;");
		$database->query();}
	
	$tn = '#__bsms_mediafiles';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
	$fieldcheck	= isset( $fields[$tn]['link_type'] );	
	if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN link_type VARCHAR(1) NULL AFTER createdate;");
		$database->query();}
	$fieldcheck	= isset( $fields[$tn]['hits'] );	
	if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_mediafiles ADD COLUMN hits INT(10) NULL AFTER link_type;");
		$database->query();}
	$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 608)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db608 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$db608.'<br>';
//} //End check of $schema_version == 600	
// Begin $schema_version 611
$tn = '#__bsms_studies';
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck = false;
$fieldcheck	= isset( $fields[$tn]['show_level'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN show_level INT(2) NOT NULL default '0' AFTER user_name;");
		$database->query();}
$fieldcheck	= isset( $fields[$tn]['location_id'] );
		if (!$fieldcheck) {$database->setQuery ("ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level;");
		$database->query();}
$tn = '#__bsms_studies';
	$db611 = true;
	$fields = $database->getTableFields( array( $tn ) );
	$fieldcheck	= isset( $fields[$tn]['show_level'] );
		if (!$fieldcheck) { $show_level_message = 'Problem creating field show_level. Check permissions on your MySQL database'; $db611 = false;}
	$fieldcheck	= isset( $fields[$tn]['location_id'] );
		if (!$fieldcheck) { $location_id_message = 'Problem creating field location_id. Check permissions on your MySQL database'; $db611 = false;}
	if (!$db611) { $dbmessage = 'There was a problem with the installation of this version as follows: <br>' . $location_id_message.'<br>'.$show_level_message;}
	else {
		$database->setQuery ("DELETE FROM #__bsms_schemaVersion WHERE id = 1 LIMIT 1");
		$database->query();
	$database->setQuery ("INSERT IGNORE INTO #__bsms_schemaVersion VALUES (1, 611)");
		$database->query();
		$database->setQuery ("SELECT schemaVersion FROM #__bsms_schemaVersion");
		$db611 = $database->loadResult();
		$dbmessage =  'The current database schema for Bible Study is: '.$db611.'<br>';
		}
// End version 611 upgrade
?>
<div class="header"><?php 
global $mainframe; ?>
<img src = "<?php echo $mainframe->getCfg("live_site"); ?>/components/com_biblestudy/images/openbible.png" alt = "" border = "0">Congratulations, Bible Study Message Manager has been installed successfully. </div>
<p>
<?php echo $isdb.'<br>'.$dbmessage; ?>
<p>
Welcome to the Bible Study Message System. Please note if there are any error messages above. This component is designed to help your church communicate the gospel and teachings in the Word of God. com_biblestudy allows you to enter detailed information about the studies given and links to multimedia content you have uploaded to your server. You can also display full text or notes. All this is searchable in many different ways and you have a lot of control over how much information is displayed on the front end. </p>
<p>
It is very important that you do a couple of things when you first install the component. </p>
<p>
 1. Go to Components | Bible Study Manager. There should be a sample study there. Look on the toolbar for Parameters. Click the link. It opens up a window where you set most of the global parameters of the component. For now, just save the preferences. Now go to Menu Main Menu and create a new menu item to the Bible Study List. Click on it after saving and open up the Component Parameters. This is where you should change parameters so they "stick" IF they involved entering a text value. For those with drop down boxes you can use either this menu item or the Parameters from the admin side of the component by leaving the value at Use Global.</p>
 <p>
 2. Go back to Components | Bible Study. Click on the Servers and add your server, then go to the Folders link and add a folder under that server. Now you are set to add your first real study (be sure to delete the samples once you are familiar with how the component works). </p>
 <p>
 3. Click on Studies. Just put in some text to add a new study. Then go to the Media Files tab and enter a media file associated with that study. </p>
 <?php } // end of if ($bsms) for whole db update block	?>
 <p><a href="http://www.joomlaoregon.org/index.php?option=com_fireboard" target="_blank">Visit our forum with your questions</a></p>
 <p><a href="http://www.joomlaoregon.org" target="_blank">Get more help and information at JoomlaOregon.org</a></p>
 </td>
 </tr>
 </table>
 <?php } // end of com_install?>