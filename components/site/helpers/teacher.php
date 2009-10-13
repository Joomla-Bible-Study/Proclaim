<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

function getTeacher($params, $id, $admin_params)
{
	
	global $mainframe, $option;
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'image.php');
	$teacher = null;
	$teacherid = null;
	//$templatemenuid = $params->get('templatemenuid');
	$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	$viewtype = JRequest::getVar('view');
	//dump ($viewtype, 'view: ');
		if ($viewtype == 'studieslist')
			{
				$teacherid = $params->get('listteachers');
				$teacherids = explode(",", $params->get('listteachers'));
			}
		if ($viewtype == 'studydetails')
			{$teacherids[] = $id;}
	//if ($teacherid > 0) {$teacherids['id'] = $teacherid;}
	//if ($params->get('mult_teachers')) { $teacherids = explode(",", $params->get('mult_teachers'));}
	//if ($params->get('listteachers') && $teacherid) {$teacherids = explode(",", $params->get('mult_teachers')); $teacherids[] = $teacherid;}
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
			$i_path = null;
			//dump ($tresult, 'tresult: ');
			//Check to see if there is a teacher image, if not, skip this step
			
			if ($tresult->teacher_thumbnail == '- Select Image -' || !$tresult->teacher_thumbnail) 
				{ 
					$image->path = $tresult->thumb; $image->height = $tresult->thumbh; $image->width = $tresult->thumbw;
				}
			else
			{
				if ($tresult->teacher_thumbnail && !$admin_params->get('teachers_imagefolder')) 
					{ 
						$i_path = 'images/stories/'.$tresult->teacher_thumbnail; 
					}
				if ($tresult->teacher_thumbnail && $admin_params->get('teachers_imagefolder')) 
					{
						$i_path = 'images'.DS.$admin_params->get('teachers_imagefolder').DS.$tresult->teacher_thumbnail;
					}
			$image = getImage($i_path);
				if (!$image) 
					{
						$image->path = ''; $image->width=0; $image->height=0;
					}
			}
				$teacher .= '<td><table cellspacing ="0"><tr><td><img src="'.$image->path.'" border="1" width="'.$image->width.'" height="'.$image->height.'" ></td></tr>';
			
		$teacher .= '<tr><td>';
		if ($params->get('teacherlink') > 0)
			{
				$teacher .= '<a href="index.php?option=com_biblestudy&view=teacherdisplay&amp;id='.$tresult->id.'&templatemenuid='.$templatemenuid.'">';
			}
		$teacher .= $tresult->teachername;
		if ($params->get('teacherlink') > 0)
			{
				$teacher .='</a>';
			}
		$teacher .= '</td></tr></table></td>';
		}
	if ($params->get('intro_show') == 2 && $viewtype == 'studieslist')
		{
			$teacher .= '<td><div id="listintro"><table id="listintro"><tr><td><p>'.$params->get('list_intro').'</p></td></tr></table> </div></td>';
		}
		$teacher .= '</tr></table>';

	return $teacher;
}