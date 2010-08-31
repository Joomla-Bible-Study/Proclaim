<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class biblestudyControlleradmin extends JController {
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */

	function __construct()
	{
		parent::__construct();


		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
		$this->registerTask( 'apply',    'save');
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'admin' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('admin');

		if ($model->store($post)) {
			$msg = JText::_( 'Saved!' );
		} else {
			$msg = JText::_( 'Error Saving' );
		}

		switch ($this->_task) {
			case 'apply':
				$msg = JText::_( 'Changes to Admin Settings Updated! (by Apply)' );
				$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
				$link = 'index.php?option=com_biblestudy&view=cpanel';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Data Saved!' );
				//$link = 'index.php?option=com_driver';

				// Check the table in so it can be edited.... we are done with it anyway
				$link = 'index.php?option=com_biblestudy&view=cpanel';
				break;
		}

		// Check the table in so it can be edited.... we are done with it anyway
	//	$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
		$this->setRedirect($link, $msg);
	}

	
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
	}

	function updatesef()
		{
			$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
			include_once($path1.'updatesef.php');
			$update = updateSEF();
			if ($update)
			{
				$this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $update );
			}
			else
			{
				$msg = JText::_('Update successful. No error messages generated.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
			}

		}

	function resetHits()
	{
		$msg = null;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_studies SET hits='0'");
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('An error occured while resetting the hits:').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('Reset successful. No error messages generated.').' '.$updated.' '.JText::_('row(s) reset.');
                $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
			}
	}
function resetDownloads()
	{
		$msg = null;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET downloads='0'");
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('An error occured while resetting the downloads:').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('Reset successful. No error messages generated.').' '.$updated.' '.JText::_('row(s) reset.');
                $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
			}
	}

function resetPlays()
	{
		$msg = null;
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__bsms_mediafiles SET plays='0'");
		$reset = $db->query();
		if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg = JText::_('An error occured while resetting the plays:').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('Reset successful. No error messages generated.').' '.$updated.' '.JText::_('row(s) reset.');
                $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
			}
	}


   function changePlayers()
    {
 
            $db = JFactory::getDBO();
            $msg = null;
            $from = JRequest::getInt('from','','post');
            $to = JRequest::getInt('to','','post');
            
            $errortext = '';
            $query = 'SELECT * FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            $add = 0;
          //  $model = $this->getModel('mediafilesedit');
            JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'tables');
            

          //  dump ($results, 'results: ');
            foreach ($results AS $result)
            {
             
                  
                  $params = new JParameter($result->params);
                  $player = $params->get('player');
                  //This should be if there is no player set, option 100 from form
                  if (!$player && $from == '100') 
                  {
                    $params->set('player', $to);
                    $player = $params->get('player') ;// dump ($params, 'player: ');
                    
                 
                    $_POST['params'] = $params;
                    $_POST['id'] = $result->id; 
               
                 $row =& JTable::getInstance('mediafilesedit', 'Table');
                 if (!$row->bind( JRequest::get( 'post' ) )) 
                 {
                   $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
                if (!$row->store()) 
                {
                    $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 


                  }
                  //This should be if there is a player set and it matches the $from in the post
                  
                  if($player == $from)
                  {
                    $params->set('player', $to); 
                    $player = $params->get('player'); // dump ($player, 'player: ');
                   
                 
                    $_POST['params'] = $params;
                    $_POST['id'] = $result->id; 
                 
                   $row =& JTable::getInstance('mediafilesedit', 'Table');
                    if (!$row->bind( JRequest::get( 'post' ) )) 
                 {
                   $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
                if (!$row->store()) 
                {
                    $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
        	
                  }
            
            }
    
        if ($from == '100') {$add = $add + $addnull;}
      //  $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
     
    function changePopup()
    {
        
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('pfrom','','post');
        $to = JRequest::getInt('pto','','post');
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'tables');
            $errortext = '';
            $query = 'SELECT * FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            $add = 0;
          //  dump ($results, 'results: ');
            foreach ($results AS $result)
            {
              
              $params = new JParameter($result->params);
              $popup = $params->get('internal_popup');
              if (!$popup && $from == '100') 
                  {
                    $params->set('internal_popup', $to);
                    $popup = $params->get('internal_popup') ;// dump ($params, 'player: ');
                    
                 
                    $_POST['params'] = $params;
                    $_POST['id'] = $result->id; 
               
                  
                 $row =& JTable::getInstance('mediafilesedit', 'Table');
                 if (!$row->bind( JRequest::get( 'post' ) )) 
                 {
                   $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
                if (!$row->store()) 
                {
                    $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 


                  }
                  //This should be if there is a player set and it matches the $from in the post
                  
                  if($popup == $from)
                  {
                    $params->set('internal_popup', $to); 
                  
                    $_POST['params'] = $params;
                    $_POST['id'] = $result->id; 
                
                   //  dump ($_POST, 'post: ');
                   $row =& JTable::getInstance('mediafilesedit', 'Table');
                    if (!$row->bind( JRequest::get( 'post' ) )) 
                 {
                   $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
                if (!$row->store()) 
                {
                    $msg = JText::_( 'Error!' );
                    		} else {
                    			$msg = JText::_( 'Saved!' );
                    		} 
        	
                  }
            
            }
    
        if ($from == '100') {$add = $add + $addnull;}
      //  $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
}
?>
