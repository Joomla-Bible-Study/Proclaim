<?php
/**
 * @author Tom Fuller
 * @copyright 2011
 */
//No Direct Access
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
        if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
        else { document.getElementById(d).style.display = "none"; }
    }
</script>
<?php
$commentjava = "javascript:ReverseDisplay('comments')";
switch ($this->params->get('link_comments', 0)) {
    case 0:
        echo '<strong><a class="heading' . $this->params->get('pageclass_sfx') . '" href="' . $commentjava . '">>>'
        . JText::_('JBS_CMT_SHOW_HIDE_COMMENTS') . '<<</a></strong>';
        ?> <div id="comments" style="display:none;"><br /> <?php
        break;

    case 1:
        ?><div id="comments"><?php
        break;
}
?>

        <div id="commentstable" ><table id="bslisttable" cellspacing="0" border="0"><thead><tr class="lastrow"><th id="commentshead" class="row1col1">
                            <?php echo JText::_('JBS_CMN_COMMENTS'); ?>
                        </th></tr></thead>
                <?php
                if (!$this->study->id){$this->study->id = JRequest::getInt('id');} 
                $db = JFactory::getDBO();
                $query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1 AND c.study_id = ' . $this->study->id . ' ORDER BY c.comment_date ASC';

                $db->setQuery($query);
                $comments = $db->loadObjectList();
                if (!count($comments)) {
                    ?><tr><td><?php echo JText::_('JBS_STY_NO_COMMENT') ?></td></tr></table><?php
                    } else {
                        foreach ($comments as $comment) {

                            $comment_date_display = JHTML::_('date', $comment->comment_date, JText::_('DATE_FORMAT_LC3'));
                        ?><tbody>
                        <tr><td><strong><?php echo $comment->full_name ?></strong> <i> - <?php $comment_date_display ?></i></td></tr><tr><td><?php echo JText::_('JBS_CMN_COMMENT') . ': ' . $comment->comment_text ?></td></tr><tr><td><hr /></td></tr>
                    <?php }//end of foreach
                    ?>
                    </td></tr></tbody></table>
                <?php
            }
            ?>

            <?php
//check permissions for this view by running through the records and removing those the user doesn't have permission to see
            $allow = 0;
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $show_comments = $this->params->get('show_comments');
            $comment_access = $this->params->get('comment_access');


            if (in_array($show_comments, $groups)) {
                $allow = 9;
            }
            if (in_array($comment_access, $groups)) {
                $allow = 10;
            }


            if ($allow > 9) {
                ?>
                <form action="index.php" method="post">
                    <table id="commentssubmittable" border="0"><tr><td>
                                <?php
                                if ($allow < 10) {
                                    ?><tr><td><strong><?php echo JText::_('JBS_CMT_REGISTER_TO_POST_COMMENTS') ?></strong></td></tr>
                                    <?php
                                }
                                if ($allow >= 10) {
                                    echo '<tr><td>';
                                    if ($user->name) {
                                        $full_name = $user->name;
                                    } else {
                                        $full_name = '';
                                    }
                                    if ($user->email) {
                                        $user_email = $user->email;
                                    } else {
                                        $user_email = '';
                                    }
                                    ?><strong>
                                <?php echo JText::_('JBS_CMT_POST_COMMENT') ?>
                            </strong></td></tr><tr><td>
                                    <?php echo JText::_('JBS_CMT_FULL_NAME') ?>
                                </td><td><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="<?php echo $full_name ?>" /></td></tr>
                            <tr><td><?php echo JText::_('JBS_CMT_EMAIL') ?>
                                </td><td><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="<?php echo $user->email ?>" /></td></tr>
                            <tr><td><?php echo JText::_('JBS_CMN_COMMENT') ?>:</td>

                                <td><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea></td></tr>

                            <tr><td></td><td><?php
                            if ($this->params->get('use_captcha') > 0) {

                                // Begin captcha
                                        ?>
                                        <script type="text/javascript">
                                            var RecaptchaOptions = {
                                                theme : 'white'
                                            };
                                        </script>
                                        <?php
                                        require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'recaptchalib.php');
                                        $publickey = $this->params->get('public_key'); // you got this from the signup page
                                        if ($this->params->get('public_key')) {
                                            echo recaptcha_get_html($publickey);
                                        } else {
                                            echo JText::_('JBS_CMN_NO_KEY');
                                        }
                                    }
                                    ?> </td></tr><tr><td>

                                    <input type="hidden" name="study_id" id="study_id" value="<?php echo $this->studydetails->id ?>" />
                                    <input type="hidden" name="task" value="comment" />
                                    <input type="hidden" name="option" value="com_biblestudy" />
                                    <input type="hidden" name="published" id="published" value="<?php echo $this->params->get('comment_publish') ?>"  />
                                    <input type="hidden" name="view" value="sermon" />

                                    <input type="hidden" name="comment_date" id="comment_date" value="<?php echo date('Y-m-d H:i:s') ?>"  />
                                    <input type="hidden" name="study_detail_id" id="study_detail_id" value="<?php echo $this->studydetails->id ?>"  />

                                    <input type="submit" class="button" id="button" value="Submit"  />
                                <?php } //End of if $allow > 10
                                ?>
                            </td></tr></table>
                </form>
            <?php } //end if $allow > 9
            ?>
        
    </div>
</div>