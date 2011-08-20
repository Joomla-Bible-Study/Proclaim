<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
$user =& JFactory::getUser();
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
$params = $this->params;

$t = $params->get('teachertemplateid');
if (!$t) {
	$t = JRequest::getVar('t', 1, 'get', 'int');
}
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
$admin_params = $this->admin_params;
include_once($path1.'image.php');
?>
<div id="biblestudy" class="noRefTagger">
	<table id="bsm_teachertable" cellspacing="0">
		<tbody>
			<tr class="titlerow">
				<td align="center" colspan="3" class="title"><?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS'));?>
				</td>
			</tr>

			<tr>
				<td>
				<?php
				$class1 = 'bsodd';
				$class2 = 'bseven';
				$oddeven = $class1;
				?>
					

				<?php foreach ($this->items as $item) {
					if ($item->title) {
						$teacherdisplay = $item->teachername.' - '.$item->title;
					}else {$teacherdisplay = $item->teachername;
					}
					if($oddeven == $class1){
						//Alternate the color background
						$oddeven = $class2;
					} else {
						$oddeven = $class1;
					}
					$images = new jbsImages();
					$image = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
					?>
			
			
			
			
			
			<tr class="<?php echo $oddeven; ?> lastrow">

				<td class="bsm_teacherthumbnail"><?php if ($item->thumb || $item->teacher_thumbnail){?>
					<img src="<?php echo $image->path;?>" border="1"
					title="<?php echo $item->teachername;?>"
					alt="<?php echo $item->teachername;?>"
					width="<?php echo $image->width;?>"
					height="<?php echo $image->height;?>" />
				
				
				
				<?php } ?>
        </td>
				<td class="bsm_teachername"><a
					href="index.php?option=com_biblestudy&view=teacherdisplay&id=<?php echo $item->id.'&t='.$t;?>"><?php echo $teacherdisplay;?>
				</a>
				</td>

				<td align="left" class="bsm_short">
				<?php echo $item->short;?>
				</td>
			</tr>
			
			
			
			
   
    	

    <?php } //end of foreach ?>
</td></tr>
</tbody>
	</table>
	<div class="listingfooter">
		

	<?php
	echo $this->pagination->getPagesLinks();
      echo $this->pagination->getPagesCounter();
	 ?>
	</div>
	<!--end of bsfooter div-->
</div>
