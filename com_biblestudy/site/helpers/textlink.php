<?php
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
function getTextlink($params, $row, $textorpdf, $admin_params, $template)
{//dump ($template, 'template: ');
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'scripture.php');
include_once($path1.'image.php');
include_once($path1.'helper.php');
$scripturerow = 1;
$scripture1 = getScripture($params, $row, $esv=null, $scripturerow);
$intro = str_replace('"','',$row->studyintro);
$t = $params->get('detailstemplateid',1);
	//This was added to see if we could get AVR to behave properly. In somes cases it errors out with Popup Database Error is there is no Itemid
//	$itemid = JRequest::getVar('Itemid','get');
//	if (!$itemid) {JRequest::setVar('Itemid',1,'get'); $itemid='1';}
	//End AVR
//I put in the below check because for some reason when showing teacher and/or header with a textlink caused an error, saying the a JParameter type was being sent. I was not able to figure out where it was coming from, so added this check because if it is a JParameter object, get_object_vars will return with the object, otherwise it returns FALSE
$object_vars = @get_object_vars( $template ) ;
//dump ($object_vars, 'myobject: ');
if (!$object_vars) {
	$images = new jbsImages();
if (!$t) {$t = JRequest::getVar('t',1,'get','int');}

	if ($textorpdf == 'text') {
		if (!$template[0]->text ) { $i_path = 'components/com_biblestudy/images/textfile24.png'; 
        $textimage = getImagePath($i_path); 
        $src = JURI::base().$textimage->path;
		$height = $textimage->height;
		$width = $textimage->width;
        }
    elseif (substr_count($template[0]->text,'http://'))
    {
        $src = $template[0]->text;
        $height = '24';
        $width = '24';
    }
	else
	{
 	 if ($template[0]->text ) { $i_path = $template[0]->text; }

		$textimage = $images->getImagePath($i_path);
        $src = JURI::base().$textimage->path;
		$height = $textimage->height;
		$width = $textimage->width;
	}
	   
		
       $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id.'&t='.$t ).JHTML::_('behavior.tooltip');
	   $details_text = $params->get('details_text');
	}
	
	if ($params->get('tooltip') >0)
		{
			$linktext = getTooltip($row->id, $row, $params, $admin_params, $template);
       	} //end of is show tooltip


	$linktext .= '
	<a href="'.$link.'"><img src="'.$src.'" alt="'.$details_text.'" width="'.$width.'" height="'.$height.'" border="0" />';

	if ($params->get('tooltip') >0) {$linktext .= '</span>';}
	$linktext .= '</a></span>';

   return $linktext;
} // end of if object_vars is FALSE
}