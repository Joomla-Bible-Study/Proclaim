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
            
            switch ($from)
            {
                case '100':
                $query = "UPDATE #__bsms_mediafiles SET `player` = '$to' WHERE `player` IS NULL";
                break;
                
                default:
                $query = "UPDATE #__bsms_mediafiles SET `player` = '$to' WHERE `player` = '$from'"; 
            }
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
                
                    
                    
                  $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
     
    function changePopup()
    {
        
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('pfrom','','post');
        $to = JRequest::getInt('pto','','post');
        $query = "UPDATE #__bsms_mediafiles SET `popup` = '$to' WHERE `popup` = '$from'";
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
               
       $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );    
 }
 
 function changePermissions()
 {
    $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('ppfrom','','post');
        $to = JRequest::getInt('ppto','','post');
        $query = "UPDATE #__bsms_studies SET `show_level` = '$to' WHERE `show_level` = '$from'";
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
               
       $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );    
    
 }
}
?>
