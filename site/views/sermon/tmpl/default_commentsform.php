<?php
/**
 * Default CommonetForm
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
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
$commentjava = "javascript:ReverseDisplay('JBScomments')";

//php code
JPluginHelper::importPlugin('captcha');
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onInit', 'dynamic_recaptcha_1');

switch ($this->item->params->get('link_comments', 0))
{
	case 0:
		echo '<strong><a class="heading' . $this->item->params->get('pageclass_sfx') . '" href="' . $commentjava . '">>>'
			. JText::_('JBS_CMT_SHOW_HIDE_COMMENTS') . '<<</a></strong>';
		?>
        <div id="JBScomments" style="display:none;">
            <br/>
		<?php
		break;
	case 1:
		echo '<div id="JBScomments">';
		break;
}
?>
<div id="commentstable">
<div class="container-fluid">

    <div class="row-fluid">
        <div class="span12">
			<h2><?php echo JText::_('JBS_CMN_COMMENTS'); ?></h2>
        </div>
    </div>

<?php
$input = new JInput;

if (!$this->item->id)
{
	$this->item->id = $input->get('id', '', 'int');
}


if (!count($this->comments))
{
	?>
    <div class="row-fluid">
        <div class="span12"><?php echo JText::_('JBS_STY_NO_COMMENT') ?></div>
    </div>
		</div>
			<?php
}
else
{
	foreach ($this->comments as $comment)
	{

		$comment_date_display = JHtml::_('date', $comment->comment_date, JText::_('DATE_FORMAT_LC3'));
		?>
                    <div class="row-fluid">
                        <div class="span6"><strong><?php echo $comment->full_name ?></strong> <i>
                            - <?php echo $comment_date_display ?></i>
                        </div>
                    </div>
    <div class="row-fluid">
        <div class="span12"><?php echo JText::_('JBS_CMN_COMMENT') . ': ' . $comment->comment_text ?></div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <hr />
        </div>
    </div>
		<?php
	} // End of foreach
	?>

</div>
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
<div class="container-fluid">
<form action="index.php" method="post">

    <div class="row-fluid">
		<?php
		if ($allow < 10)
		{
			?>

                    <strong><div class="span12"><?php echo JText::_('JBS_CMT_REGISTER_TO_POST_COMMENTS') ?></div></strong>

			<?php
		}
		if ($allow >= 10)
		{
			?>
            <div class="span12">
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
                </div>
            <div class="row-fluid">
                <div class="span2">
					<?php echo JText::_('JBS_CMT_FULL_NAME') ?>
                </div>
                <div class="span7">
                    <input class="text_area" size="50" type="text" name="full_name" id="full_name"
                           value="<?php echo $full_name ?>"/>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span2">
					<?php echo JText::_('JBS_CMT_EMAIL') ?>
                </div>
                <div class="span7">
                    <input class="text_area" type="text"  name="user_email" id="user_email"
                           value="<?php echo $user->email ?>"/>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span2">
					<?php echo JText::_('JBS_CMN_COMMENT') ?>:
                </div>
                <div class="span7">
                    <textarea class="text_area" cols="20" rows="4" name="comment_text"
                              id="comment_text">
                    </textarea>
                </div>
            </div>
            <div class="row-fluid">
               <div class="span12">
					<?php
					if ($this->item->params->get('use_captcha') > 0)
					{
						if ($this->item->params->get('public_key'))
						{
							echo '<div id="dynamic_recaptcha_1"></div>';
						}
						else
						{
							echo JText::_('JBS_CMN_NO_KEY');
						}
					}
					?>
                </div>
            </div>
            <div class="row-fluid">
            <div class="span12">
<?php $input = new JInput(); $t = $input->getString('t'); ?>
                <input type="hidden" name="study_id" id="study_id" value="<?php echo $this->item->id ?>"/>
                <input type="hidden" name="t" value="<?php echo $t;?>">
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
            </div>
        </div>
    </div>
</form>
</div>
	<?php
} // End if $allow > 9
?>
</div>

