<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class biblestudyControllerpodcastedit extends JController
{
 /**
 * constructor (registers additional tasks to methods)
 * @return void
 */
 function __construct()
 {
  parent::__construct();

  // Register Extra tasks
  $this->registerTask( 'add' , 'edit', 'WriteXML' );
 }

 /**
 * display the edit form
 * @return void
 */
 function edit()
 {
  JRequest::setVar( 'view', 'podcastedit' );
  JRequest::setVar( 'layout', 'form' );
  JRequest::setVar('hidemainmenu', 1);

  parent::display();
 }

 /**
 * save a record (and redirect to main page)
 * @return void
 */
 function save()
 {
  $model = $this->getModel('podcastedit');

  if ($model->store($post)) {
   $msg = JText::_( 'Podcast Saved!' );
  } else {
   $msg = JText::_( 'Error Saving Podcast' );
  }

  // Check the table in so it can be edited.... we are done with it anyway
  $link = 'index.php?option=com_biblestudy&view=podcastlist';
  $this->setRedirect($link, $msg);
 }

 /**
 * remove record(s)
 * @return void
 */
 function remove()
 {
  $model = $this->getModel('podcastedit');
  if(!$model->delete()) {
   $msg = JText::_( 'Error: One or More podcast Could not be Deleted' );
  } else {
   $msg = JText::_( 'Podcast(s) Deleted' );
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist', $msg );
 }
function publish()
 {
  global $mainframe;

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'Select an item to publish' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 1)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist' );
 }


 function unpublish()
 {
  global $mainframe;

  $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

  if (!is_array( $cid ) || count( $cid ) < 1) {
   JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
  }

  $model = $this->getModel('podcastedit');
  if(!$model->publish($cid, 0)) {
   echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
  }

  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist' );
 }
 /**
 * cancel editing a record
 * @return void
 */
 function cancel()
 {
  $msg = JText::_( 'Operation Cancelled' );
  $this->setRedirect( 'index.php?option=com_biblestudy&view=podcastlist', $msg );
 }
 
 
 function writeXML()
 {

  global $mainframe, $option;
  //$params =& $mainframe->getPageParameters();
  $params = &JComponentHelper::getParams($option);
  $path1 = JPATH_COMPONENT_SITE.DS.'helpers'.DS;
	include_once($path1.'custom.php');
  jimport('joomla.utilities.date');
  $year = '('.date('Y').')';
  $date = date('r');
  global $mainframe, $option;
  $cid = JRequest::getVar('cid');
  $db =& JFactory::getDBO();
  //Let's get the data from the podcast
  $query = 'SELECT * FROM #__bsms_podcast WHERE #__bsms_podcast.id = '.$cid;
  $db->setQuery($query);
  $podinfo = $db->loadObject();
  $description = str_replace("&","and",$podinfo->description);
  $client =& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
  $detailsitemid = $params->get('detailsitemid');
  $detailsitemid = '&amp;Itemid='.$detailsitemid;
  $podhead = '<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
<channel>
	<title>'.$podinfo->title.'</title>
	<link>http://'.$podinfo->website.'</link>
	<description>'.$description.'</description>
	<itunes:summary>'.$description.'</itunes:summary>
	<itunes:subtitle>'.$podinfo->title.'</itunes:subtitle>
	<image>
		<link>http://'.$podinfo->website.'</link>
		<url>http://'.$podinfo->image.'</url>
		<title>'.$podinfo->title.'</title>
		<height>'.$podinfo->imageh.'</height>
		<width>'.$podinfo->imagew.'</width>
	</image>
	<itunes:image href="http://'.$podinfo->podcastimage.'" />
	<category>Religion &amp; Spirituality</category>
	<itunes:category text="Religion &amp; Spirituality">
		<itunes:category text="Christianity" />
	</itunes:category>
	<language>'.$podinfo->language.'</language>
	<copyright>'.$year.' All rights reserved.</copyright>
	<pubDate>'.$date.'</pubDate>
	<lastBuildDate>'.$date.'</lastBuildDate>
	<generator>Bible Study Message Management System</generator>
	<managingEditor>'.$podinfo->editor_email.' ('.$podinfo->editor_name.')</managingEditor>
	<webMaster>'.$podinfo->editor_email.' ('.$podinfo->editor_name.')</webMaster>
	<itunes:owner>
		<itunes:name>'.$podinfo->editor_name.'</itunes:name>
		<itunes:email>'.$podinfo->editor_email.'</itunes:email>
	</itunes:owner>
	<itunes:author>'.$podinfo->editor_name.'</itunes:author>
	<itunes:explicit>no</itunes:explicit>
	<ttl>1</ttl>
	<atom:link href="http://'.$podinfo->website.'/'.$podinfo->filename.'" rel="self" type="application/rss+xml" />
';
  //Now let's get the podcast episodes
  $limit = $podinfo->podcastlimit;
   if ($limit > 0) {
    $limit = 'LIMIT '.$limit;
   }
   else {
    $limit = '';
   }
  $query = 'SELECT p.id AS pid, p.podcastlimit,'
   . ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate,'
   . ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
   . ' s.media_hours, s.media_minutes, s.media_seconds,'
   . ' sr.id AS srid, sr.server_path,'
   . ' f.id AS fid, f.folderpath,'
   . ' t.id AS tid, t.teachername,'
   . ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
   . ' mt.id AS mtid, mt.mimetype'
   . ' FROM #__bsms_mediafiles AS mf'
   . ' LEFT JOIN #__bsms_studies AS s ON (s.id = mf.study_id)'
   . ' LEFT JOIN #__bsms_servers AS sr ON (sr.id = mf.server)'
   . ' LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)'
   . ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
   . ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
   . ' LEFT JOIN #__bsms_mimetype AS mt ON (mt.id = mf.mime_type)'
   . ' LEFT JOIN #__bsms_podcast AS p ON (p.id = mf.podcast_id)'
   . ' WHERE mf.podcast_id = '.$cid.' AND s.published = 1 AND mf.published = 1 ORDER BY createdate DESC '.$limit;
  $db->setQuery( $query );
  $episodes = $db->loadObjectList();
  $episodedetail = '';
  foreach ($episodes as $episode) {
  $episodedate = date("r",strtotime($episode->createdate));
  $hours = $episode->media_hours;
  if (!$hours) { $hours = '00'; }
  if ($hours < 1) { $hours = '00'; }
  if ($hours > 0) { $hours = $hours; }
  else { $hours = '00'; }
  if (!$episode->media_seconds) {$episode->media_seconds = 1;}
  //$podcast_title = 1;
  $pod_title = $podinfo->episodetitle;

  switch ($pod_title)
  {
   case 0:
    $title = $episode->bookname.' '.$episode->chapter_begin.' - '.$episode->studytitle;
    break;
   case 1:
    $title = $episode->studytitle;
    break;
   case 2:
    $title = $episode->bookname.' '.$episode->chapter_begin;
    break;
   case 3:
    $title = $episode->studytitle.' - '.$episode->bookname.' '.$episode->chapter_begin;
    break;
   case 4:
    $title = $episodedate.' - '.$episode->bookname.' '.$episode->chapter_begin.' - '.$episode->studytitle;
    break;
   case 5:
    $element = getCustom($rowid='row1col1', $podinfo->custom, $episode, $params);
	//dump ($episode->custom, 'custom: ');
	$title = $element->element;
	break;
  }
  $title = str_replace('&',"and",$title);
  $description = str_replace('&',"and",$episode->studyintro);
  $episodedetailtemp = '';
  $episodedetailtemp = '	<item>
		<title>'.$title.'</title>
		<link>http://'.$podinfo->website.'/index.php?option=com_biblestudy&amp;view=studydetails&amp;id='.$episode->sid.$detailsitemid.'</link>
		<comments>http://'.$podinfo->website.'/index.php?option=com_biblestudy&amp;view=studydetails&amp;id='.$episode->sid.$detailsitemid.'</comments>
		<itunes:author>'.$episode->teachername.'</itunes:author>
		<dc:creator>'.$episode->teachername.'</dc:creator>
		<description>'.$description.'</description>
		<content:encoded>'.$description.'</content:encoded>
		<pubDate>'.$episodedate.'</pubDate>
		<itunes:subtitle>'.$title.'</itunes:subtitle>
		<itunes:summary>'.$description.'</itunes:summary>
		<itunes:keywords>'.$podinfo->podcastsearch.'</itunes:keywords>
		<itunes:duration>'.$hours.':'.sprintf("%02d", $episode->media_minutes).':'.sprintf("%02d", $episode->media_seconds).'</itunes:duration>
		<enclosure url="http://'.$episode->server_path.$episode->folderpath.str_replace(' ',"%20",$episode->filename).'" length="'.$episode->size.'" type="'.$episode->mimetype.'" />
		<guid>http://'.$episode->server_path.$episode->folderpath.str_replace(' ',"%20",$episode->filename).'</guid>
		<itunes:explicit>no</itunes:explicit>
	</item>
';
  $episodedetail = $episodedetail.$episodedetailtemp;
  } //end of foreach for episode details
  $podfoot = '</channel>
</rss>';
  $filecontent = $podhead.$episodedetail.$podfoot;
  
  // Initialize some variables
  $option = JRequest::getCmd('option');

  if (!$filecontent) {
   $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('Operation Failed').': '.JText::_('Content empty.'));
  }

  // Set FTP credentials, if given
  jimport('joomla.client.helper');
  jimport('joomla.filesystem.file');
  JClientHelper::setCredentialsFromRequest('ftp');
  $ftp = JClientHelper::getCredentials('ftp');
  $client =& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
  //$file = $client->path.DS.'templates'.DS.$template.DS.'index.php';
  $file = $client->path.DS.$podinfo->filename;


  // Try to make the template file writeable
  if (JFile::exists($file) && !$ftp['enabled'] && !JPath::setPermissions($file, '0755')) {
   JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file writable');
  }

  $return = JFile::write($file, $filecontent);

  // Try to make the template file unwriteable
  if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555')) {
   JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the file unwritable');
  }

  if ($return)
  {
   $task = JRequest::getCmd('task');
   switch($task)
   {
    case 'apply_source':
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_($podinfo->filename.' saved'));
     break;

    case 'save_source':
    default:
     $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_($podinfo->filename.' saved'));
     break;
   }
  }
  else {
   $mainframe->redirect('index.php?option='.$option.'&view=podcastlist', JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
  }
  
 }

}
?>