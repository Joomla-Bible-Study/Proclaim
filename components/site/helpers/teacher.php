<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

function getTeacher($params, $id)
{
	global $mainframe, $option;
	$teacher = null;
	//$templatemenuid = $params->get('templatemenuid');
	$templatemenuid = $params->get('teachertemplateid');
	if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
	

	if ($id > 0) {$teacherids['id'] = $id;}
	else {$teacherids = explode(",", $params->get('mult_teachers'));}
		
		
		//dump ($teacherids['id'], 'tresult: ');
		$teacher = '<table id = "teacher"><tr>';
		foreach ($teacherids as $teachers)
		
		{
			$database	= & JFactory::getDBO();
			$query = 'SELECT * FROM #__bsms_teachers'.
					'  WHERE id = '.$teachers['id'];
			//dump ($teachers, 'teachers: ');		
			$database->setQuery($query);
			$tresult = $database->loadObject();
			//dump ($tresult, 'tresult: ');
			$teacher .= '<td><table cellspacing ="0"><tr><td><img src="'.$tresult->thumb.'" border="1" width="'.$tresult->thumbh.'" height="'.$tresult->thumbw.'" ></td></tr><tr><td><a href="index.php?option=com_biblestudy&view=teacherdisplay&amp;id='.$tresult->id.'&templatemenuid='.$templatemenuid.'">'.$tresult->teachername.'</a></td></tr></table></td>';
		}
	
		$teacher .= '</tr></table>';

	return $teacher;
}