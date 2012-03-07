<?php
//No Direct Access
defined('_JEXEC') or die;


$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'teacher.php');
include_once($path1 . 'listing.php');



$admin_params = $this->admin_params;

$t = JRequest::getVar('t', 1, 'get', 'int');
if (!$t) {
    $t = 1;
}
$t = $this->params->get('teachertemplateid');
if (!$t) {
    $t = JRequest::getVar('t', 1, 'get', 'int');
}
$studieslisttemplateid = $this->params->get('studieslisttemplateid');


if (!$studieslisttemplateid) {
    $studieslisttemplateid = JRequest::getVar('t', 1, 'get', 'int');
}
?>
<div id="biblestudy" class="noRefTagger">
    <table id="bsm_teachertable" cellspacing="0">

        <tr>
            <td class="bsm_teacherthumbnail">
                <?php
                
                if ($item->title) {
                    $teacherdisplay = $this->item->teachername . ' - ' . $this->item->title;
                } else {
                    $teacherdisplay = $this->item->teachername;
                }
                ?>
                <img src="<?php echo JURI::base() . $this->item->image->path; ?>" width="<?php echo $this->item->image->width; ?>" height="<?php echo $this->item->image->height; ?>" border="1" alt="<?php echo $teacherdisplay; ?>" />
                

            </td>
            <td class="bsm_teachername">
                <table id="bsm_teachertable" cellspacing="0">
                    <tr><td class="bsm_teachername">
                            <?php echo $teacherdisplay; ?>
                        </td></tr>
                    <tr> <td class="bsm_teacherphone">
                            <?php echo $item->phone; ?></td></tr>
                    <tr><td class="bsm_teacheremail">
                            <?php
                            if ($this->item->email) {
                                if (!stristr($item->email, '@')) {
                                    ?>
                                    <a href="<?php echo $this->item->email; ?>"><?php echo JText::_('JBS_TCH_EMAIL_CONTACT'); ?></a>
                                <?php } else {
                                    ?>
                                    <a href=mailto:"<?php echo $this->item->email; ?>"><?php echo JText::_('JBS_TCH_EMAIL_CONTACT'); ?></a>
                                <?php
                                }
                            } //end if $item->email
                            ?>
                        </td></tr>
                    <tr><td class="bsm_teacherwebsite">
                            <?php if ($this->item->website) { ?>
                                <a href="<?php echo $this->item->website; ?>"><?php echo JText::_('JBS_TCH_WEBSITE'); ?></a>
<?php } ?>
                        </td></tr></table>
            </td>
        </tr>

<?php if ($item->information) { ?>

            <tr>
                <td class="bsm_teacherlong" colspan="2">
            <?php echo $this->item->information; ?>
                </td>
            </tr>
    <?php } // end of if for teacher->information ?>
    </table>
    <?php ?> <table id="bslisttable" cellspacing="0"><tr><td> <?php
    switch ($this->params->get('show_teacher_studies')) {
        case 1:
            ?>    <table  id="bsm_teachertable" cellspacing="0">
                            <tr class="titlerow"><td class="title" colspan="3"><?php echo $this->params->get('label_teacher'); ?></td></tr>

                            <tr class="bsm_studiestitlerow">
                                <td class="bsm_titletitle"> <?php echo JText::_('JBS_CMN_TITLE'); ?></td>
                                <td class="bsm_titlescripture"> <?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></td>
                                <td class="bsm_titledate"> <?php echo JText::_('JBS_CMN_STUDY_DATE'); ?></td>
                            </tr>
        <?php foreach ($this->teacherstudies as $study) { ?>
                                <tr>
                                    <td class="bsm_studylink"> <a href="index.php?option=com_biblestudy&view=sermon&id=<?php echo $study->id . '&t=' . $studieslisttemplateid; ?>"><?php echo $study->studytitle; ?></a></td>
                                    <td class="bsm_scripture"> <?php
                            if ($study->bookname) {
                                echo JText::_($study->bookname) . ' ' . $study->chapter_begin;
                            }
                            ?></td>
                                    <td class="bsm_date"> <?php $date = JHTML::_('date', $study->studydate, JText::_('DATE_FORMAT_LC'));
                            echo $date;
                            ?></td>
                                </tr>
                        <?php } // end of foreach ?>


                        </table><?php
                break;

            case 2:
                        ?>    <table id="bsm_teachertable" cellspacing="0">
                            <tr class="titlerow"><td class="title" colspan="3"><?php echo $this->params->get('label_teacher'); ?></td></tr></table><table id="bslisttable" cellspacing="0"><tr><td><?php
                            $headerCall = JView::loadHelper('header');
                            $header = getHeader($this->studies, $this->params, $this->admin_params, $this->template, $showheader = $this->params->get('use_headers_list'), $ismodule = 0);
                            echo $header;
                            $class1 = 'bsodd';
                            $class2 = 'bseven';
                            $oddeven = $class1;
                            foreach ($this->teacherstudies as $row) { //Run through each row of the data result from the model
                                if ($oddeven == $class1) { //Alternate the color background
                                    $oddeven = $class2;
                                } else {
                                    $oddeven = $class1;
                                }
                                $studies = getListing($row, $this->params, $oddeven, $admin_params, $this->template, $ismodule = 0);
                                echo $studies;
                                
                            }
                        ?></td></tr></table><?php
                    break;

                case 3:
                    
                $studies = getTeacherStudiesExp($this->item->id, $this->params, $admin_params, $this->template);
                    echo $studies;
                    break;
            }
                ?> </td></tr>

        <tr><td align="center" colspan="0"class="bsm_teacherfooter"><a href="index.php?option=com_biblestudy&view=teacher&t=<?php echo $t; ?>"><?php echo '<-- ' . JText::_('JBS_TCH_RETURN_TEACHER_LIST'); ?></a> <?php
if ($this->params->get('teacherlink', '1') > 0) {
    echo ' | <a href="index.php?option=com_biblestudy&view=sermons&filter_teacher=' . (int) $this->item->id . '&t=' . $t . '">' . JText::_('JBS_TCH_MORE_FROM_THIS_TEACHER') . ' --></a>';
}
?></td></tr></table>
</div>