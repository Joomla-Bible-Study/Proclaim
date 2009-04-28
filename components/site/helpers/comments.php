<?php defined('_JEXEC') or die();

function getComments($params, $row)
{
		$database	= & JFactory::getDBO();
		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1'
				.' AND c.study_id = '.$row->id.' ORDER BY c.comment_date ASC';
		$database->setQuery($query);
		$commentsresult = $database->loadObjectList();
		
		$comments = '<strong><a class="heading" href="javascript:ReverseDisplay('comments')">>>'.JText::_('Show/Hide Comments').'<<</a>
		<div id="comments" style="display:none;"></strong>'

$comments .= '
		<div class="commentsheader'.$params->get('pageclass_sfx').'">
		'.JText::_('Comments');

	if (count($commentsresult)) {
 
		foreach ($commentsresult as $comment){

		$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') , '$offset' );
		
		$comments .= '<strong>'.$comment->full_name.'</strong> <i>'.$comment_date_display.'</i><br>'.JText::_('Comment: ').$comment->comment_text.'<br>';
		}//end of foreach
	} // End of if(count($commentsresult))
		
		
		$comments .= '</div>';
		
		

		<?php if ($this->params->get('show_comments') > 0) {?>
		<tr><td><?php //Row for submit form for comments?>
		
		<?php $user =& JFactory::getUser();
		$this->assignRef('thestudy',$this->studydetails->study_id);
		$comment_access = $this->params->get('comment_access');
		$comment_user = $user->usertype;
		if (!$comment_user) { $comment_user = 0;}
		//$comment_access = $this->params->get('comment_access');
		//dump ($comment_access, 'Comment Access'); dump ($comment_user, 'Comment User');
		if ($comment_access > $comment_user){echo '<strong><br />'.JText::_('You must be registered to post comments').'</strong>';}else{
		if ($user->name){$full_name = $user->name; } else {$full_name = ''; } ?>
		<?php if ($user->email) {$user_email = $user->email;} else {$user_email = '';}?>
		
		<form action="index.php" method="post">
		<table><tr><td><strong><?php echo JText::_('Post a Comment');?></strong></td></tr>
		<tr><td ><?php echo JText::_('First & Last Name: ');?></td><td><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="<?php echo $full_name;?>" /></td></tr>
		<tr><td><?php echo JText::_('Email (Not displayed): ');?></td><td><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="<?php echo $user->email;?>" /></td></tr>
		<tr><td><?php echo JText::_('Comment: ');?></td><td><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea></td></tr>
		<?php if ($this->params->get('use_captcha') == 1) { ?>
		<tr><td><?php // Beginning of row for captcha
		// Begin captcha . Thanks OSTWigits 
		//Must be installed. Here we check that
		if (JPluginHelper::importPlugin('system', 'captcha'))
			{ 								
				echo JText::_('Enter the text in the picture').'&nbsp;'?>
				<input name="word" type="text" id="word" value="" style="vertical-align:middle" size="10">&nbsp;
				<img src=<?php echo JURI::base().'index.php?option=com_biblestudy&view=studydetails&controller=studydetails&task=displayimg';?>>
				<br />
			<?php } else { echo JText::_('Captcha plugin not installed. Please inform site administrator'); } //end of check for OSTWigit plugin?>							
		</td></td><?php //end of row for captcha?>
		<?php
			} // end of if for use of captcha
		?>
		</table><?php //End of Form table?>
		<input type="hidden" name="study_id" id="study_id" value="<?php echo $this->studydetails->id;?>" />
		<input type="hidden" name="task" value="comment" />
		<input type="hidden" name="option" value="com_biblestudy" />
		<input type="hidden" name="published" id="published" value="<?php echo $this->params->get('comment_publish');?>"  />
		<input type="hidden" name="view" value="studydetails" />
		<input type="hidden" name="controller" value="studydetails" />
		<input type="hidden" name="comment_date" id="comment_date" value="<?php echo date('Y-m-d H:i:s');?>"  />
		<input type="hidden" name="study_detail_id" id="study_detail_id" value="<?php echo $this->studydetails->id;?>"  />
		<input type="submit" class="button" id="button" value="Submit"  />
		</form>
		<?php } //End of if $comment_access < $comment_user?>
		</td></tr><?php //End of row for submit form?>
		<?php } //End of show_comments on for submit form?>
		</div>';
        
	return $comments;
}