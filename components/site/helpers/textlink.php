<?php
defined('_JEXEC') or die();

function getTextlink($params, $row, $textorpdf)
{
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'scripture.php');
$scripturerow = 1;	
$scripture1 = getScripture($params, $row, $esv, $scripturerow);
$intro = str_replace('"','',$row->studyintro);
$detailsitemid = $params->get('detailsitemid');

	if ($textorpdf == 'text') {
	   $src = JURI::base().$params->get('text_image');
       $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id.'&Itemid='.$detailsitemid ).JHTML::_('behavior.tooltip');
	   $details_text = $params->get('details_text');
	}
	if ($textorpdf == 'pdf') 
	{
		$src = JURI::base().$params->get('pdf_image');
	    $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf' );
		$details_text = $params->get('details_text').JText::_(' - PDF Version');
	}
	if ($params->get('tooltip') >0) {
		JHTML::_('behavior.tooltip');
        $linktext = '<div class="zoomTip" title="<strong>'.JText::_('Sermon Info').'</strong> :: ';
       	  if ($row->studytitle) {$linktext .= '<strong>'.JText::_('Title: ').'</strong>'.$row->studytitle.'<br />';}
       	  if ($intro) {$linktext .= '<strong>'.JText::_('Details: ').'</strong>'.$intro.'<br /><br />';}
       	  if ($row->studynumber) { $linktext .= '<strong>'.JText::_('Sermon Number: ').'</strong>'.$row->studynumber.'<br />';}
       	  if ($row->teachername) {$linktext .= '<strong>'.JText::_('Teacher: ').'</strong>'.$row->teachername.'<br />';}
       	 $linktext .= '<br />';
       	  if ($scripture1) {$linktext .= '<strong>'.JText::_('Scripture: ').'</strong>'.$scripture1.'">';}
       } //end of is show tooltip
	if ($params->get('imagew', 24)) {$width = $params->get('imagew', 24);} else {$width = 24;}
    if ($params->get('imageh', 24)) {$height = $params->get('imageh', 24);} else {$height= 24;}
    
	$linktext .= '<a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
	$linktext .= '</div>';
	
   return $linktext;
   
}