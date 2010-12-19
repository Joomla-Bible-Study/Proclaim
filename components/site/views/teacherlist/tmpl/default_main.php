<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php //$params = &JComponentHelper::getParams($option);  
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
$user =& JFactory::getUser();
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
$params = $this->params;

$templatemenuid = $params->get('teachertemplateid');
//dump ($templatemenuid);
if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');}
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
$admin_params = $this->admin_params;
include_once($path1.'image.php');
//if (!$templatemenuid){$templatemenuid = 1;}
?>
<div id="biblestudy" class="noRefTagger">
<table id="bsm_teachertable" cellspacing="0">
<tbody>
    <tr class="titlerow"><td align="center" colspan="3" class="title" ><?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS'));?></td></tr>
   
<tr><td>
<?php 
$class1 = 'bsodd';
 $class2 = 'bseven';
 $oddeven = $class1;
?>
 
	
    
<?php foreach ($this->items as $item) {
	if ($item->title) {$teacherdisplay = $item->teachername.' - '.$item->title;}else {$teacherdisplay = $item->teachername;}
	if($oddeven == $class1){ //Alternate the color background
	$oddeven = $class2;
	} else {
	$oddeven = $class1;
	}
	$images = new jbsImages();
	$image = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
//if (!$item->teacher_thumbnail) { $image->path = $item->thumb; $image->height = $item->thumbh; $image->width = $item->thumbw; }
//	else
//	{
//		if ($item->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'images/stories/'.$item->teacher_thumbnail; }
//		if ($item->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images/'.$admin_params->get('teachers_imagefolder').'/'.$item->teacher_thumbnail;}
//		$image = getImage($i_path);
//	}
?>

    
    <tr class="<?php echo $oddeven; ?> lastrow">
   
        <td class="bsm_teacherthumbnail" ><?php if ($item->thumb || $item->teacher_thumbnail){?>
        	<img src="<?php echo $image->path;?>" border="1" title="<?php echo $item->teachername;?>" alt="<?php echo $item->teachername;?>" width="<?php echo $image->width;?>" height="<?php echo $image->height;?>" /><?php } ?>
        </td>
        <td class="bsm_teachername">
            <a href="index.php?option=com_biblestudy&view=teacherdisplay&id=<?php echo $item->id.'&templatemenuid='.$templatemenuid;?>"><?php echo $teacherdisplay;?></a>
        </td>
        
        <td align="left" class="bsm_short">
			<?php echo $item->short;?>
        </td>
     </tr>
   
    	

    <?php } //end of foreach ?>
</td></tr>
</tbody>
</table>
<div class="listingfooter" >
	<?php 
      echo $this->pagination->getPagesLinks();
      echo $this->pagination->getPagesCounter();
	 ?>
</div> <!--end of bsfooter div-->
</div>