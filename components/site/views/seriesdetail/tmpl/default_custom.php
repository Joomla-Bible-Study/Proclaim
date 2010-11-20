<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
//JHTML::_('behavior.tooltip');

$document =& JFactory::getDocument();
//$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$params = $this->params;
$url = $this->params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}	
$listingcall = JView::loadHelper('serieslist');
$templatemenuid = $this->params->get('serieslisttemplateid');
if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
<?php 
    
    //dump ($this->items);
    echo getSeriesDetailsExp($this->items, $this->params, $this->admin_params, $this->template);
   ?> <table id="bslisttable" cellspacing="0"> <?php
    $studies = getSeriesstudiesExp($this->items->id, $this->params, $this->admin_params, $this->template);	echo $listing;
    echo $studies;
 	?></table>
<?php	if ($this->params->get('series_list_return') > 0) 
		{
            echo '<table><tr class="seriesreturnlink"><td><a href="'.JRoute::_('index.php?option=com_biblestudy&view=serieslist&templatemenuid='.$templatemenuid).'"><< '.JText::_('JBS_SER_RETURN_SERIES_LIST').'</a> | <a href="'.JRoute::_('index.php?option=com_biblestudy&view=studieslist&filter_series='.$this->items->id.'&templatemenuid='.$templatemenuid).'">'.JText::_('JBS_CMN_SHOW_ALL').' '.JText::_('JBS_SER_STUDIES_FROM_THIS_SERIES').' >></a></td></tr></table>';
        }
?>        
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">
  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="seriesdetail" type="hidden">
  
</form>