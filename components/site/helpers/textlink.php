<?php
defined('_JEXEC') or die();

function getTextlink($params, $row, $textorpdf, $admin_params, $template)
{//dump ($template, 'templatetextlink: ');
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'scripture.php');
include_once($path1.'image.php');
$scripturerow = 1;	
$scripture1 = getScripture($params, $row, $esv=null, $scripturerow);
$intro = str_replace('"','',$row->studyintro);
$templatemenuid = $params->get('detailstemplateid');
//I put in the below check because for some reason when showing teacher and/or header with a textlink caused an error, saying the a JParameter type was being sent. I was not able to figure out where it was coming from, so added this check because if it is a JParameter object, get_object_vars will return with the object, otherwise it returns FALSE
$object_vars = get_object_vars( $template ) ;
//dump ($object_vars, 'myobject: ');
if (!$object_vars) {
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
		//$link = 'index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf';
		$details_text = $params->get('details_text').JText::_(' - PDF Version');
	}
	//dump ($i_path, 'text: ');
	if ($params->get('tooltip') >0) 
		{
			$linktext = getTooltip($row->id, $row, $params, $admin_params, $template);
       	} //end of is show tooltip
	
    
	$linktext .= '
	<a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" />';
	
	if ($params->get('tooltip') >0) {$linktext .= '</span>';}
	$linktext .= '</a>';
	//This was added to see if we could get AVR to behave properly. In somes cases it errors out with Popup Database Error is there is no Itemid
	$itemid = JRequest::getVar('Itemid','get');
	if (!$itemid) {$itemid = JRequest::setVar('Itemid',1,'get');}
	//End AVR
   return $linktext;
} // end of if object_vars is FALSE
}

function getTooltip($rowid, $row, $params, $admin_params, $template)
	{
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'elements.php');
		$toolTipArray = array('className'=>'custom');
		JHTML::_('behavior.mootools');
		JHTML::_('behavior.tooltip', '.zoomTip', $toolTipArray);

        $linktext = '<span class="zoomTip" title="<strong>'.$params->get('tip_title').': </strong> :: ';
       	$tip1 = getElementid($params->get('tip_item1'), $row, $params, $admin_params, $template);  
		$tip2 = getElementid($params->get('tip_item2'), $row, $params, $admin_params, $template);
		$tip3 = getElementid($params->get('tip_item3'), $row, $params, $admin_params, $template);
		$tip4 = getElementid($params->get('tip_item4'), $row, $params, $admin_params, $template);
		$tip5 = getElementid($params->get('tip_item5'), $row, $params, $admin_params, $template);
		
		$linktext .= '<strong>'.$params->get('tip_item1_title').'</strong>: '.$tip1->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item2_title').'</strong>: '.$tip2->element.'<br /><br />';
		$linktext .= '<strong>'.$params->get('tip_item3_title').'</strong>: '.$tip3->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item4_title').'</strong>: '.$tip4->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item5_title').'</strong>: '.$tip5->element;
 		$linktext .= '">';
	return $linktext;	
	}