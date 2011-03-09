<script type="text/javascript" language="JavaScript">
function HideContent(d) {
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
document.getElementById(d).style.display = "block";
}
function ReverseDisplay(d) {
if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
else { document.getElementById(d).style.display = "none"; }
}
</script>

<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined('_JEXEC') or die('Restricted access'); 
$commentjava = "javascript:ReverseDisplay('comments')";	
 switch ($this->params->get('link_comments',0))
        {
            case 0:
            echo '<strong><a class="heading'.$pageclass_sfx.'" href="'.$commentjava.'">>>'
            .JText::_('JBS_CMT_SHOW_HIDE_COMMENTS').'<<</a></strong>';
            ?> <div id="comments" style="display:none;"><br /> <?php
            break;
            
            case 1:
            ?><div id="comments"><?php
            break;
        }
?>

<div id="commentstable" >		<table id="bslisttable" cellspacing="0" border="0"><thead><tr class="lastrow"><th id="commentshead" class="row1col1">
		<?php echo JText::_('JBS_CMN_COMMENTS');?></th></tr></thead>
<?php
	
       
        $db = JFactory::getDBO();
        $query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1 AND c.study_id = '.$this->studydetails->id.' ORDER BY c.comment_date ASC';
				
		$db->setQuery($query);
		$comments = $db->loadObjectList();
  if (!count($comments)) {echo '<tr><td>'.JText::_('JBS_CMN_NO_COMMENT').'</td></tr></table>';}
  else
  {
        foreach ($comments as $comment){

		$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') );
		echo '<tbody>';
		echo '<tr><td><strong>'.$comment->full_name.'</strong> <i> - '.$comment_date_display.'</i></td></tr><tr><td>'.JText::_('JBS_CMN_COMMENT').': '.$comment->comment_text.'</td></tr><tr><td><hr /></td></tr>';
		}//end of foreach

		echo '</td></tr></tbody></table>';
  }
?>

<?php

//check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $allow = 0;
        $user = JFactory::getUser();
        $groups	= $user->getAuthorisedViewLevels(); 
        $show_comments = $this->params->get('show_comments');
        $comment_access = $this->params->get('comment_access');
        
            
               if (in_array($show_comments,$groups))
               {
                   $allow = 9;
               } 
               if (in_array($comment_access, $groups))
               {
                    $allow = 10;
               }
	        

        if ($allow > 9)
        {
            ?>
        <form action="index.php" method="post">
		<table id="commentssubmittable" border="0"><tr><td>
<?php
		if ($allow < 10){echo '<tr><td><strong>'.JText::_('JBS_CMT_REGISTER_TO_POST_COMMENTS').'</strong></td></tr>';}
        if ($allow >= 10)
        {
		echo '<tr><td>';
		if ($user->name){$full_name = $user->name; } else {$full_name = ''; }
		if ($user->email) {$user_email = $user->email;} else {$user_email = '';}

		echo '<strong>'
		.JText::_('JBS_CMT_POST_COMMENT').'</strong></td></tr>
                <tr><td>'.JText::_('JBS_CMT_FULL_NAME').
		'</td><td><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="'.$full_name.'" /></td></tr>
                <tr><td>'.JText::_('JBS_CMT_EMAIL').'</td><td><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="'.$user->email.'" /></td></tr>
                <tr><td>'.JText::_('JBS_CMN_COMMENT').':</td>';
		
		echo '<td><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea></td></tr>';


		echo  '<tr><td></td><td>';
		if ($this->params->get('use_captcha') > 0) 
        {

		  // Begin captcha ?>
<script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'white'
 };
 </script>
 <?php
        require_once(JPATH_SITE .DS. 'components' .DS. 'com_biblestudy' .DS. 'assets' .DS. 'captcha' .DS. 'recaptchalib.php');
        $publickey = $this->params->get('public_key'); // you got this from the signup page
        echo recaptcha_get_html($publickey);
		

		} // end of if for use of captcha
		//dump ($params->get('comment_publish'));
        echo '</td></tr><tr><td>
        
		<input type="hidden" name="study_id" id="study_id" value="'.$this->studydetails->id.'" />
		<input type="hidden" name="task" value="comment" />
		<input type="hidden" name="option" value="com_biblestudy" />
		<input type="hidden" name="published" id="published" value="'.$this->params->get('comment_publish').'"  />
		<input type="hidden" name="view" value="studydetails" />
		
		<input type="hidden" name="comment_date" id="comment_date" value="'.date('Y-m-d H:i:s').'"  />
		<input type="hidden" name="study_detail_id" id="study_detail_id" value="'.$this->studydetails->id.'"  />
		
		<input type="submit" class="button" id="button" value="Submit"  />';
		} //End of if $allow > 10
        ?>
</td></tr></table>
</form>
<?php

} //end if $allow > 9?>
</div>
</div>
