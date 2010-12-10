<?php defined('_JEXEC') or die();

function getStudiesedit($row, $params) {

$studiesedit = '<table><tr>
		<td><strong>'.JText::_('JBS_CMN_STUDIES').'</strong></td>
	</tr>
	<tr>
		<td><a
			href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">'.JText::_('JBS_CMN_ADD_STUDY').'</a></td>
	</tr>
	<tr>
		<td><a
			href="'.JURI::base().'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">'.JText::_('JBS_CMN_ADD_MEDIA').'</a></td>
	</tr>';
	
		$studiesedit .= '<tr><td>
		<a href="'.JURI::base().'index.php?option=com_biblestudy&view=commentslist">'.JText::_('JBS_CMN_MANAGE_COMMENTS').'</a></td>
	</tr>';
	



$studiesedit .= '</tr></table>';
	
return $studiesedit;
}