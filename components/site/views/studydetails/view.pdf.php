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
		$df = 	($params->get('date_format'));
	switch ($df)
		{
			case 0:
				$date	= date('M j, Y', strtotime($studydetails->studydate));
			break;
			case 1:
				$date	= date('M j', strtotime($studydetails->studydate) );
			break;
			case 2:
				$date	= date('n/j/Y',  strtotime($studydetails->studydate));
			break;
			case 3:
				$date	= date('n/j', strtotime($studydetails->studydate));
			break;
			case 4:
				$date	= date('l, F j, Y',  strtotime($studydetails->studydate));
			break;
			case 5:
				$date	= date('F j, Y',  strtotime($studydetails->studydate));
			break;
			case 6:
				$date = date('j F Y', strtotime($studydetails->studydate));
			break;
			case 7:
				$date = date('j/n/Y', strtotime($studydetails->studydate));
			break;
			case 8:
				$date = JHTML::_('date', $studydetails->studydate, JText::_('DATE_FORMAT_LC'));
			break; 
			default:
				$date = date('n/j', strtotime($studydetails->studydate));
			break;
		}
		$document->setModifiedDate($date);
		//$document->setMetaData('keywords', $article->metakey);

		// prepare header lines
		$document->setHeader($this->_getHeaderText($studydetails, $params));
		echo $studydetails->studytext;

function format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e) {
global $mainframe, $scripture, $option;
$params =& $mainframe->getPageParameters();
//$params = &JComponentHelper::getParams($option);
$db	= & JFactory::getDBO();
$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = '.$booknumber;
$db->setQuery($query);
$bookresults = $db->loadObject();
$book=$bookresults->bookname;
$b1 = ' ';
$b2 = ':';
$b2a = ':';
$b3 = '-';
$b3a = '-';

	$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		if ($ch_e == $ch_b) {
			$ch_e = '';
			$b2a = '';
			}
		if ($v_b == 0){
			$v_b = '';
			$v_e = '';
			$b2a = '';
			$b2 = '';
			}
		if ($v_e == 0) {
			$v_e = '';
			$b2a = '';
			}
		if ($ch_e == 0) {
			$b2a = '';
			$ch_e = '';
				if ($v_e == 0) {
					$b3 = '';
				}
			}
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
	
	
return $scripture;
}	  


$booknumber = $studydetails->booknumber;
$ch_b = $studydetails->chapter_begin;
$ch_e = $studydetails->chapter_end;
$v_b = $studydetails->verse_begin;
$v_e = $studydetails->verse_end;
$scripture1 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e);

$booknumber = $studydetails->booknumber2;
$ch_b = $studydetails->chapter_begin2;
$ch_e = $studydetails->chapter_end2;
$v_b = $studydetails->verse_begin2;
$v_e = $studydetails->verse_end2;
$scripture2 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e);		

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


function format_scripture2($booknumber, $ch_b, $ch_e, $v_b, $v_e) {
global $mainframe, $scripture, $option;
$params =& $mainframe->getPageParameters();
//$params = &JComponentHelper::getParams($option);
$db	= & JFactory::getDBO();
$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = '.$booknumber;
$db->setQuery($query);
$bookresults = $db->loadObject();
$book=$bookresults->bookname;
$b1 = ' ';
$b2 = ':';
$b2a = ':';
$b3 = '-';
$b3a = '-';
if ($params->get('show_verses') >0)
	{
	$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		if ($ch_e == $ch_b) {
			$ch_e = '';
			$b2a = '';
			}
		if ($v_b == 0){
			$v_b = '';
			$v_e = '';
			$b2a = '';
			$b2 = '';
			}
		if ($v_e == 0) {
			$v_e = '';
			$b2a = '';
			}
		if ($ch_e == 0) {
			$b2a = '';
			$ch_e = '';
				if ($v_e == 0) {
					$b3 = '';
				}
			}
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
	}
	else 
	{
		if ($ch_e > $ch_b) {
			$scripture = $book.$b1.$ch_b.$b3.$ch_e;
			}
			else {
			$scripture = $book.$b1.$ch_b;
			}
	}
return $scripture;
}	  	
$booknumber = $studydetails->booknumber;
$ch_b = $studydetails->chapter_begin;
$ch_e = $studydetails->chapter_end;
$v_b = $studydetails->verse_begin;
$v_e = $studydetails->verse_end;
$scripture1 = format_scripture2($booknumber, $ch_b, $ch_e, $v_b, $v_e);

$booknumber = $studydetails->booknumber2;
$ch_b = $studydetails->chapter_begin2;
$ch_e = $studydetails->chapter_end2;
$v_b = $studydetails->verse_begin2;
$v_e = $studydetails->verse_end2;
$scripture2 = format_scripture2($booknumber, $ch_b, $ch_e, $v_b, $v_e);	
			$text .= $scripture1.' - '.$scripture2;
			
			if ($studydetails->secondary_reference) { $text .= ' - '.$studydetails->secondary_reference; }
			if ($params->get('show_teacher_view')) 
			{
				$text .= "\n";
				$text .= JText::_('By').' '. ($studydetails->tname);
			}

		if ($params->get('show_date_view')) 
			{
			// Display Created Date
				$date = JHTML::_('date', $studydetails->studydate, JText::_('DATE_FORMAT_LC2'));
				$text .="\n";
				$text .= $date;
			}
			return $text;
		}
	
	}	
	

?>