<?php defined('_JEXEC') or die('Restriced Access');

/**
 * @author Calvary Chapel Newberg
 * @copyright 2009
 */

function getCustom($custom, $row, $params)
{
	$custom1 = substr($custom, 0); //returns part of a string
	
	
		switch ($custom)
		{
	 case 'scripture1':
		$elementid->id = 'scripture1';
		$esv = 0;
		$scripturerow = 1;
		$elementid->element = getScripture($params, $row, $esv, $scripturerow);
		break;
	case 'scripture2':
		$elementid->id = 'scripture2';
		$esv = 0;
		$scripturerow = 2;
		$elementid->element = getScripture($params, $row, $esv, $scripturerow);
		break;
	case 'secondary':
		$elementid->id = 'secondary';
		$elementid->element = $row->secondary_reference;
		break;
	case 'duration':
		$elementid->id = 'duration';
		$elementid->element = getDuration($params, $row);
		break;
	case 'title':
		$elementid->id = 'title';
		$elementid->element = $row->studytitle;
		break;
	case 'studyintro':
		$elementid->id = 'studyintro';
		$elementid->element = $row->studyintro;
		break;
	case 'teacher':
		$elementid->id = 'teacher';
		$elementid->element = $row->teachername;
		break;
	case 'teacher':
		$elementid->id = 'teacher';
		$elementid->element = $row->teachertitle.' '.$row->teachername;
		break;
	case 'series':
		$elementid->id = 'series';
		$elementid->element = $row->series_text;
		break;
	case 'date':
		$elementid->id = 'date';
		$elementid->element = getstudyDate($params, $row->studydate);
		break;
	case 'submitted':
		$elementid->id = 'submitted';
		$elementid->element = $row->submitted;
		break;
	case 'hits':
		$elementid->id = 'hits';
		$elementid->element = JText::_('Hits: ').$row->hits;
		break;
	case 'studynumber':
		$elementid->id = 'studynumber';
		$elementid->element = $row->studynumber;
		break;
	case 'topic':
		$elementid->id = 'topic';
		$elementid->element = $row->topic_text;
		break;
	case 'location':
		$elementid->id = 'location';
		$elementid->element = $row->location_text;
		break;
	case 'messagetype':
		$elementid->id = 'messagetype';
		$elementid->element = $row->message_type;
		break;
	case 'details':
		$elementid->id = 'details';
		$textorpdf = 'text';
		$elementid->element = getTextlink($params, $row, $textorpdf);
		break;
	case 'details-text-pdf':
		$elementid->id = 'details';
		$textorpdf = 'text';
		$elementid->element = '<table class="detailstable"><tbody><tr><td>';
		$elementid->element .= getTextlink($params, $row, $textorpdf).'</td><td>';
		$textorpdf = 'pdf';
		$elementid->element .= getTextlink($params, $row, $textorpdf).'</td></tr></table>';
		break;
	case 'details-pdf':
		$elementid->id = 'details';
		$textorpdf = 'pdf';
		$elementid->element = getTextlink($params, $row, $textorpdf);
		break;
	case 'media':
		$elementid->id = 'media';
		$elementid->element = getMediatable($params, $row);
		break;
	case 'store':
		$elementid->id = 'store';
		$elementid->element = getStore($params, $row);
		break;
	case 'filesize':
		$elementid->id = 'filesize';
		$query_media1 = 'SELECT #__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__mediafiles.mime_type, #__bsms_studies.id AS sid, #__bsms_studies.study_id'
		. ' FROM #__bsms_mediafiles'
		. ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1, AND #__bsms_mediafiles.mime_type = 1';
		$db->setQuery( $query_media1 );
		$media1 = $db->loadObjectList('id');
		$elementid->element = getFilesize($media1->size);
		break;
	}	
	return $custom;
}

?>