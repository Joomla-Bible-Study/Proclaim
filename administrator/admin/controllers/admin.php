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
				$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
				break;

			case 'save':
			default:
				$msg = JText::_( 'Data Saved!' );
				//$link = 'index.php?option=com_driver';

				// Check the table in so it can be edited.... we are done with it anyway
				$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
				break;
		}
		
		// Check the table in so it can be edited.... we are done with it anyway
	//	$link = 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('booksedit');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Books Could not be Deleted' );
		} else {
			$msg = JText::_( 'Book(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist', $msg );
	}
function publish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('booksedit');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist' );
	}


	function unpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('booksedit');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_biblestudy&view=bookslist' );
	}
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
	}

	function updatesef()
		{
			$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
			include_once($path1.'updatesef.php');
			$update = updateSEF();
			if ($update)
			{
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $update );	
			}
			else
			{
				$msg = JText::_('Update successful. No error messages generated.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
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
					$msg = 'An error occured while resetting the hits: '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
				$msg = JText::_('Reset successful. No error messages generated. '.$updated.' row(s) reset.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
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
					$msg = 'An error occured while resetting the downloads: '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
				$msg = JText::_('Reset successful. No error messages generated. '.$updated.' row(s) reset.');
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
					$msg = 'An error occured while resetting the plays: '.$error;
					$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
				}
		else
			{
				$updated = $db->getAffectedRows();
				$msg = JText::_('Reset successful. No error messages generated. '.$updated.' row(s) reset.');
				$this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
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
    					$errortext .= 'An error occured while updating mediafile '.$result->id.': '.$error.'<br />';
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
    					$errortext .= 'An error occured while updating mediafile '.$result->id.': '.$error.'<br />';
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
    $msg = $add.' Rows of Media Files updated. Error messages follow if any<br />'.$errortext;
    $this->setRedirect( 'index.php?option=com_biblestudy&view=admin&controller=admin&layout=form', $msg );
}	
}
?>
