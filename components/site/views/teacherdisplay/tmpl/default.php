<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php global $mainframe;

$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$teacher = $this->teacher;
$admin_params = $this->admin_params;
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');
//$templatemenuid = JRequest::getVar('templatemenuid', 1,'get', 'int');
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
		if ($teacher->teacher_image && $admin_params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('teachers_imagefolder').DS.$teacher->teacher_image;}
		$image = getImage($i_path);
	}
?>
<div id="biblestudy" class="noRefTagger">
<table id="bsm_teachertable" cellspacing="0">
<tr class="titlerow"><td colspan="2"><?php echo $this->params->get('teacher_title');?></td></tr>

<tr>
<td class="bsm_teacherthumbnail">
<?php if ($teacher->image || $teacher->teacher_image) 
		{ ?>
        <img src="<?php echo $image->path;?>" width="<?php echo $image->width;?>" height="<?php echo $image->height;?>" border="1" alt="<?php echo $teacher->teachername.' - '.$teacher->title;?>" />
        <?php 
		}?>
     
</td>
<td class="bsm_teachername">
<?php echo $teacher->teachername;?>
     <?php echo ' - '.$teacher->title;?><br />
<?php echo $teacher->phone;?><br /><br />
<?php if ($teacher->email) {
		if (!stristr($teacher->email,'@') ) { ?>
		<a href="<?php echo $teacher->email;?>"><?php echo JText::_('Contact');?></a>
    <?php }
    else { ?>
    	<a href=mailto:"<?php echo $teacher->email;?>"><?php echo JText::_('Contact ');?></a>
        <?php } 
	} //end if $teacher->email?>
    <br />
<?php if ($teacher->website) { ?>
	<a href="<?php echo $teacher->website;?>"><?php echo JText::_('Site');?></a>
    <?php } ?>
	</td>
</tr>

<?php if ($teacher->information) { ?>

<tr>
	<td class="bsm_teacherlong" colspan="2">
		<?php echo $teacher->information;?>
	</td>
</tr>
<?php } // end of if for teacher->information?>
<tr>
	<td>
    	<?php if ($this->params->get('show_teacher_studies') > 0) { ?>
		<img src="<?php echo JURI::base().'components/com_biblestudy/images/square.gif'?>" height="3" width="100%" />
	</td>
</tr>
<?php $result = count($this->studies); 
if ($result < 1 ){ }
else { ?>
<tr><td><h2><?php echo $this->params->get('label_teacher');?></h2></td></tr>
</table>

<table cellpadding="1" width="100%">
<tr>
<td> <strong><?php echo JText::_('Title');?></strong></td>
<td> <strong><?php echo JText::_('Scripture');?></strong></td>
<td> <strong><?php echo JText::_('Date');?></strong></td>
</tr>
<?php foreach ($this->studies as $study) { ?>
 <tr>
  <td> <a href="index.php?option=com_biblestudy&view=studydetails&id=<?php echo $study->sid.'&templatemenuid='.$studieslisttemplateid;?>"><?php echo $study->studytitle; ?></a></td>
  <td> <?php echo $study->bookname.' '.$study->chapter_begin;?></td>
  <td> <?php $date = JHTML::_('date', $study->studydate, JText::_('DATE_FORMAT_LC') , '$offset'); echo $date;?></td>
 </tr>
<?php } // end of foreach ?>
<?php	} // end of if show_teacher_studies ?>
<?php } // end of else testing if $result 
//if ($this->menuid){$link = '&Itemid='.$this->menuid;}?>
<tr><td align="center" colspan="0"><br /><a href="index.php?option=com_biblestudy&view=teacherlist<?php echo '&templatemenuid='.$templatemenuid;?>"><?php echo '<--'.JText::_('Return to Teacher List');?></a>
</table>
</div>