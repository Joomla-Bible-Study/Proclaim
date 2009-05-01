<?php defined('_JEXEC') or die('Restricted access'); 

function getTitle($params, $row)
{
	if ($params->get('title_line_1') > 0) 
		{ 
		$title = '<div id="detailsheadertext'.$params->get('pageclass_sfx').'">';
	 switch ($params->get('title_line_1'))
		{
		case 0:
			$title .= null;
			break; 	
		case 1:
			$title .= $row->studytitle;
			break;
		case 2:
			$title .= $row->teachername;
			break;
		case 3:
			$title .= $row->title.' '.$row->teachername;
			break;
		case 4:
			$esv = 0;
			$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
			include_once($path1.'scripture.php');
			$scripture = getScripture($params, $row, $esv);
			$title .= $scripture;
			break;
		case 5:
			$title .= $row->stext;
			break;
		case 6:
			$title .= $row->topics_text;
			break;
		}
	
	}
	
	if ($params->get('title_line_2') > 0) 
	{ 
	$title .= '<br /><div id="detailstitle2'.$params->get('pageclass_sfx').'">';
	switch ($params->get('title_line_2'))
		{
		case 0:
			$title .= null;
			break; 	
		case 1:
			$title .= $row->studytitle;
			break;
		case 2:
			$title .= $row->teachername;
			break;
		case 3:
			$title .= $row->title.' '.$row->teachername;
			break;
		case 4:
			$esv = 0;
			$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
			include_once($path1.'scripture.php');
			$scripture = getScripture($params, $row, $esv);
			$title .= $scripture;
			break;
		case 5:
			$title .= $row->stext;
			break;
		case 6:
			$title .= $row->topics_text;
			break;
		}
		$title .= '</div>';
	} // end of if title2
	$title .= '</div>';
return $title;
}

