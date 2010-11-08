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
			$msg = JText::_( 'JBS_CMN_SAVED' );
		} else {
			$msg = JText::_( 'JBS_CMN_ERROR_SAVING' );
		}

		switch ($this->_task) {
			case 'apply':
				$msg = JText::_( 'JBS_ADM_CHANGES_UPDATED' );
				$cid 	= JRequest::getVar( 'id', 1, 'post', 'int' );
				$link = 'index.php?option=com_biblestudy&view=admin&layout=form';
				break;

			case 'save':
			default:
				$msg = JText::_( 'JBS_CMN_DATA_SAVED' );
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
		$msg = JText::_( 'JBS_CMN_OPERATION_CANCELLED' );
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
				$msg = JText::_('JBS_ADM_UPDATE_SUCCESSFUL');
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
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_HITS').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
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
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_DOWNLOADS').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
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
                    $msg = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
                    $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
                $msg = JText::_('JBS_CMN_RESET_SUCCESSFUL').' '.$updated.' '.JText::_('JBS_CMN_ROWS_RESET');
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
                   
                   //If the params field is empty we fill it with blank params plus the internal popup 
                   if (!$result->params)
                       {
                            $query = 'UPDATE #__bsms_mediafiles SET `params` = "player='.$to.'\ninternal_popup=\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=\npodcasts=-1\n" WHERE `id` = '.$result->id;
                            $db->setQuery($query);
                            $db->query();
                            if ($db->getErrorNum() > 0)
        				{
        					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
        				}
                        else
                        {
                            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                        }
                       }
                    //If the param field is not empty we check to see what it has in it.
                   if ($result->params)
                       {
                            //This checks to see if the string internal_popup= exists. If so, we replce it. If not, we put it at the begining of the param
                            $ispopup = substr_count($params,'player=');
                            if ($ispopop)
                            {
                                $params = str_replace('player=\n','player='.$to.'\n',$params);
                            }
                            else
                            {
                                $params = 'player='.$to.'\n'.$params;
                            }
                        $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$params.'" WHERE `id` = '.$result->id;
                        $db->setQuery($query);
                        $db->query();  
                        if ($db->getErrorNum() > 0)
            				{
            					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
            				}
                        else
                            {
                                $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                            }  
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
                    if ($db->getErrorNum() > 0)
        				{
        					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
        				}
                        else
                        {
                            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                        }
                    
                  }
            
            }
    
       
      //  $msg = $add.' '.JTEXT::_('JBS_ADM_MEDIAFILES_UPDATED').'<br />'.$errortext;
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
              $popup = $param->get('internal_popup'); //dump ($popup, 'popup: ');
              if (!$popup && $from == '100') 
                  {
                   
                   //If the params field is empty we fill it with blank params plus the internal popup 
                   if (!$result->params)
                       {
                            $query = 'UPDATE #__bsms_mediafiles SET `params` = "player=\ninternal_popup='.$to.'\nplayerwidth=\nplayerheight=\nitempopuptitle=\nitempopupfooter=\npopupmargin=\npodcasts=-1\n" WHERE `id` = '.$result->id;
                            $db->setQuery($query);
                            $db->query();
                            if ($db->getErrorNum() > 0)
        				{
        					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
        				}
                        else
                        {
                            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                        }
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
                        if ($db->getErrorNum() > 0)
            				{
            					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
            				}
                        else
                            {
                                $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                            }  
                       } 
                  }
                  //This should be if there is a player set and it matches the $from in the post
                //  dump ($popup, 'popup: '); dump ($from, 'from: ');
                  if($popup == $from)
                  {
                    //In this case we know that the string internal_popup exists in param so we replace only the player
        	           $popupposition = strpos($params,'internal_popup=');
                       $p = $popupposition + 15;
                       $params = substr_replace($params,$to,$p,1); //dump ($params, 'params: ');
                       $query = 'UPDATE #__bsms_mediafiles SET `params` = "'.$params.'" WHERE `id` = '.$result->id;
                       $db->setQuery($query);
                       $db->query(); 
                       if ($db->getErrorNum() > 0)
        				{
        					$msg = JText::_('JBS_ADM_ERROR_OCCURED').' '.$db->getErrorMsg();
        				}
                        else
                        {
                            $msg = JText::_('JBS_ADM_OPERATION_SUCCESSFUL');
                        }
                  }
            
            }
    
        
      //  $msg = $add.' '.JTEXT::_('JBS_ADM_MEDIAFILES_UPDATED').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
}
?>
