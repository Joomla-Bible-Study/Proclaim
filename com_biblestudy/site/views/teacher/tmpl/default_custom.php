<?php
//No Direct Access
defined('_JEXEC') or die;

$mainframe = & JFactory::getApplication();

$pathway = & $mainframe->getPathWay();
$uri = & JFactory::getURI();
$database = & JFactory::getDBO();
$teacher = $this->teacher;
$admin_params = $this->admin_params;
$params = $this->params;

$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'image.php');
include_once($path1 . 'teacher.php');

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
if (!$teacher->teacher_image) {
    $image->path = $teacher->image;
    $image->height = $teacher->imageh;
    $image->width = $teacher->imagew;
} else {
    if ($teacher->teacher_image) {
        $i_path = 'images' . $teacher->teacher_image;
    }
    $image = getImage($i_path);
}
?>
<div id="biblestudy" class="noRefTagger">

        <?php
        if (!$teacher->teacher_image) {
            $image->path = $teacher->image;
            $image->height = $teacher->imageh;
            $image->width = $teacher->imagew;
        } else {
            if ($teacher->teacher_image) {
                $i_path = 'images' . $teacher->teacher_image;
            }
            $image = getImage($i_path);
        }
        ?>
    <table id="bslisttable" cellspacing="0">
<?php
$listing = getTeacherDetailsExp($teacher, $params, $this->template, $admin_params);
echo $listing;
if ($this->params->get('show_teacher_studies') > 0) {
    $studies = getTeacherStudiesExp($teacher->id, $params, $admin_params, $this->template);
    echo $studies;
}

echo '<table><tr><td id="bsmsteacherstudyfooter"><a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermons&filter_teacher=' . $teacher->id) . '">' . JText::_('JBS_TCH_MORE_FROM_THIS_TEACHER') . ' --></a></td><tr></table>';
?>
        <tr><td align="center" colspan="0"class="bsm_teacherfooter"><a href="index.php?option=com_biblestudy&view=teacher<?php echo '&t=' . $t; ?>"><?php echo '<--' . JText::_('JBS_TCH_RETURN_TEACHER_LIST'); ?></a>
    </table>
</div>