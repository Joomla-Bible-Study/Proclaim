<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

function getTeacher($params, $id, $admin_params)
{
	
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$teacher = null;
	//$templatemenuid = $params->get('templatemenuid');
	$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$teacherid = $params->get('teacher_id');

	if ($teacherid > 0) {$teacherids['id'] = $teacherid;}
	if ($params->get('mult_teachers')) { $teacherids = explode(",", $params->get('mult_teachers'));}
	if ($params->get('mult_teachers') && $teacherid) {$teacherids = explode(",", $params->get('mult_teachers')); $teacherids[] = $teacherid;}
	//if ($id) {$teacherids[] = $id;}	
		
		//dump ($teacherids['id'], 'tresult: ');
		$teacher = '<table id = "teacher"><tr>';
		if (!isset($teacherids)) {return $teacher;}
		foreach ($teacherids as $teachers)
		
		{
			$database	= & JFactory::getDBO();
			$query = 'SELECT * FROM #__bsms_teachers'.
					'  WHERE id = '.$teachers['id'];
			//dump ($teachers, 'teachers: ');		
			$database->setQuery($query);
			$tresult = $database->loadObject();
			//dump ($tresult, 'tresult: ');
			if ($tresult->teacher_thumbnail == '- Select Image -' || !$tresult->teacher_thumbnail) { $image->path = $tresult->thumb; $image->height = $tresult->thumbh; $image->width = $tresult->thumbw;}
	if ($tresult->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) { $i_path = 'components/com_biblestudy/images/stories/'.$tresult->teacher_thumbnail; }
	if ($tresult->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('teachers_imagefolder').DS.$tresult->teacher_thumbnail;}
	$image = getImage($i_path);
	//dump ($image, 'i_path: ');
			$teacher .= '<td><table cellspacing ="0"><tr><td><img src="'.$image->path.'" border="1" width="'.$image->width.'" height="'.$image->height.'" ></td></tr><tr><td><a href="index.php?option=com_biblestudy&view=teacherdisplay&amp;id='.$tresult->id.'&templatemenuid='.$templatemenuid.'">'.$tresult->teachername.'</a></td></tr></table></td>';
		}
	
		$teacher .= '</tr></table>';

	return $teacher;
}