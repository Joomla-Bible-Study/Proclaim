<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
//JHTML::_('behavior.tooltip');

$document =& JFactory::getDocument();
//$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
//$params = $this->params;
	
$listingcall = JView::loadHelper('serieslist');

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
 </tbody></table>
<table id="seriesstudytable" cellspacing="0">
<tbody>
<?php 
$studies = getSeriesstudies($this->items->id, $this->params, $this->admin_params, $this->template);
echo $studies;

?>
</tbody></table>
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">
  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="seriesdetail" type="hidden">
  
</form>

