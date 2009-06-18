<?php defined('_JEXEC') or die('Restriced Access');

function getElementid($rowid, $row, $params)
	{
	 $path1 = JPATH_COMPONENT_SITE.DS.'helpers'.DS;
	include_once($path1.'scripture.php');
	include_once($path1.'duration.php');
	include_once($path1.'date.php');
	include_once($path1.'filesize.php');
	include_once($path1.'textlink.php');
	include_once($path1.'mediatable.php');
	include_once($path1.'store.php');
	include_once($path1.'filepath.php');
	include_once($path1.'elements.php');
	include_once($path1.'custom.php');
	global $mainframe;
	$db	= & JFactory::getDBO();
	
		switch ($rowid)
			{
		 case 1:
			$elementid->id = 'scripture1';
			$elementid->headertext = JText::_('Scripture');
			$esv = 0;
			$scripturerow = 1;
			$elementid->element = getScripture($params, $row, $esv, $scripturerow);
			break;
		case 2:
			$elementid->id = 'scripture2';
			$elementid->headertext = JText::_('Scripture');
			$esv = 0;
			$scripturerow = 2;
			$elementid->element = getScripture($params, $row, $esv, $scripturerow);
			break;
		case 3:
			$elementid->id = 'secondary';
			$elementid->headertext = JText::_('Scripture');
			$elementid->element = $row->secondary_reference;
			break;
		case 4:
			$elementid->id = 'duration';
			$elementid->headertext = JText::_('Duration');
			$elementid->element = getDuration($params, $row);
			break;
		case 5:
			$elementid->id = 'title';
			$elementid->headertext = JText::_('Title');
			$elementid->element = $row->studytitle;
			break;
		case 6:
			$elementid->id = 'studyintro';
			$elementid->headertext = JText::_('Introduction');
			$elementid->element = $row->studyintro;
			break;
		case 7:
			$elementid->id = 'teacher';
			$elementid->headertext = JText::_('Teacher');
			$elementid->element = $row->teachername;
			
			break;
		case 8:
			$elementid->id = 'teacher';
			$elementid->headertext = JText::_('Teacher');
			$elementid->element = $row->teachertitle.' '.$row->teachername;
			break;
		case 9:
			$elementid->id = 'series';
			$elementid->headertext = JText::_('Series');
			$elementid->element = $row->series_text;
			break;
		case 10:
			$elementid->id = 'date';
			$elementid->headertext = JText::_('Date');
			$elementid->element = getstudyDate($params, $row->studydate);
			break;
		case 11:
			$elementid->id = 'submitted';
			$elementid->headertext = JText::_('Submitted By');
			$elementid->element = $row->submitted;
			break;
		case 12:
			$elementid->id = 'hits';
			$elementid->headertext = JText::_('Views');
			$elementid->element = JText::_('Hits: ').$row->hits;
			break;
		case 13:
			$elementid->id = 'studynumber';
			$elementid->headertext = JText::_('StudyNumber');
			$elementid->element = $row->studynumber;
			break;
		case 14:
			$elementid->id = 'topic';
			$elementid->headertext = JText::_('Topic');
			$elementid->element = $row->topic_text;
			break;
		case 15:
			$elementid->id = 'location';
			$elementid->headertext = JText::_('Location');
			$elementid->element = $row->location_text;
			break;
		case 16:
			$elementid->id = 'messagetype';
			$elementid->headertext = JText::_('Message Type');
			$elementid->element = $row->message_type;
			break;
		case 17:
			$elementid->id = 'details';
			$elementid->headertext = JText::_('Details');
			$textorpdf = 'text';
			$elementid->element = getTextlink($params, $row, $textorpdf);
			break;
		case 18:
			$elementid->id = 'details';
			$elementid->headertext = JText::_('Details');
			$textorpdf = 'text';
			$elementid->element = '<table class="detailstable"><tbody><tr><td>';
			$elementid->element .= getTextlink($params, $row, $textorpdf).'</td><td>';
			$textorpdf = 'pdf';
			$elementid->element .= getTextlink($params, $row, $textorpdf).'</td></tr></table>';
			break;
		case 19:
			$elementid->id = 'details';
			$elementid->headertext = JText::_('Details');
			$textorpdf = 'pdf';
			$elementid->element = getTextlink($params, $row, $textorpdf);
			break;
		case 20:
			$elementid->id = 'media';
			$elementid->headertext = JText::_('Media');
			$elementid->element = getMediatable($params, $row);
			break;
		case 22:
			$elementid->id = 'store';
			$elementid->headertext = JText::_('Store');
			$elementid->element = getStore($params, $row);
			break;
		case 23:
			$elementid->id = 'filesize';
			$elementid->headertext = JText::_('Filesize');
			$query_media1 = 'SELECT #__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__mediafiles.mime_type, #__bsms_studies.id AS sid, #__bsms_studies.study_id'
			. ' FROM #__bsms_mediafiles'
			. ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1, AND #__bsms_mediafiles.mime_type = 1';
			$db->setQuery( $query_media1 );
			$media1 = $db->loadObjectList('id');
			$elementid->element = getFilesize($media1->size);
			break;
		case 25:
			$elementid->id = 'thumbnail';
			$elementid->headertext = JText::_('Thumbnail');
			$elementid->element = '<img src="'.$row->thumbnailm.'" width="'.$row->thumbwm.'" height="'.$row->thumbhm.'" alt="'.$row->studytitle.'">';
			break;
		}
		
		return $elementid;
	}
