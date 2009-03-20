<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );



class biblestudyViewstudydetails extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$dispatcher	=& JDispatcher::getInstance();

		// Initialize some variables
		//$article	= & $this->get( 'Article' );
		$studydetails		=& $this->get('Data');
		//$params 	= & $article->parameters;
		$params = &$mainframe->getPageParameters();
		//$params = &JComponentHelper::getParams($option);
		// process the new plugins
		JPluginHelper::importPlugin('content', 'image');
		$dispatcher->trigger('onPrepareContent', array (& $studydetails, & $params, 0));

		$document = &JFactory::getDocument();

		// set document information
		$document->setTitle($studydetails->studytitle);
		$document->setName($studydetails->studytitle);
		$document->setDescription($studydetails->studyintro);
		
                

		$document->setModifiedDate($date);
		//$document->setMetaData('keywords', $article->metakey);

		// prepare header lines
		$document->setHeader($this->_getHeaderText($studydetails, $params));
		echo $studydetails->studytext;
		

		 //code added to provide Scripture reference at bottom 
 if ($params->get('show_passage_view') > 0) { 
 if ($scripture1) { 
	

   $key = "IP";
  $passage = urlencode($scripture1);
  $options = "include-passage-references=false";
  $url = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";

  $p = (get_extension_funcs("curl")); // This tests to see if the curl functions are there. It will return false if curl not installed
  if ($p) { // If curl is installed then we go on
  $ch = curl_init($url); // This will return false if curl is not enabled
  if ($ch) { //This will return false if curl is not enabled

  $ch = curl_init($url); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $response = curl_exec($ch);
  curl_close($ch);
  echo '<br /><hr /><br />';
  echo "".$scripture1." (ESV)";
  print $response;
  } // End of if ($ch)
  } // End if ($p)
		
	 } 
	} // end of if show_passage_view 

	}

	function _getHeaderText(& $studydetails, & $params)
	{
		// Initialize some variables
		$text = '';
		
		
		//return $text;
	//}
	
$scripture_call = Jview::loadHelper('scripture');
$show_verses = $params->get('show_verses');
$booknumber = $studydetails->booknumber;
$ch_b = $studydetails->chapter_begin;
$ch_e = $studydetails->chapter_end;
$v_b = $studydetails->verse_begin;
$v_e = $studydetails->verse_end;
$id2 = $studydetails->id;
$scripture1 = format_scripture2($id2, $esv, $booknumber, $ch_b, $ch_e, $v_b, $v_e, $show_verses);

$booknumber = $studydetails->booknumber2;
$id2 = $studydetails->id;
$ch_b = $studydetails->chapter_begin2;
$ch_e = $studydetails->chapter_end2;
$v_b = $studydetails->verse_begin2;
$v_e = $studydetails->verse_end2;
$scripture2 = format_scripture2($id2, $esv, $booknumber, $ch_b, $ch_e, $v_b, $v_e, $show_verses);
if (!$studydetails->booknumber2) {$text .= $scripture1;}
else {$text .= $scripture1.' - '.$scripture2;}
			
			if ($studydetails->secondary_reference) { $text .= ' - '.$studydetails->secondary_reference; }
			if ($params->get('show_teacher_view')) 
			{
				$text .= "\n";
				$text .= JText::_('By').' '. ($studydetails->tname);
			}

		if ($params->get('show_date_view')) 
			{
			// Display Created Date
				$df = 	($params->get('date_format'));
				$date_call = JView::loadHelper('date');
 				$date = getstudyDate($df, $studydetails->studydate);		
				//$date = JHTML::_('date', $studydetails->studydate, JText::_('DATE_FORMAT_LC2') , '$offset');
				$text .="\n";
				$text .= $date;
			}
			return $text;
		}
	
	}	
	

?>