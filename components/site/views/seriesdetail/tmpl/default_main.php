<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
//JHTML::_('behavior.tooltip');

$document =& JFactory::getDocument();
//$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
//$params = $this->params;
$url = $this->params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}	
$listingcall = JView::loadHelper('serieslist');
$studylistcall = JView::loadHelper('listing');
$templatemenuid = $this->params->get('serieslisttemplateid');
if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
    
   
<!--header-->
    
    
   
     <table id="seriestable" cellspacing="0">
      <tbody>

        <?php 
 
	$listing = getSerieslist($this->items, $this->params, $oddeven = 'bsodd', $this->admin_params, $this->template, $view = 1);
	//dump ($listing, 'listing: ');
 	echo $listing;
 	
 	//echo '</table>';
 
 ?>

 
 <?php
 switch ($this->params->get('series_detail_listtype'))
{

	case 0:
		?></tbody></table><table id="seriesstudytable" cellspacing="0"><tbody>
		<?php 
		$studies = getSeriesstudies($this->items->id, $this->params, $this->admin_params, $this->template);
		echo $studies;
		?>  </tbody></table>
		<?php
		break;
		
	case 1:
    ?></tbody></table> <table id="bslisttable" cellspacing="0"><tr><td><?php
     $headerCall = JView::loadHelper('header');
     $header = getHeader($row, $this->params, $this->admin_params, $this->template, $showheader = $this->params->get('use_headers_list'), $ismodule=0);
	 echo $header;
		
		$class1 = 'bsodd';
		$class2 = 'bseven';
		$oddeven = $class1;
        
		foreach ($this->studies as $row) 
		{ //Run through each row of the data result from the model
			if($oddeven == $class1){ //Alternate the color background
			$oddeven = $class2;
			} else {
			$oddeven = $class1;
			}
    //        dump ($row,'$row: ');
		  $studylisting = getListing($row, $this->params, $oddeven, $this->admin_params, $this->template, $ismodule=0);
		  echo $studylisting;
		}
		?>
		  </td></tr></table><?php
		break;
	
	case 2:
		?> </table><table id="seriesstudytable" cellspacing="0"><tr><td><?php
		$studies = getSeriesstudiesExp($this->items->id, $this->params, $this->admin_params, $this->template);	
		echo $studies;
		?>
		</td></tr></table><?php
		break;
}
	if ($this->params->get('series_list_return') > 0) 
		{
            echo '<table><tr class="seriesreturnlink"><td><a href="'.JRoute::_('index.php?option=com_biblestudy&view=serieslist&templatemenuid='.$templatemenuid).'"><< '.JText::_('Return to Series List').'</a> | <a href="'.JRoute::_('index.php?option=com_biblestudy&view=studieslist&filter_series='.$this->items->id.'&templatemenuid='.$templatemenuid).'">'.JText::_('Show All').' '.JText::_('Studies From This Series').' >></a></td></tr></table>';
        }
?>

  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">
  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="seriesdetail" type="hidden">
  
</form>

