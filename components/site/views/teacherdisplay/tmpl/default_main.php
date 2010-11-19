<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $mainframe =& JFactory::getApplication();

require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'teacher.php');
include_once($path1.'listing.php');

$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$teacher = $this->teacher;
$admin_params = $this->admin_params;
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');
$templatemenuid = JRequest::getVar('templatemenuid', 1,'get', 'int');
if (!$templatemenuid) {$templatemenuid = 1;}
$templatemenuid = $this->params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
$studieslisttemplateid = $this->params->get('studieslisttemplateid');
	$images = new jbsImages();

	if (!$studieslisttemplateid) {$studieslisttemplateid = JRequest::getVar('templatemenuid',1,'get','int');}

?>
<div id="biblestudy" class="noRefTagger">
<table id="bsm_teachertable" cellspacing="0">

<tr>
<td class="bsm_teacherthumbnail">
<?php 
$image = $images->getTeacherThumbnail($teacher->teacher_thumbnail, $teacher->thumb);
	//{ 
	if ($teacher->title) {$teacherdisplay = $teacher->teachername.' - '.$teacher->title;}else {$teacherdisplay = $teacher->teachername;}?>
        <img src="<?php echo JURI::base().$image->path;?>" width="<?php echo $image->width;?>" height="<?php echo $image->height;?>" border="1" alt="<?php echo $teacherdisplay;?>" />
        <?php 
//		}?>
     
</td>
<td class="bsm_teachername">
<table id="bsm_teachertable" cellspacing="0">
<tr><td class="bsm_teachername">
<?php echo $teacherdisplay;?>
     </td></tr>
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
<?php } // end of if for teacher->information?>
</table>
<?php 

?> <table id="bslisttable" cellspacing="0"><tr><td> <?php
switch ($this->params->get('show_teacher_studies'))
{
	case 1:
?>    <table  id="bsm_teachertable" cellspacing="0">
<tr class="titlerow"><td class="title" colspan="3"><?php echo $this->params->get('label_teacher');?></td></tr>

<tr class="bsm_studiestitlerow">
<td class="bsm_titletitle"> <?php echo JText::_('JBS_CMN_TITLE');?></td>
<td class="bsm_titlescripture"> <?php echo JText::_('JBS_CMN_SCIPTURE');?></td>
<td class="bsm_titledate"> <?php echo JText::_('JBS_CMN_STUDY_DATE');?></td>
</tr>
<?php foreach ($this->studies as $study) { ?>
 <tr>
  <td class="bsm_studylink"> <a href="index.php?option=com_biblestudy&view=studydetails&id=<?php echo $study->id.'&templatemenuid='.$studieslisttemplateid;?>"><?php echo $study->studytitle; ?></a></td>
  <td class="bsm_scripture"> <?php if ($study->bookname) {echo $study->bookname.' '.$study->chapter_begin;}?></td>
  <td class="bsm_date"> <?php $date = JHTML::_('date', $study->studydate, JText::_('DATE_FORMAT_LC') , '$offset'); echo $date;?></td>
 </tr>
<?php } // end of foreach ?>


</table><?php
    break;
    
    case 2:
    ?>    <table id="bsm_teachertable" cellspacing="0">
<tr class="titlerow"><td class="title" colspan="3"><?php echo $this->params->get('label_teacher');?></td></tr></table><table id="bslisttable" cellspacing="0"><tr><td><?php
	$headerCall = JView::loadHelper('header');
     $header = getHeader($this->studies, $this->params, $this->admin_params, $this->template, $showheader = $this->params->get('use_headers_list'), $ismodule=0);
	 echo $header;
	$class1 = 'bsodd';
 	$class2 = 'bseven';
 	$oddeven = $class1;
	 foreach ($this->studies as $row) { //Run through each row of the data result from the model
		if($oddeven == $class1){ //Alternate the color background
		$oddeven = $class2;
		} else {
		$oddeven = $class1;
		}
	  $studies = getListing($row, $this->params, $oddeven, $admin_params, $this->template, $ismodule=0);
	  echo $studies;
	}
    ?></td></tr></table><?php
	break;
	
	case 3:
	$studies = getTeacherStudiesExp($teacher->id, $params, $admin_params, $this->template);
	echo $studies;
	break;
}



?> </td></tr>
<?php
?>
<tr><td align="center" colspan="0"class="bsm_teacherfooter"><a href="index.php?option=com_biblestudy&view=teacherlist&templatemenuid=<?php echo $templatemenuid;?>"><?php echo '<-- '.JText::_('Return to Teacher List');?></a> <?php if ($this->params->get('teacherlink', '1') > 0) { echo ' | <a href="index.php?option=com_biblestudy&view=studieslist&filter_teacher='.(int)$teacher->id.'&templatemenuid='.$templatemenuid.'">'.JText::_('More From This Teacher').' --></a>';}
?></td></tr></table>
</div>