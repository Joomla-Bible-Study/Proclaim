<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
global $mainframe, $option;
//$params = &JComponentHelper::getParams($option);
$params =& $mainframe->getPageParameters();
$user =& JFactory::getUser();
$entry_user = $user->get('gid');
$entry_access = $this->params->get('entry_access');
$allow_entry = $this->params->get('allow_entry_study');
if ($allow_entry > 0) {
if ($entry_access <= $entry_user){ 
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	} else {
		submitform( pressbutton );
	}
}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Comments Details' ); ?></legend>
<?php $editor =& JFactory::getEditor();?>
		
    <table class="admintable">
    <tr><div>
	<button type="button" onclick="submitbutton('save')">
		<?php echo JText::_('Save') ?>
	</button>
	<button type="button" onclick="submitbutton('cancel')">
		<?php echo JText::_('Cancel') ?>
	</button>
</div></tr>
      <tr> 
        <td class="key"><?php echo JText::_( 'Published' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Create Date YYYY-MM-DD' ); ?></td>
        <td>
        <?php if (!$this->commentsedit->id) 
		{
			echo JHTML::_('calendar', $this->commentsedit->comment_date, 'comment_date', 'comment_date'); 
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d', strtotime($this->commentsedit->comment_date)), 'comment_date', 'comment_date'); 
        }?>
		
        </td>
		</tr>

      <tr> 
        <td class="key"><?php echo JText::_( 'Comment' );?></td>
        <td >
        
        <table width="300" border="0" cellspacing="1" cellpadding="1">
        <tr><td><?php echo JText::_( 'Study: '); echo $this->lists['studies'];?></td></tr>
        
            <tr>
              <td  ><?php echo JText::_('Full Name: ');?><input class="text_area" type="text" name="full_name" id="full_name" size="50" maxlength="50" value="<?php echo $this->commentsedit->full_name;?>" /></td>
            </tr>
           
			  <td  ><?php echo JText::_('Email: ')?> <input class="text_area" type="text" name="user_email" id="user_email" size="100" maxlength="100" value="<?php echo $this->commentsedit->user_email;?>" /></td>
            </tr>
            	<td><textarea class="text_area" name="comment_text" cols="150" rows="4" id="comment_text" ><?php echo $this->commentsedit->comment_text;?></textarea>
            </td>
                </tr>
            </tr>
          </table>
          </td>
      </tr>
      </td>
      </tr>
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->commentsedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="commentsedit" />
</form>
<?php } // End of checking to see if allowed
} // End of if authorized
else { echo 'You are not authorized to view this page';}