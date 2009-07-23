<?php defined('_JEXEC') or die('Restriced Access');
function getSerieslist($row, $params, $oddeven, $admin_params, $template)
{ //dump ($row, 'series: ');
	
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	include_once($path1.'image.php');
	
	if ($admin_params->get('series_show_description') > 0) {$listing .= '<tr class="onlyrow'.$oddeven.'">';}
	else {$listing .= '<tr class="firstrow'.$oddeven.'>';}
	
	$custom = $params->get('seriescustom1');
	$listelementid = $params->get('serieselement1'); //dump ($listelementid, 'listelementid: ');
	$islink = $params->get('seriesislink1');
	$listelement = seriesGetelement($row, $listelementid, $custom, $islink, $admin_params, $params); 
	$listing .= '<td class="firstcol">';
	$listing .= $listelement;
	$listing .= '</td>';
	
	$custom = $params->get('seriescustom2');
	$listelementid = $params->get('serieselement2');
	$islink = $params->get('seriesislink2');
	$listelement = seriesGetelement($row, $listelementid, $custom, $islink, $admin_params, $params);
	$listing .= '<td >';
	$listing .= $listelement;
	$listing .= '</td>';
	
	$custom = $params->get('seriescustom3');
	$listelementid = $params->get('serieselement3');
	$islink = $params->get('seriesislink3');
	$listelement = seriesGetelement($row, $listelementid, $custom, $islink, $admin_params, $params);
	$listing .= '<td >';
	$listing .= $listelement;
	$listing .= '</td>';
	
	$custom = $params->get('seriescustom4');
	$listelementid = $params->get('serieselement4');
	$islink = $params->get('seriesislink4');
	$listelement = seriesGetelement($row, $listelementid, $custom, $islink, $admin_params, $params);
	$listing .= '<td class="lastcol">';
	$listing .= $listelement; //dump ($listelement, 'listelement: ');
	$listing .= '</td>';
	
	$listing .= '</tr>';
	
	//add if last row to above
	
	if ($params->get('series_show_description') > 0) 
		{
			$listing .= '<tr class="lastrow'.$oddeven.'">';
			$listing .= '<td colspan="4">'.description.'</td></tr>';
		}
	//dump ($listing, 'listing: ');
	return $listing;
}

//elements are: series title, series image, series pastor + image, description

function getSerieslink($row, $element, $params, $admin_params)
{
	$link = '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=seriesdetails&templatemenuid='.$admin_params->get('seriesdetailstemplateid', 1).'&id='.$row->id).'">'.$element.'</a>';
	return $link;
}

function seriesGetelement($row, $listelementid, $custom, $islink, $admin_params, $params)
{//dump ($listelementid, 'listelementidcheck: ');
	switch ($listelementid)
	{ 
		case 1:
			$element = $row->series_text;
			break;
		case 2:
			if ($row->series_thumbnail && !$params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumnbail; }
			if ($row->series_thumbnail && $params->get('series_imagefolder')) { $i_path = 'images'.DS.$params->get('series_imagefolder').DS.$row->series_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'">';
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			break;
		case 3:
			if ($row->series_thumbnail && !$params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumnbail; }
			if ($row->series_thumbnail && $params->get('series_imagefolder')) { $i_path = 'images'.DS.$params->get('series_imagefolder').DS.$row->series_thumbnail;}
			$image = getImage($i_path);
			$element = '<table><tr><td><img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'"></td></tr>'
			.'<tr><td align="center">'.$row->series_text;
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			$element .='</td></tr></table>';
			break;
		case 4:
			$row->teachername.' - '.$row->teachertitle;
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			break;
		case 5:
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			if ($row->teacher_thumbnail && !$params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
			if ($row->teacher_thumbnail && $params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$params->get('teachers_imagefolder').DS.$row->teacher_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			break;
		case 6:
			$element = '<table><tr><td>';
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			if ($row->teacher_thumbnail && !$params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
			if ($row->teacher_thumbnail && $params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$params->get('teachers_imagefolder').DS.$row->teacher_thumbnail;}
			$image = getImage($i_path);
			$element .= '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			$element .= '</td></tr><tr><td align="center">'.$row->teaachername;
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			$element .= '</td></tr></table>';
			break;
		case 7:
			$element = $row->description;
			if ($islink > 0) {$element = getSerieslink($row, $element, $params, $admin_params);}
			break;
	}//dump ($element, 'element: ');
	return $element;
}