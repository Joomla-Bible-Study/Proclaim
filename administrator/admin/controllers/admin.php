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
         
            foreach ($results AS $result)
            {
             
                  
                  $param = new JParameter($result->params);
                  $params = $result->params;
                  $player = $param->get('player');
                  //This should be if there is no player set, option 100 from form
                  if (!$player && $from == '100') 
                  {
                   
                    //First let's see if there is anything in the params
                    if (!$result->params)
                    {
                        $query = 'UPDATE #__bsms_mediafiles SET `params` = "player='.$to.'\ninternal_popup=\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n" WHERE `id` = '.$result->id;
                        $db->seQuery($query);
                        $db->query();
                    }
                    //Maybe there are other params but not the player
                    if ($result->params)
                    {
                        //Check to see if there a substring for player=
                        $isplayer = substr_count($params,'player=');
                        if ($isplayer)
                        {
                            $newparams = str_replace('player=\n','player='.$to.'\n',$params);
                        }
                        else
                        {
                            $newparams = 'player='.$to.'\n'.$params;
                        }
                        
                        $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$newparams.'" WHERE `id` = '.$result->id;
                        $db->setQuery($query);
                        $db->query();
                    }
                  }
                  //This should be if there is a player set and it matches the $from in the post
                  
                  if($player == $from)
                  {
                                      
                    $playerposition = strpos($params,'player=');
                    $toposition = $playerposition + 7;
                    $params = substr_replace($params,$to,$toposition, 1);
                 
                    $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$params.'" WHERE `id` = '.$result->id;
                   
                   
                    $db->setQuery($query);
                    $db->query();
                    
                  }
            
            }
    
       
      //  $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
     
    function changePopup()
    {
        
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('pfrom','','post');
        $to = JRequest::getInt('pto','','post');
        
            
            $query = 'SELECT `id`, `params` FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            
            foreach ($results AS $result)
            {
              $params = $result->params;
              $param = new JParameter($result->params);
              $popup = $param->get('internal_popup');
              if (!$popup && $from == '100') 
                  {
                   
                   //If the params field is empty we fill it with blank params plus the internal popup 
                   if (!$result->params)
                       {
                            $query = 'UPDATE #__bsms_mediafiles SET `params` = "player=\ninternal_popup='.$to.'\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=50\npodcasts=-1\n" WHERE `id` = '.$result->id;
                            $db->setQuery($query);
                            $db->query();
                       }
                    //If the param field is not empty we check to see what it has in it.
                   if ($result->params)
                       {
                            //This checks to see if the string internal_popup= exists. If so, we replce it. If not, we put it at the begining of the param
                            $ispopup = substr_count($params,'internal_popup=');
                            if ($ispopop)
                            {
                                $params = str_replace('internal_popup=\n','internal_popup='.$to.'\n',$params);
                            }
                            else
                            {
                                $params = 'internal_popup='.$to.'\n'.$params;
                            }
                        $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$params.'" WHERE `id` = '.$result->id;
                        $db->setQuery($query);
                        $db->query();    
                            
                       } 
                  }
                  //This should be if there is a player set and it matches the $from in the post
                  
                  if($popup == $from)
                  {
                    //In this case we know that the string internal_popup exists in param so we replace only the player
        	           $popupposition = strpos($params,'internal_popup=');
                       $p = $popupposition + 16;
                       $params = substr_replace($params,$to,$p,1);
                       $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$params.'" WHERE `id` = '.$result->id;
                       $db->setQuery($query);
                       $db->query(); 
                  }
            
            }
    
        
      //  $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
}
?>
