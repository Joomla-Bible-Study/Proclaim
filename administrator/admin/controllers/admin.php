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
        if ($from == '100')
        {
            $from = '';
            $query = "UPDATE #__bsms_mediafiles SET `params` = 'player=".$to."' WHERE `params` IS NULL";
            $db->setQuery($query);
            $db->query();
            $addnull = $db->getAffectedRows();
            $query = 'SELECT id, params FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $results = $db->loadObjectList();
            foreach ($results AS $result)
            {
                $param = $results->params;
                $noplayer = substr_count($param,'player=');
                if (!$noplayer)
                {
                  $param = $param.'player='.$to.'\n';
                  $query = "UPDATE #__bsms_mediafiles SET `params` = '".$param."' WHERE `id` = ".$result->id;
        	  	  $db->setQuery($query);
        	  	  $db->query();
                  $updated = 0;
                  $updated = $db->getAffectedRows();
        	   	  if ($db->getErrorNum() > 0)
        				{
        					$error = $db->getErrorMsg();
                            $errortext .= JText::_('An error occured while updating mediafile').' '.$result->id.': '.$error.'<br />';
        				}
                  else
        			{
        				$updated = 0;
        				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$add = $add + $updated;
    
        			}
                }
            }
        }
        else
        {
    
            $playerfrom = 'player='.$from;
            $playerto = 'player='.$to;
            $errortext = '';
            $query = 'SELECT id, params FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            $add = 0;
          //  dump ($results, 'results: ');
            foreach ($results AS $result)
            {
                $param = $result->params;
                $isfrom = substr_count($param,$playerfrom);
                if ($isfrom)
                {
                  $param = str_replace($playerfrom,$playerto,$param,$count);
                  $query = "UPDATE #__bsms_mediafiles SET `params` = '".$param."' WHERE `id` = ".$result->id;
        	  	  $db->setQuery($query);
        	  	  $db->query();
                  $updated = 0;
                  $updated = $db->getAffectedRows();
        	   	  if ($db->getErrorNum() > 0)
        				{
        					$error = $db->getErrorMsg();
                            $errortext .= JText::_('An error occured while updating mediafile').' '.$result->id.': '.$error.'<br />';
                        }
                  else
        			{
        				$updated = 0;
        				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$add = $add + $updated;
    
        			}
    
                }
            }
        }
        if ($from == '100') {$add = $add + $addnull;}
        $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
    }
    
    function changePopup()
    {
        
        $db = JFactory::getDBO();
        $msg = null;
        $from = JRequest::getInt('pfrom','','post');
        $to = JRequest::getInt('pto','','post');
        if ($from == '100')
        {
            $from = '';
            $query = "UPDATE #__bsms_mediafiles SET `params` = 'internal_popup=".$to."' WHERE `params` IS NULL";
            $db->setQuery($query);
            $db->query();
            $addnull = $db->getAffectedRows();
            $query = 'SELECT id, params FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $results = $db->loadObjectList();
            foreach ($results AS $result)
            {
                $param = $results->params;
                $noplayer = substr_count($param,'internal_popup=');
                if (!$noplayer)
                {
                  $param = $param.'internal_popup='.$to.'\n';
                  $query = "UPDATE #__bsms_mediafiles SET `params` = '".$param."' WHERE `id` = ".$result->id;
        	  	  $db->setQuery($query);
        	  	  $db->query();
                  $updated = 0;
                  $updated = $db->getAffectedRows();
        	   	  if ($db->getErrorNum() > 0)
        				{
        					$error = $db->getErrorMsg();
                            $errortext .= JText::_('An error occured while updating mediafile').' '.$result->id.': '.$error.'<br />';
        				}
                  else
        			{
        				$updated = 0;
        				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$add = $add + $updated;
    
        			}
                }
            }
        }
        else
        {
    
            $playerfrom = 'internal_popup='.$from;
            $playerto = 'internal_popup='.$to;
            $errortext = '';
            $query = 'SELECT id, params FROM #__bsms_mediafiles';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            $add = 0;
          //  dump ($results, 'results: ');
            foreach ($results AS $result)
            {
                $param = $result->params;
                $isfrom = substr_count($param,$playerfrom);
                if ($isfrom)
                {
                  $param = str_replace($playerfrom,$playerto,$param,$count);
                  $query = "UPDATE #__bsms_mediafiles SET `params` = '".$param."' WHERE `id` = ".$result->id;
        	  	  $db->setQuery($query);
        	  	  $db->query();
                  $updated = 0;
                  $updated = $db->getAffectedRows();
        	   	  if ($db->getErrorNum() > 0)
        				{
        					$error = $db->getErrorMsg();
                            $errortext .= JText::_('An error occured while updating mediafile').' '.$result->id.': '.$error.'<br />';
                        }
                  else
        			{
        				$updated = 0;
        				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$add = $add + $updated;
    
        			}
    
                }
            }
        }
        if ($from == '100') {$add = $add + $addnull;}
        $msg = $add.' '.JTEXT::_('Rows of Media Files updated. Error messages follow if any.').'<br />'.$errortext;
        $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
        
    }
}
?>
