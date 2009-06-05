<?php defined('_JEXEC') or die('Restricted access'); 

function getTitle($params, $row)
{
	if ($params->get('title_line_1') > 0) 
		{ 
		$title = '<table id="titletable" cellspacing="0"><tbody><tr><td><h1 class="componentheading">';
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
			$scripture = getScripture($params, $row, $esv, $scripturerow = 1);
			$title .= $scripture;
			break;
		case 5:
			$title .= $row->stext;
			break;
		case 6:
			$title .= $row->topics_text;
			break;
		}
	$title .= '</h1></td></tr>';
	}
	
	if ($params->get('title_line_2') > 0) 
	{ 
	$title .= '<tr><td id="titlesecondline" class="titletable">';
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
			$scripture = getScripture($params, $row, $esv, $scripturerow = 1);
			$title .= $scripture;
			break;
		case 5:
			$title .= $row->stext;
			break;
		case 6:
			$title .= $row->topics_text;
			break;
		}
		$title .= '</td><tr></tbody></table>';
	} // end of if title2
	//$title .= '</div>';
return $title;
}

