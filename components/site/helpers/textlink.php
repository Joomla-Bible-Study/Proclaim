<?php
defined('_JEXEC') or die();

function getTextlink($params, $row, $textorpdf, $admin_params, $template)
{dump ($template, 'templatetextlink: ');
/*$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'scripture.php');
include_once($path1.'image.php');
$scripturerow = 1;	
$scripture1 = getScripture($params, $row, $esv, $scripturerow);
$intro = str_replace('"','',$row->studyintro);
$templatemenuid = $params->get('detailstemplateid');

if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}

	if ($textorpdf == 'text') {
		if ($template[0]->text == '- No Image -') { $i_path = 'components/com_biblestudy/images/textfile24.png'; $textimage = getImage($i_path); }
	else 
	{
	  	if ($template[0]->text && !$admin_params->get('media_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$template[0]->text; }
	  	if ($template[0]->text && $admin_params->get('media_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('media_imagefolder').DS.$template[0]->text;}
		$textimage = getImage($i_path);
	}
	   $src = JURI::base().$textimage->path;
		$height = $textimage->height;
		$width = $textimage->width;
       $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id.'&templatemenuid='.$templatemenuid ).JHTML::_('behavior.tooltip');
	   $details_text = $params->get('details_text');
	}
	if ($textorpdf == 'pdf') 
	{
		if ($template[0]->pdf == '- No Image -') { $i_path = 'components/com_biblestudy/images/pdf24.png'; $pdfimage = getImage($i_path); }
	else 
	{
	  	if ($template[0]->pdf && !$admin_params->get('media_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$template[0]->pdf; }
	  	if ($template[0]->pdf && $admin_params->get('media_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('media_imagefolder').DS.$template[0]->pdf;}
		$pdfimage = getImage($i_path);
	}
		$src = JURI::base().$pdfimage->path;
		$height = $pdfimage->height;
		$width = $pdfimage->width;
	    $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf' );
		$details_text = $params->get('details_text').JText::_(' - PDF Version');
	}
	//dump ($i_path, 'text: ');
	if ($params->get('tooltip') >0) {
		//JHTML::_('behavior.tooltip');
        $linktext = '<div class="zoomTip" title="<strong>'.JText::_('Sermon Info').'</strong> :: ';
       	  if ($row->studytitle) {$linktext .= '<strong>'.JText::_('Title: ').'</strong>'.$row->studytitle.'<br />';}
       	  if ($intro) {$linktext .= '<strong>'.JText::_('Details: ').'</strong>'.$intro.'<br /><br />';}
       	  if ($row->studynumber) { $linktext .= '<strong>'.JText::_('Sermon Number: ').'</strong>'.$row->studynumber.'<br />';}
       	  if ($row->teachername) {$linktext .= '<strong>'.JText::_('Teacher: ').'</strong>'.$row->teachername.'<br />';}
       	 $linktext .= '
		 <br />';
       	  if ($scripture1) {$linktext .= '<strong>'.JText::_('Scripture: ').'</strong>'.$scripture1.'">';}
       } //end of is show tooltip
	
    
	$linktext .= '
	<a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
	$linktext .= '</div>';
	
   return $linktext;
*/   
}