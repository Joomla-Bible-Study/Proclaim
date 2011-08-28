<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $mainframe =& JFactory::getApplication();

$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$teacher = $this->teacher;
$admin_params = $this->admin_params;
$params = $this->params;

$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');
include_once($path1.'teacher.php');

$t = JRequest::getVar('t', 1,'get', 'int');

if (!$t) {
	$t = 1;
}
$t = $this->params->get('teachertemplateid');
if (!$t) {
	$t = JRequest::getVar('t',1,'get','int');
}
$studieslisttemplateid = $this->params->get('studieslisttemplateid');
if (!$studieslisttemplateid) {
	$studieslisttemplateid = JRequest::getVar('t',1,'get','int');
}
if (!$teacher->teacher_image) {
	$image->path = $teacher->image; $image->height = $teacher->imageh; $image->width = $teacher->imagew;
}
else
{
	//		if ($teacher->teacher_image && !$admin_params->get('teachers_imagefolder')) { $i_path = 'images/stories/'.$teacher->teacher_image; }
	//		if ($teacher->teacher_image && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder/').$teacher->teacher_image;}
	if ($teacher->teacher_image) {
		$i_path = 'images/stories/'.$teacher->teacher_image;
	}
	$image = getImage($i_path);
}

?>
<div id="biblestudy" class="noRefTagger">



<?php
if (!$teacher->teacher_image) {
	$image->path = $teacher->image; $image->height = $teacher->imageh; $image->width = $teacher->imagew;
}
else
{
	//		if ($teacher->teacher_image && !$admin_params->get('teachers_imagefolder')) { $i_path = 'images/stories/'.$teacher->teacher_image; }
	//		if ($teacher->teacher_image && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder/').$teacher->teacher_image;}
	if ($teacher->teacher_image) {
		$i_path = 'images/stories/'.$teacher->teacher_image;
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

	echo '<table><tr><td id="bsmsteacherstudyfooter"><a href="'.JRoute::_('index.php?option=com_biblestudy&view=studieslist&filter_teacher='.$teacher->id).'">'.JText::_('JBS_TCH_MORE_FROM_THIS_TEACHER').' --></a></td><tr></table>';
	?>
		<tr>
			<td align="center" colspan="0" class="bsm_teacherfooter"><a
				href="index.php?option=com_biblestudy&view=teacherlist<?php echo '&t='.$t;?>"><?php echo '<--'.JText::_('JBS_TCH_RETURN_TEACHER_LIST');?>
			</a>
	
	</table>
</div>
