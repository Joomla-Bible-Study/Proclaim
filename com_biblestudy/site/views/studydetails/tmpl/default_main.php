<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
require_once (JPATH_ROOT  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.admin.class.php');
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
JHTML::_('behavior.tooltip');
$params = $this->params;
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$url = $params->get('stylesheet');
if ($url) {
	$document->addStyleSheet($url);
}
$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
$sharecall = JView::loadHelper('share');
?>
<div id="biblestudy" class="noRefTagger">
	<!-- This div is the container for the whole page -->
	<div id="bsmHeader">

		<div class="buttonheading">
			
			

		<?php


		if ($this->params->get('show_print_view') > 0)
		{
			$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'JBS_CMN_PRINT' ) );
			echo '<a href="#&tmpl=component" onclick="window.print();return false;">'.$text.'</a>';
		}
		?>

		</div>
		
		
		
		
<?php //Social Networking begins here
if ($this->admin_params->get('socialnetworking')> 0)
	{ ?>
<div id="bsms_share">
<?php
		$social = getShare($this->detailslink, $row, $params, $this->admin_params);
		echo $social;
?>
</div>
<?php } //End Social Networking ?>
<table><tr><td>
  
 
 <?php if ($this->params->get('show_teacher_view') > 0)
	{ ?>        
    
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($this->params, $row->teacher_id, $this->admin_params);
	echo $teacher;
	echo '</td><td>';
	}?>
	
	
	<?php
	if ($this->params->get('title_line_1') + $params->get('title_line_2') > 0) 
	{
		$title_call = JView::loadHelper('title');
		$title = getTitle($this->params, $row, $this->admin_params, $this->template);
		echo $title;
	}?> 


</td></tr></table>
   </div>
	<!-- header -->

	<table id="bsmsdetailstable" cellspacing="0">


	<?php
	if ($this->params->get('use_headers_view') > 0 || $this->params->get('list_items_view')< 1)
	{
		$headerCall = JView::loadHelper('header');
		$header = getHeader($row, $this->params, $this->admin_params, $this->template, $showheader=$params->get('use_headers_view'), $ismodule=0);
		echo $header;
	}	?>
		<tbody>
			
			

		<?php
		if ($this->params->get('list_items_view') == 1)
		{
			echo '<tr class="bseven"><td class="media">';
			require_once (JPATH_ROOT  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.media.class.php');
			$media = new jbsMedia();
			$listing = $media->getMediaTable($row, $this->params, $this->admin_params);
			echo $listing;
			echo '</td></tr>';

		}
		if ($params->get('list_items_view') == 0)
		{
			$oddeven = 'bsodd';
			$listing = getListing($row, $this->params, $oddeven, $this->admin_params, $this->template, $ismodule=0);
			echo $listing;
		}?>
		</tbody>
	</table>
	<table id="bsmsdetailstable" cellspacing="0">
		<tr>
			<td id="studydetailstext">
			<?php
			if ($this->params->get('show_scripture_link') > 0)
			{
				echo $this->article->studytext;
			}
			else {echo $this->studydetails->studytext;
			}

			?>
			</td>
		</tr>
	</table>
	
	
	
	
<?php 

?>

</div>
<!--End of page container div-->
