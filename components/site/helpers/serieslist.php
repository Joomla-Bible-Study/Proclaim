<?php defined('_JEXEC') or die('Restriced Access');
function getSerieslist($row, $params, $oddeven, $admin_params, $template)
{
	
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	include_once($path1.'image.php');
	
	if ($admin_params->get('series_show_description') > 0) {$listing .= '<tr class="onlyrow'.$oddeven.'">';}
	else {$listing .= '<tr class="firstrow'.$oddeven;}
	$listing .= '">';
	
	$description = $row->description;
	
	$custom = $admin_params->get('seriescustom1');
	$listelementid = $admin_params->get('serieselement1');
	$islink = $admin_params->get('seriesislink1');
	$listelement = seriesGetelement($listelementid, $custom, $islink);
	$listing .= '<td class="firstcol">';
	$listing .= $element;
	$listing .= '</td>';
	
	$custom = $admin_params->get('seriescustom2');
	$listelementid = $admin_params->get('serieselement2');
	$islink = $admin_params->get('seriesislink2');
	$listelement = seriesGetelement($listelementid, $custom, $islink);
	$listing .= '<td >';
	$listing .= $element;
	$listing .= '</td>';
	
	$custom = $admin_params->get('seriescustom3');
	$listelementid = $admin_params->get('serieselement3');
	$islink = $admin_params->get('seriesislink3');
	$listelement = seriesGetelement($listelementid, $custom, $islink);
	$listing .= '<td >';
	$listing .= $element;
	$listing .= '</td>';
	
	$custom = $admin_params->get('seriescustom4');
	$listelementid = $admin_params->get('serieselement4');
	$islink = $admin_params->get('seriesislink4');
	$listelement = seriesGetelement($listelementid, $custom, $islink);
	$listing .= '<td class="lastcol">';
	$listing .= $element;
	$listing .= '</td>';
	
	$listing .= '</tr>';
	//add if last row to above
	
	if ($admin_params->get('series_show_description') > 0) {$listing .= '<tr class="lastrow'.$oddeven.'">';}
	
	$listing .= '<td colspan="4">'.description.'</td></tr>';
	
	return $listing;
}

//elements are: series title, series image, series pastor + image, description

function getSerieslink($row, $element, $admin_params)
{
	$link = '<a href="'.JRoute('index.php?option=com_biblestudy&view=seriesdetails&templatemenuid='.$admin_params->get('seriesdetailstemplateid', 1).'&id='.$row->id).'">'.$element.'</a>';
	return $link;
}

function seriesGetelement($elementid, $custom, $islink)
{
	switch ($elementid)
	{
		case 1:
			$element = $row->series_text;
			break;
		case 2:
			if ($row->series_thumbnail && !$admin_params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumnbail; }
			if ($row->series_thumbnail && $admin_params->get('series_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('series_imagefolder').DS.$row->series_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'">';
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
		case 3:
			if ($row->series_thumbnail && !$admin_params->get('series_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$row->series_thumnbail; }
			if ($row->series_thumbnail && $admin_params->get('series_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('series_imagefolder').DS.$row->series_thumbnail;}
			$image = getImage($i_path);
			$element = '<table><tr><td><img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->series_text.'"></td></tr>'
			.'<tr><td align="center">'.$row->series_text.'<td></tr></table>';
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
		case 4:
			$row->teachername.' - '.$row->teachertitle;
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
		case 5:
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			if ($row->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
			if ($row->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('teachers_imagefolder').DS.$row->teacher_thumbnail;}
			$image = getImage($i_path);
			$element = '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
		case 6:
			$element = '<table><tr><td>';
			if ($row->teacher_thumbnail == '- Select Image -' || !$row->teacher_thumbnail) { $image->path = $row->thumb; $image->height = $row->thumbh; $image->width = $row->thumbw;}
			if ($row->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$row->teacher_thumbnail; }
			if ($row->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('teachers_imagefolder').DS.$row->teacher_thumbnail;}
			$image = getImage($i_path);
			$element .= '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->teachername.'">';
			$element .= '</td></tr><tr><td align="center">'.$row->teaachername;
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
		case 7:
			$element = $row->description;
			if ($islink > 0) {$element = getSerieslink($row, $element, $admin_params);}
			break;
	}
	return $element;
}