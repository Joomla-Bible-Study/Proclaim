<?php defined('_JEXEC') or die();

function getComments($params, $row)
{
		$database	= & JFactory::getDBO();
		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1'
				.' AND c.study_id = '.$row->id.' ORDER BY c.comment_date ASC';
		$database->setQuery($query);
		$commentsresult = $database->loadObjectList();
		$pageclass_sfx = $params->get('pageclass_sfx');
		
		$commentjava = "javascript:ReverseDisplay('comments')";
		$comments = '<strong><a class="heading'.$pageclass_sfx.'" href="'.$commentjava.'">>>'.JText::_('Show/Hide Comments').'<<</a>
		<div id="comments" style="display:none;"></strong>';

$comments .= '
		<div id="commentsheader'.$params->get('pageclass_sfx').'">
		'.JText::_('Comments').'<br></div>';

	if (count($commentsresult)) {
 		$comments .= '<div id="commentstext'.$pageclass_sfx.'">'
		;
		foreach ($commentsresult as $comment){

		$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') , '$offset' );
		
		$comments .= '<strong>'.$comment->full_name.'</strong> <i>'.$comment_date_display.'</i><br>'.JText::_('Comment: ').$comment->comment_text.'<br>';
		}//end of foreach
		
		$comments .= '</div>';
	} // End of if(count($commentsresult))
		
		
		$comments .= '</div>';
		
		

		
		$comments .= '<div id="commentssubmittable'.$pageclass_sfx.'">';
		
		$user =& JFactory::getUser();
		//$this->assignRef('thestudy',$this->studydetails->study_id);
		$comment_access = $params->get('comment_access');
		$comment_user = $user->usertype;
		if (!$comment_user) { $comment_user = 0;}
		if ($comment_access > $comment_user){$comments .= '<div id="register'.$pageclass_sfx.'"><strong><br />'.JText::_('You must be registered to post comments').'</strong></div>';}else{
		$comments .= '<div id="register'.$pageclass_sfx.'">';
		if ($user->name){$full_name = $user->name; } else {$full_name = ''; } 
		if ($user->email) {$user_email = $user->email;} else {$user_email = '';}
		
		$comments .= '<form action="index.php" method="post">
		<div id="commentsheader'.$pageclass_sfx.'">'.JText::_('Post a Comment').'</div><br />
		'.JText::_('First & Last Name: ').'<br /><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="'.$full_name.'" /><br />
		'.JText::_('Email (Not displayed): ').'<br /><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="'.$user->email.'" /><br />
		'.JText::_('Comment: ').'<br /><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea><br /><br />';

		if ($params->get('use_captcha') == 1) { 
		
		// Begin captcha . Thanks OSTWigits 
		//Must be installed. Here we check that
		if (JPluginHelper::importPlugin('system', 'captcha'))
			{ 								
				$comments .= JText::_('Enter the text in the picture').'&nbsp
				<input name="word" type="text" id="word" value="" style="vertical-align:middle" size="10">&nbsp;
				<img src="'.JURI::base().'index.php?option=com_biblestudy&view=studydetails&controller=studydetails&task=displayimg">
				<br />';
			} 
			else 
			{ 
				$comments .= JText::_('Captcha plugin not installed. Please inform site administrator'); 
			} //end of check for OSTWigit plugin							
		
			} // end of if for use of captcha
		
		$comments .=  '<br />
		<input type="hidden" name="study_id" id="study_id" value="'.$row->id.'" />
		<input type="hidden" name="task" value="comment" />
		<input type="hidden" name="option" value="com_biblestudy" />
		<input type="hidden" name="published" id="published" value="'.$params->get('comment_publish').'"  />
		<input type="hidden" name="view" value="studydetails" />
		<input type="hidden" name="controller" value="studydetails" />
		<input type="hidden" name="comment_date" id="comment_date" value="'.date('Y-m-d H:i:s').'"  />
		<input type="hidden" name="study_detail_id" id="study_detail_id" value="'.$row->id.'"  />
		<input type="hidden" name="detailsitemid" id="detailsitemid" value="'.$params->get('detailsitemid').'" />
		<input type="submit" class="button" id="button" value="Submit"  />
		</form></div>';
		} //End of if $comment_access < $comment_user
		//} //End of show_comments on for submit form
		$comments .= '</div>';
        
	return $comments;
}