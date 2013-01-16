<?php
/**
 * Default CommonetForm
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
?>
<script type="text/javascript" language="JavaScript">
    function HideContent(d) {
        document.getElementById(d).style.display = "none";
    }
    function ShowContent(d) {
        document.getElementById(d).style.display = "block";
    }
    function ReverseDisplay(d) {
        if (document.getElementById(d).style.display == "none") {
            document.getElementById(d).style.display = "block";
        }
        else {
            document.getElementById(d).style.display = "none";
        }
    }
</script>
<?php
$commentjava = "javascript:ReverseDisplay('comments')";

switch ($this->item->params->get('link_comments', 0))
{
	case 0:
		echo '<strong><a class="heading' . $this->item->params->get('pageclass_sfx') . '" href="' . $commentjava . '">>>'
			. JText::_('JBS_CMT_SHOW_HIDE_COMMENTS') . '<<</a></strong>';
		?>
        <div id="comments" style="display:none;">
            <br/>
		<?php
		break;
	case 1:
		echo '<div id="comments">';
		break;
}
?>
<div id="commentstable">
<table class="table table-striped bslisttable" border="0">
    <thead>
    <tr class="lastrow">
        <th id="commentshead" class="row1col1">
			<?php echo JText::_('JBS_CMN_COMMENTS'); ?>
        </th>
    </tr>
    </thead>
<?php
$input = new JInput;

if (!$this->item->id)
{
	$this->item->id = $input->get('id', '', 'int');
}
// ToDo look like this should not be in this file. need to move this out to the model, TOM BCC
$db    = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select('c.*')->from('#__bsms_comments AS c')->where('c.published = 1')->where('c.study_id = ' . $this->item->id)->order('c.comment_date asc');
$db->setQuery($query);
$comments = $db->loadObjectList();

if (!count($comments))
{
	?>
    <tr>
        <td><?php echo JText::_('JBS_STY_NO_COMMENT') ?></td>
    </tr>
		</table>
			<?php
}
else
{
	foreach ($comments as $comment)
	{

		$comment_date_display = JHTML::_('date', $comment->comment_date, JText::_('DATE_FORMAT_LC3'));
		?><tbody>
                    <tr>
                        <td><strong><?php echo $comment->full_name ?></strong> <i>
                            - <?php echo $comment_date_display ?></i>
                        </td>
                    </tr>
    <tr>
        <td><?php echo JText::_('JBS_CMN_COMMENT') . ': ' . $comment->comment_text ?></td>
    </tr>
    <tr>
        <td>
            <hr/>
        </td>
    </tr>
		<?php
	} // End of foreach
	?>
</td>
</tr>
		</tbody>
			</table>
	<?php
}
?>
<?php
// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
$allow          = 0;
$user           = JFactory::getUser();
$groups         = $user->getAuthorisedViewLevels();
$show_comments  = $this->item->params->get('show_comments');
$comment_access = $this->item->params->get('comment_access');

if (in_array($show_comments, $groups))
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
    <table class="table table-striped" id="commentssubmittable" border="0">
		<?php
		if ($allow < 10)
		{
			?>
            <tr>
                <td>
                    <strong><?php echo JText::_('JBS_CMT_REGISTER_TO_POST_COMMENTS') ?></strong>
                </td>
            </tr>
			<?php
		}
		if ($allow >= 10)
		{
			?>
            <tr>
                <td>
					<?php
					if ($user->name)
					{
						$full_name = $user->name;
					}
					else
					{
						$full_name = '';
					}
					if ($user->email)
					{
						$user_email = $user->email;
					}
					else
					{
						$user_email = '';
					}
					?><strong>
					<?php echo JText::_('JBS_CMT_POST_COMMENT') ?>
                </strong>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo JText::_('JBS_CMT_FULL_NAME') ?>
                </td>
                <td>
                    <input class="text_area" size="50" type="text" name="full_name" id="full_name"
                           value="<?php echo $full_name ?>"/>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo JText::_('JBS_CMT_EMAIL') ?>
                </td>
                <td>
                    <input class="text_area" type="text" size="50" name="user_email" id="user_email"
                           value="<?php echo $user->email ?>"/>
                </td>
            </tr>
            <tr>
                <td>
					<?php echo JText::_('JBS_CMN_COMMENT') ?>:
                </td>
                <td>
                    <textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text"
                              id="comment_text">
                    </textarea>
                </td>
            </tr>
            <tr>
                <td>
                </td>
                <td>
					<?php
					if ($this->item->params->get('use_captcha') > 0)
					{
						// Begin captcha
						?>
                        <script language="javascript" type="text/javascript">
                            var RecaptchaOptions = {
                                theme:'white'
                            };
                        </script>
						<?php
						require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/captcha/recaptchalib.php';

						// You got this from the signup page
						$publickey = $this->item->params->get('public_key');

						if ($this->item->params->get('public_key'))
						{
							echo recaptcha_get_html($publickey);
						}
						else
						{
							echo JText::_('JBS_CMN_NO_KEY');
						}
					}
					?>
                </td>
            </tr>
                        <tr>
                            <td>

                                <input type="hidden" name="study_id" id="study_id"
                                       value="<?php echo $this->item->id ?>"/>
            <input type="hidden" name="task" value="comment"/>
            <input type="hidden" name="option" value="com_biblestudy"/>
            <input type="hidden" name="published" id="published"
                   value="<?php echo $this->item->params->get('comment_publish') ?>"/>
            <input type="hidden" name="view" value="sermon"/>

            <input type="hidden" name="comment_date" id="comment_date"
                   value="<?php echo date('Y-m-d H:i:s') ?>"/>
            <input type="hidden" name="study_detail_id" id="study_detail_id"
                   value="<?php echo $this->item->id ?>"/>

            <input type="submit" class="button" id="button" value="Submit"/>
			<?php
		} // End of if $allow > 10
		?>
    </td>
    </tr>
    </table>
</form>
	<?php
} // End if $allow > 9
?>
</div>
</div>