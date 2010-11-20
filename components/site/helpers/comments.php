<?php defined('_JEXEC') or die();

function getComments($params, $row, $Itemid)
{
		$user =& JFactory::getUser();
		$comment_access = $params->get('comment_access');
		$comment_user = $user->usertype;
		if (!$comment_user) { $comment_user = 0;} $comm = $params->get('show_comments'); //dump ($comment_user);
		$yes = 0;
		switch ($comm)
		{
			case 2:
				$yes = 0;
			break;

			case 1:
				if ($comment_user >= $comm)
				{
					$yes = 1;
				}
			break;

			case 0:
				$yes = 1;
			break;
		}

		if ($yes == 0)
		{
			$comments = '';
			return $comments;
		}
		/*if ($params->get('show_comments') >= $comment_user || $params->get('show_comments') == 2)
		{
			$comments = '';
			return $comments;
		}*/
		else
		{

		$database	= & JFactory::getDBO();
		$editor =& JFactory::getEditor();

		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1 AND c.study_id = '.$row->id.' ORDER BY c.comment_date ASC';
				//dump ($query, 'row');
		$database->setQuery($query);
		$commentsresult = $database->loadObjectList();
		$pageclass_sfx = $params->get('pageclass_sfx');
		$Itemid = JRequest::getVar('Itemid');
		$commentjava = "javascript:ReverseDisplay('comments')";
        
        switch ($params->get('link_comments',0))
        {
            case 0:
            $comments = '<strong><a class="heading'.$pageclass_sfx.'" href="'.$commentjava.'">>>'
            .JText::_('JBS_CMT_SHOW_HIDE_COMMENTS').'<<</a></strong>
            <div id="comments" style="display:none;"><br />';
            break;
            
            case 1:
            echo $comments = '<div id="comments">';
            break;
        }
		
if (count($commentsresult)) {

$comments .= '
		<table id="bslisttable" cellspacing="0"><thead><tr class="lastrow"><th id="commentshead" class="row1col1">
		'.JText::_('JBS_CMN_COMMENTS').'</th></tr></thead>';

		foreach ($commentsresult as $comment){

		$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') , '$offset' );
		$comments .= '<tbody>';
		$comments .= '<tr><td><strong>'.$comment->full_name.'</strong> <i> - '.$comment_date_display.'</i></td></tr><tr><td>'.JText::_('JBS_CMN_COMMENT').': '.$comment->comment_text.'</td></tr><tr><td><hr /></td></tr>';
		}//end of foreach

		$comments .= '</td></tr></tbody></table>';
	} // End of if(count($commentsresult))






		$comments .= '<table id="commentssubmittable">';



		if ($comment_access > $comment_user){$comments .= '<tr><td><strong>'.JText::_('JBS_CMT_REGISTER_TO_POST_COMMENTS').'</strong></td></tr>';}else{
		$comments .= '<tr><td>';
		if ($user->name){$full_name = $user->name; } else {$full_name = ''; }
		if ($user->email) {$user_email = $user->email;} else {$user_email = '';}

		$comments .= '<form action="index.php" method="post"><strong>'
		.JText::_('JBS_CMT_POST_COMMENT').'</strong></td></tr>
                <tr><td>'.JText::_('JBS_CMT_FULL_NAME').
		'</td><td><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="'.$full_name.'" /></td></tr>
                <tr><td>'.JText::_('JBS_CMT_EMAIL').'</td><td><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="'.$user->email.'" /></td></tr>
                <tr><td>'.JText::_('JBS_CMN_COMMENT').':</td>';
		//$comments .= $editor->display('comment_text', 'comment_text', '100%', '400', '70', '15').'</td></tr></table>';
		$comments .= '<td><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea></td></tr>';
//dump ($params->get('use_captcha'), 'captch: ');
		if ($params->get('use_captcha') > 0) {

		// Begin captcha . Thanks OSTWigits
		//Must be installed. Here we check that
		if (JPluginHelper::importPlugin('system', 'captcha'))
			{
				$comments .= '<table><tr><td>'.JText::_('JBS_CMT_ENTER_CAPTCHA_TEXT').'&nbsp
				<input name="word" type="text" id="word" value="" style="vertical-align:middle" size="10">&nbsp;
				<img src="'.JURI::base().'index.php?option=com_biblestudy&view=studydetails&controller=studydetails&task=displayimg">
				</td></tr>';
			}
			else
			{
				$comments .= JText::_('JBS_CMT_CAPTCHA_NOT_INSTALLED');
			} //end of check for OSTWigit plugin

			} // end of if for use of captcha
		//dump ($params->get('comment_publish'));
		$comments .=  '<tr><td>
		<input type="hidden" name="study_id" id="study_id" value="'.$row->id.'" />
		<input type="hidden" name="task" value="comment" />
		<input type="hidden" name="option" value="com_biblestudy" />
		<input type="hidden" name="published" id="published" value="'.$params->get('comment_publish').'"  />
		<input type="hidden" name="view" value="studydetails" />
		<input type="hidden" name="controller" value="studydetails" />
		<input type="hidden" name="comment_date" id="comment_date" value="'.date('Y-m-d H:i:s').'"  />
		<input type="hidden" name="study_detail_id" id="study_detail_id" value="'.$row->id.'"  />
		<input type="hidden" name="Itemid" id="Itemid" value="'.$Itemid.'" />
		<input type="submit" class="button" id="button" value="Submit"  />
		</form>';
		} //End of if $comment_access < $comment_user
		//} //End of show_comments on for submit form
		$comments .= '</td></tr></table></div>';

	return $comments;
	} //end else if ($params->get('show_comments') < $comment_user)
}
