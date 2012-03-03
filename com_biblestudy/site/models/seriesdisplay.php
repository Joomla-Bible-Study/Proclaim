<?php
/**
 * @version $Id: seriesdisplay.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');


class BiblestudyModelSeriesdisplay extends JModelItem
{
    /**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_biblestudy.seriesdisplay';
	
        /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('series.id', $pk);

		$offset = JRequest::getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user		= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) &&  (!$user->authorise('core.edit', 'com_biblestudy'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

       
        /**
	 * Method to get study data.
	 *
	 * @param	integer	The id of the study.
	 * @since 7.1.0
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
    function &getItem($pk = null)
    {
        // Initialise variables.
	$pk = (!empty($pk)) ? $pk : (int) $this->getState('series.id');
        if (!isset($this->_item[$pk])) {

            try {
                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    	$query->select($this->getState(
				'item.select', 'se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug')
                                );
                        $query->from('#__bsms_series AS se');
                        //join over teachers
                        $query->select('t.id AS tid, t.teachername, t.title AS teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail');
                        $query->join('LEFT','#__bsms_teachers as t on se.teacher = t.id');
                        $query->where('se.id = ' . (int) $pk);
                        $db->setQuery($query);
			$data = $db->loadObject();
                        if ($error = $db->getErrorMsg()) {
                            throw new Exception($error);
                            }

                            if (empty($data)) {
                                    return JError::raiseError(404, JText::_('JBS_CMN_SERIES_NOT_FOUND'));
                            }
                             
                        $this->_item[$pk] = $data;
                    }
                catch (JException $e)
			{
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
                    
            }
        return $this->_item[$pk];
    }
        /**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
         */



function getTemplate() {
		if(empty($this->_template)) {
			$templateid = JRequest::getVar('t',1,'get', 'int');
			$query = 'SELECT *'
			. ' FROM #__bsms_templates'
			. ' WHERE published = 1 AND id = '.$templateid;
			$this->_template = $this->_getList($query);
		}
		return $this->_template;
	}
 function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}

//end class
}