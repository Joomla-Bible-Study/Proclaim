<?php
defined('_JEXEC') or die();

function getTextlink($params, $row, $scripture1, $textorpdf)
{
$intro = str_replace('"','',$row->studyintro);

	if ($textorpdf == 'text') {
	   $src = JURI::base().$params->get('text_image');
       $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id ).JHTML::_('behavior.tooltip');
	   $details_text = $params->get('details_text');
	}
	if ($textorpdf == 'pdf') 
	{
		$src = JURI::base().$params->get('pdf_image');
	    $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf' );
		$details_text = $params->get('details_text').JText::_('- PDF Version');
	}
	if ($params->get('tooltip') >0) {
		JHTML::_('behavior.tooltip');
        $linktext = '<span class="zoomTip" title="<strong>Sermon Info:</strong> :: ';
       	  if ($row->studytitle) {$linktext .= '<strong>'.JText::_('Title:').'</strong>'.$row->studytitle.'<br><br>';}
       	  if ($intro) {$linktext .= '<strong>'.JText::_('Details:').'</strong>'.$intro.'<br><br>';}
       	  if ($row->studynumber) { $linktext .= '<strong>'.JText::_('Sermon Number:').'</strong>'.$row->studynumber.'<br>';}
       	  if ($row->teachername) {$linktext .= '<strong>'.JText::_('Teacher:').'</strong>'.$row->teachername.'<br><br>';}
       	 $linktext .= '<hr /><br>';
       	  if ($scripture1) {$linktext .= '<strong>'.JText::_('Scripture:').'</strong>'.$scripture1.'">';}
       } //end of is show tooltip
	if ($params->get('imagew', 24)) {$width = $params->get('imagew', 24);} else {$width = 24;}
    if ($params->get('imageh', 24)) {$height = $params->get('imageh', 24);} else {$height= 24;}
    
	//$linktext .= '<td><a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
    $linktext .= '<div style="width:100%;"><a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
	if ($params->get('tooltip') >0) {
	$linktext .= '</span>';
	}
	//$linktext .='</td>';
	$linktext .='</div>';
   return $linktext;
   
}