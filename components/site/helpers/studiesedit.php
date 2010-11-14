<?php defined('_JEXEC') or die();

function getStudiesedit($row, $params) {

$studiesedit = '<table><tr>
		<td><strong>'.JText::_('Studies').'</strong></td>
	</tr>
	<tr>
		<td><a
			href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">'.JText::_('Add a New Study').'</a></td>
	</tr>
	<tr>
		<td><a
			href="'.JURI::base().'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">'.JText::_('Add a New Media File Record').'</a></td>
	</tr>';
	
		$studiesedit .= '<tr><td>
		<a href="'.JURI::base().'index.php?option=com_biblestudy&view=commentslist">'.JText::_('Manage Comments').'</a></td>
	</tr>';
	


// Here we start the test to see if podcast entry allowed
		
				$database = & JFactory::getDBO();
				$query = ('SELECT id, title, published FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC');
				$database->setQuery( $query );
				$podcasts = $database->loadAssocList();
				
	$studiesedit .= '<tr>
		<td><strong>'.JText::_('JBS_CMN_PODCASTS').'</td></tr></strong>
		<tr>
		<td>
		<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form'.'">'.JText::_('Add A Podcast').'</a></td>
	</tr>
	<tr>
		<td>';
		foreach ($podcasts as $podcast) 
		{ 
			$pod = $podcast['id']; 
			$podtitle = $podcast['title']; 
			$studiesedit .= 
			'<tr>
			<td><a href="'.JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form&task=edit&cid[]='.$pod.'">'.$podtitle.'</a></td>
		</tr>';
	 	} // end foreach for podcasts as podcast
	$studiesedit .= '</td>
	</tr></table>';
	
return $studiesedit;
}