<?php defined('_JEXEC') or die('Restriced Access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
function getElementid($rowid, $row, $params, $admin_params, $template)
	{
	$elementid = null;
	 $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
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
	include_once($path1.'image.php');
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
			$elementid->element = getTextlink($params, $row, $textorpdf, $admin_params, $template);
			break;
		case 18:
			$elementid->id = 'details';
			$elementid->headertext = JText::_('Details');
			$textorpdf = 'text';
			$elementid->element = '<table class="detailstable"><tbody><tr><td>';
			$elementid->element .= getTextlink($params, $row, $textorpdf, $admin_params, $template).'</td><td>';
			$textorpdf = 'pdf';
			$elementid->element .= getTextlink($params, $row, $textorpdf, $admin_params, $template).'</td></tr></table>';
			break;
		case 19:
			$elementid->id = 'details';
			$elementid->headertext = JText::_('Details');
			$textorpdf = 'pdf';
			$elementid->element = getTextlink($params, $row, $textorpdf, $admin_params, $template);
			break;
		case 20:
            $mediaclass = new jbsMedia(); 
			$elementid->id = 'media';
			$elementid->headertext = JText::_('Media');
		//	$elementid->element = getMediatable($params, $row, $admin_params);
            $elementid->element = $mediaclass->getMediaTable($row, $params, $admin_params);
        //    $elementid->element = $mediaclass->getMediaid($row->id);
            dump ($elementid->element, 'elementid: ');
        
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
			
			//$i_path = ($admin_params->get('study_images') ? 'images/'.$admin_params->get('study_images') : 'images/'.'stories');
			if ($row->thumbnailm) 
			{
				$images = new jbsImages(); 
				$image = $images->getStudyThumbnail($row->thumbnailm);
			//	$i_image = $row->thumbnailm;
			//	$i_path = $i_path.'/'.$i_image;
			//	$image = getImage($i_path);
    			$elementid->element = '<img src="'.JURI::base().$image->path.'" width="'.$image->width.'" height="'.$image->height.'" alt="'.$row->studytitle.'">';
			}
			else {$elementid->element = '';}
			break;
		case 26:
			$elementid->id = 'series_thumbnail';
			$elementid->headertext = JText::_('Thumbnail');
		//	$i_path = ($admin_params->get('series_imagefolder') ? 'images/'.$admin_params->get('series_imagefolder') : 'images/stories');
			if ($row->series_thumbnail) 
			{
				$images = new jbsImages(); 
				$image = $images->getSeriesThumbnail($row->series_thumbnail); // dump ($image, 'image: ');
			//	$i_image = $row->series_thumbnail;
			//	$i_path = $i_path.'/'.$i_image;
			//	$image = getImage($i_path);
    			$elementid->element = '<img src="'.JURI::base().$image->path.'" width="'.$image->width.'" height="'.$image->height.'" alt="'.$row->series_text.'">';
			}
			else {$elementid->element = '';}
			break;
		case 27:
			$elementid->id = 'series_description';
			$elementid->headertext = JText::_('Description');
			$elementid->element = $row->sdescription; //dump ($row->sdescription, 'sdescription: ');
			//dump ($element->element, 'element: ');
			break;
        case 28:
            $elementid->id = 'plays';
            $elementid->headertext = JText::_('Plays');
            $elementid->element = $row->totalplays;
            break;
        case 29:
            $elementid->id = 'downloads';
            $elementid->headertext = JText::_('Downloads');
            $elementid->element = $row->totaldownloads;
            break;
        case 30:
        	$elemntid->id = 'teacher-image';
        	$elemtnid->headetext = JText::_('Teacher Image');
        	$query = "SELECT thumb FROM #__bsms_teachers WHERE id = $row->id";
        	$db->setQuery($query);
   			$thumb = $db->loadObject();
        	$elementid->element = '<img src="'.$thumb->thumb.'"/>';
        	break;
		case 100:
			$elementid->id = '';
			$elementid->headertext = '';
			$elementid->element = '';
			break;
		}
		//dump ($elementid, 'elementid: ');
		return $elementid;
	}
