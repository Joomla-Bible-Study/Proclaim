,<?php


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
		$templatemenuid = $params->get('templatemenuid');
		if (!$templatemenuid){$templatemenuid = 1;}
		JRequest::setVar( 'templatemenuid', $templatemenuid, 'get');
		$template = $this->get('Template');
		$params = new JParameter($template[0]->params);
		$studydetails		=& $this->get('Data');
		$admin =& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'date.php');
		include_once($path1.'scripture.php');
		JPluginHelper::importPlugin('content', 'image');
		$dispatcher->trigger('onPrepareContent', array (& $studydetails, & $params, 0));

		$document = &JFactory::getDocument();

		// set document information
		$document->setTitle($studydetails->studytitle);
		$document->setName($studydetails->studytitle);
		$document->setDescription($studydetails->studyintro);
		
                
		$date = getstudyDate($params, $studydetails->studydate);
		$document->setModifiedDate($date);
		//$document->setMetaData('keywords', $article->metakey);

		// prepare header lines
		$document->setHeader($this->_getHeaderText($studydetails, $params));
		echo $studydetails->studytext;
		

		 //code added to provide Scripture reference at bottom 
 if ($params->get('show_passage_view') > 0) { 
 if (isset($scripture1)) { 
	

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
	
//$scripture_call = Jview::loadHelper('scripture');

$scripture1 = getScripture($params, $studydetails, $esv=0, $scripturerow=1);

$scripture2 = getScripture($params, $studydetails, $esv=0, $scripturerow=2);

if (!$studydetails->booknumber2) {$text .= $scripture1;}
else {$text .= $scripture1.' - '.$scripture2;}
			
			if ($studydetails->secondary_reference) { $text .= ' - '.$studydetails->secondary_reference; }
			if ($params->get('show_teacher_view')) 
			{
				$text .= "\n";
				$text .= JText::_('By').' '. ($studydetails->tname);
			}

		
			// Display Created Date
				$date_call = JView::loadHelper('date');
 				$date = getstudyDate($params, $studydetails->studydate);		
				//$date = JHTML::_('date', $studydetails->studydate, JText::_('DATE_FORMAT_LC2') , '$offset');
				$text .="\n";
				$text .= $date;
			
			return $text;
		}
	
	}	
	

?>