<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
$admin = new JBSAdmin();
$allow = $admin->getPermission();
if (!$allow)
{
    return JError::raiseError('403', JText::_('JBS_CMN_ACCESS_FORBIDDEN'));
}

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
		<legend><?php echo JText::_( 'JBS_CMT_COMMENTS_DETAILS' ); ?></legend>
<?php $editor =& JFactory::getEditor();?>

    <table class="admintable">
    <tr>
      <td colspan="0">
	    <button type="button" onclick="submitbutton('save')">
		  <?php echo JText::_('JBS_CMN_SAVE') ?>
	    </button>
	    <button type="button" onclick="submitbutton('cancel')">
		  <?php echo JText::_('JBS_CMN_CANCEL') ?>
	    </button>
      </td>
    </tr>
    <tr>
      <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?></td>
      <td > <?php echo $this->lists['published'];?></td>
    </tr>
    <tr>
      <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_CREATE_DATE_YMD_HMS' ); ?></td>
      <td>
        <?php if (!$this->commentsedit->id)
		{
			echo JHTML::_('calendar', $this->commentsedit->comment_date, 'comment_date', 'comment_date');
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d', strtotime($this->commentsedit->comment_date)), 'comment_date', 'comment_date');
        }?></td>
    </tr>
    <tr>
      <td><?php echo JText::_( 'JBS_CMN_STUDY');?></td>
	  <td><?php echo $this->lists['studies'];?></td>
    </tr>
    <tr>
      <td><?php echo JText::_('JBS_CMT_FULL_NAME');?></td>
      <td><input class="text_area" type="text" name="full_name" id="full_name" size="70" maxlength="50" value="<?php echo $this->commentsedit->full_name;?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_('JBS_CMT_EMAIL')?></td>
      <td><input class="text_area" type="text" name="user_email" id="user_email" size="70" maxlength="100" value="<?php echo $this->commentsedit->user_email;?>" /></td>
    </tr>
    <tr>
      <td><?php echo JText::_( 'JBS_CMN_COMMENT' ).':';?></td>
      <td><textarea class="text_area" name="comment_text" cols="53" rows="4" id="comment_text" ><?php echo $this->commentsedit->comment_text;?></textarea></td>
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
<?php //} // End of checking to see if allowed
//} // End of if authorized
//