<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
$uri 		=& JFactory::getURI();
//$pathway	=& $mainframe->getPathway();

class biblestudyViewstudydetails extends JView
{
	
	function display($tpl = null)
	{
		//TF added
		global $mainframe, $option;
		//$dispatcher	   =& JDispatcher::getInstance();
		$document =& JFactory::getDocument();
		$pathway	   =& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_biblestudy' );
		$dispatcher	=& JDispatcher::getInstance();
		// Get the menu item object
		//$menus = &JMenu::getInstance();
		$menu =& JSite::getMenu();
		$item =& $menu->getActive();
		//$params = &JComponentHelper::getParams($option);
		//$params = &$mainframe->getPageParameters();
		$params		=& $mainframe->getParams('com_biblestudy');
		//$this->assignRef('params', $params);
		//end TF added
		$studydetails		=& $this->get('Data');
		
		//Begin test of using helper
		$scripture = Jview::loadHelper('scripture');
		//$scripture = getScripture(); 	
		dump ($scripture, 'Scripture: ');
		//End helper test
		
		//We pick up the variable to show media in view - this is only used in the view.pdf.php. Here we simply pass the variable to the default template
		$show_media = $contentConfig->get('show_media_view');
		$this->assignRef('show_media', $show_media);
		
		//Added database queries from the default template - moved here instead
		$database	= & JFactory::getDBO();
		$query = "SELECT id"
			. "\nFROM #__menu"
			. "\nWHERE link ='index.php?option=com_biblestudy&view=studieslist' and published = 1";
		$database->setQuery($query);
		$menuid = $database->loadResult();
		$this->assignRef('menuid',$menuid);
		$query = 'SELECT c.* FROM #__bsms_comments AS c WHERE c.published = 1'
		.' AND c.study_id = '.$this->studydetails->id.' ORDER BY c.comment_date ASC';
		$database->setQuery($query);
		$comments = $database->loadObjectList();
		$this->assignRef('comments', $comments);
		
		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}
		$print = JRequest::getBool('print');
		// build the html select list for ordering
		
		/*
		 * Process the prepare content plugins
		 */
		$article->text = $studydetails->studytext;
		$linkit = $params->get('show_scripture_link');
		if ($linkit) {
			switch ($linkit) 
			{
			case 0:
				break;
			case 1:
				JPluginHelper::importPlugin('content');
				break;
			case 2:
				JPluginHelper::importPlugin('content', 'scripturelinks');
				break;
			}
			$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));
			$article->studytext = $article->text;
			
		} //end if $linkit
                // End process prepare content plugins
//Formats the scripture for the scripture links plugin
		$ch_b = $studydetails->chapter_begin;
                $v_b = $studydetails->verse_begin;
                $ch_e = $studydetails->chapter_end;
                $v_e = $studydetails->verse_end;
                $book = $studydetails->bname;
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
		$plugin =& JPluginHelper::getPlugin('content', 'scripturelinks');
 		$st_params 	= new JParameter( $plugin->params );
		$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
		$version = $st_params->get('bible_version');
		$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
		$passage_link = '<a href="http://bible.gospelcom.net/passage/?search='.$scripture.';&version='.$version.'" target="_blank" onclick="'.$windowopen.'">'.$scripture.'</a>';
		
		//$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));
		//$database	= & JFactory::getDBO();
		$this->assignRef('print', $print);
		$this->assignRef('params' , $params);	
		$this->assignRef('studydetails',		$studydetails);
		$this->assignRef('article', $article);
  $this->assignRef('passage_link', $passage_link);
		parent::display($tpl);
	}
	function _displayPagebreak($tpl)
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('PGB ARTICLE PAGEBRK'));
		parent::display($tpl);
	}
}
?>