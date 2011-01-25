<?php defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
function getComments($params, $row, $Itemid)
{
		
        $allow = 0;
        $admin = new JBSAdmin();
        $allow = $admin->commentsPermission($params);
        if (!$allow){$comments = ''; return $comments;}
        if ($allow > 9)
        {
            
       
		$database	= & JFactory::getDBO();
	//	$editor =& JFactory::getEditor();

		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1 AND c.study_id = '.$row->id.' ORDER BY c.comment_date ASC';
				//dump ($query, 'row');
		$database->setQuery($query);
		$commentsresult = $database->loadObjectList();
		$pageclass_sfx = $params->get('pageclass_sfx');
		$Itemid = JRequest::getInt('Itemid','1','get');
		$commentjava = "javascript:ReverseDisplay('comments')";
        
        switch ($params->get('link_comments',0))
        {
            case 0:
            $comments = '<strong><a class="heading'.$pageclass_sfx.'" href="'.$commentjava.'">>>'
            .JText::_('JBS_CMT_SHOW_HIDE_COMMENTS').'<<</a></strong>
            <div id="comments" style="display:none;"><br />';
            break;
            
            case 1:
            $comments = '<div id="comments">';
            break;
        }
		
if (count($commentsresult)) {

$comments .= '
		<table id="bslisttable" cellspacing="0"><thead><tr class="lastrow"><th id="commentshead" class="row1col1">
		'.JText::_('JBS_CMN_COMMENTS').'</th></tr></thead>';

		foreach ($commentsresult as $comment){

		$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') );
		$comments .= '<tbody>';
		$comments .= '<tr><td><strong>'.$comment->full_name.'</strong> <i> - '.$comment_date_display.'</i></td></tr><tr><td>'.JText::_('JBS_CMN_COMMENT').': '.$comment->comment_text.'</td></tr><tr><td><hr /></td></tr>';
		}//end of foreach

		$comments .= '</td></tr></tbody></table>';
	} // End of if(count($commentsresult))



		
		

	return $comments;
	} //end else if $allow > 9
}
