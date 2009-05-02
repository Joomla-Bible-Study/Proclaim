<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

function getTeacher($params, $id)
{
	global $mainframe, $option;
	$teacher = null;
	$teacheritemid = $params->get('teacheritemid');
	$teacherids = explode(",", $params->get('teacherids'));
		
		if ($id > 0) { $teacherids['id'] = $id;}
		//dump ($id, 'tresult: ');
		
		foreach ($teacherids as $teachers)
		
		{
			$database	= & JFactory::getDBO();
			$query = 'SELECT * FROM #__bsms_teachers'.
					'  WHERE id = '.$teachers['id'];
			//dump ($teachers, 'teachers: ');		
			$database->setQuery($query);
			$tresult = $database->loadObject();
			
			$teacher .= '<div class="teacher'.$params->get('pageclass_sfx').'"><img src="'.$tresult->thumb.'" border="1" width="'.$tresult->thumbh.'" height="'.$tresult->thumbw.'" ><br />
			<a href="index.php?option=com_biblestudy&view=teacherdisplay&amp;id='.$tresult->id.'&Itemid='.$teacheritemid.'">'.$tresult->teachername.'</a></div>';
		}
	
	

	return $teacher;
}