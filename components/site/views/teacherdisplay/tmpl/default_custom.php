<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php global $mainframe;

$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$teacher = $this->teacher;
$admin_params = $this->admin_params;
$params = $this->params;

$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');
include_once($path1.'teacher.php');

$templatemenuid = JRequest::getVar('templatemenuid', 1,'get', 'int');

if (!$templatemenuid) {$templatemenuid = 1;}
$templatemenuid = $this->params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
$studieslisttemplateid = $this->params->get('studieslisttemplateid');
	if (!$studieslisttemplateid) {$studieslisttemplateid = JRequest::getVar('templatemenuid',1,'get','int');}
	if (!$teacher->teacher_image) { $image->path = $teacher->image; $image->height = $teacher->imageh; $image->width = $teacher->imagew; }
	//if (!$teacher->teacher_image) { $i_path = $teacher->image; }
	else
	{
		if ($teacher->teacher_image && !$admin_params->get('teachers_imagefolder')) { $i_path = 'images/stories/'.$teacher->teacher_image; }
		if ($teacher->teacher_image && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder/').$teacher->teacher_image;}
		$image = getImage($i_path);
	}

?>
<div id="biblestudy" class="noRefTagger">
<!--
  <table id="bsm_teachertable" cellspacing="0">

<tr>
<td class="bsm_teacherthumbnail">
<?php
if (!$teacher->teacher_image) { $image->path = $teacher->image; $image->height = $teacher->imageh; $image->width = $teacher->imagew; }
	else
	{
		if ($teacher->teacher_image && !$admin_params->get('teachers_imagefolder')) { $i_path = 'images/stories/'.$teacher->teacher_image; }
		if ($teacher->teacher_image && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder/').$teacher->teacher_image;}
		$image = getImage($i_path);
	}

/*
if ($teacher->image || $teacher->teacher_image) 
		{ ?>
        <img src="<?php echo $image->path;?>" width="<?php echo $image->width;?>" height="<?php echo $image->height;?>" border="1" alt="<?php echo $teacher->teachername.' - '.$teacher->title;?>" />
        <?php 
		}?>
     
</td>
<td class="bsm_teachername">
<table id="bsm_teachertable" cellspacing="0">
<tr><td class="bms_teachername">
<?php echo $teacher->teachername;?>
     <?php echo ' - '.$teacher->title;?></td></tr>
     <tr> <td class="bsm_teacherphone">
<?php echo $teacher->phone;?></td></tr>
<tr><td class="bsm_teacheremail">
<?php if ($teacher->email) {
		if (!stristr($teacher->email,'@') ) { ?>
		<a href="<?php echo $teacher->email;?>"><?php echo JText::_('Contact');?></a>
    <?php }
    else { ?>
    	<a href=mailto:"<?php echo $teacher->email;?>"><?php echo JText::_('Contact ');?></a>
        <?php } 
	} //end if $teacher->email?>
    </td></tr>
    <tr><td class="bsm_teacherwebsite">
<?php if ($teacher->website) { ?>
	<a href="<?php echo $teacher->website;?>"><?php echo JText::_('Site');?></a>
    <?php } ?>
    </td></tr></table>
	</td>
</tr>

<?php if ($teacher->information) { ?>

<tr>
	<td class="bsm_teacherlong" colspan="2">
		<?php echo $teacher->information;?>
	</td>
</tr>
<?php }

*/
// end of if for teacher->information?>
</table>
-->
<table id="bslisttable" cellspacing="0">
<?php

  $listing = getTeacherDetailsExp($teacher, $params, $this->template, $admin_params);
  echo $listing;
if ($this->params->get('show_teacher_studies') > 0) {
  $studies = getTeacherStudiesExp($teacher->id, $params, $admin_params, $this->template);
  echo $studies;
}

echo '<table><tr><td id="bsmsteacherstudyfooter"><a href="'.JRoute::_('index.php?option=com_biblestudy&view=studieslist&filter_teacher='.$teacher->id).'">'.JText::_('See more from this teacher').' --></a></td><tr></table>';
	 // end of if show_teacher_studies ?>
<?php 
//if ($this->menuid){$link = '&Itemid='.$this->menuid;}?>
<tr><td align="center" colspan="0"class="bsm_teacherfooter"><a href="index.php?option=com_biblestudy&view=teacherlist<?php echo '&templatemenuid='.$templatemenuid;?>"><?php echo '<--'.JText::_('Return to Teacher List');?></a>
</table>
</div>