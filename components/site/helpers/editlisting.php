<?php
defined('_JEXEC') or die();

function getEditlisting($admin_params, $params) {
	
	$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');;
	$database	= & JFactory::getDBO();
	$editlisting = null; 
	$message = JRequest::getVar('msg');
	$user =& JFactory::getUser();
	$entry_user = $user->get('gid');
	if (!$entry_user) { $entry_user = 0;}
	$entry_access = $admin_params->get('entry_access');
	if (!$entry_access) {$entry_access = 23;}
	$allow_entry = $admin_params->get('allow_entry_study');
	if (!$entry_user) { $entry_user = 0; }
	if (!$entry_access) { $entry_access = 23; }
	if ($allow_entry > 0) {
		if ($entry_access <= $entry_user){
			
			
	if ($message) {
		$editlisting .= '<div class="message'.$params->get('pageclass_sfx').'"><h2>'.$message.'</h2></div>';
	 } //End of if $message
	
	$editlisting .=  '<div id="studyheader">'.JText::_('Studies').'</div>';
	$editlisting .= '<div class="studyedit">';
	$editlisting .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">'.JText::_('Add a New Study').'</a><br />';
	$editlisting .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">'.JText::_('Add a New Media File Record').'</a><br />';
	 if ($params->get('show_comments') > 0){
		$editlisting .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&view=commentslist">'.JText::_('Manage Comments').'</a><br /><br />';
$editlisting .= '</div>';
	 } //end if show_comments
		} //End of testing for if user is authorized
		}//End of testing for $allow_entry
	

// Here we start the test to see if podcast entry allowed
		if ($params->get('allow_podcast') > 0){
			$podcast_access = $params->get('podcast_access');
			if (!$podcast_access) {$podcast_access = 23;}
			if ($podcast_access <= $entry_user){
				$query = ('SELECT id, title, published FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC');
				$database->setQuery( $query );
				$podcasts = $database->loadAssocList();
				
		$editlisting .= '<div id="studyheader">'.JText::_('Podcasts').'</div>';
		$editlisting .= '<br /><div class="podcastlist">'.'<br />';
		$editlisting .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form">'.JText::_('Add A Podcast').'</a>';
		
		foreach ($podcasts as $podcast) { $pod = $podcast['id']; $podtitle = $podcast['title'];
		$editlisting .= '<br /><a href="'.JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form&task=edit&cid[]='.$pod.'">'.$podtitle.'</a>';
		 } // end foreach for podcasts as podcast
	// End row for podcast
	$editlisting .= '</div>';
		} // end of checking podcast authorization
		} // end allow_entry_podcast
		
	else {$editlisting = null;}
	return $editlisting;
}