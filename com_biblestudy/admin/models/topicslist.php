<?php

/**
 * @version     $Id: topicslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }
 
class biblestudyModeltopicslist extends modelClass
{
	/**
	 *
	 * @var array
	 */
	var $_data;
	var $allow_deletes = null;


	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function _buildQuery()
	{
		$query = ' SELECT params AS topic_params'
			. ' FROM #__bsms_topics '
		;

		return $query;
	}

	/**
	 * Retrieves the data
	 * @return array Array of objects containing the data from the database
	 */
	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$result = $this->_getList( $query );
			$this->_data = getTopicItemsTranslated($result);
		}

		return $this->_data;
	}
function getDeletes()
	{
		if (empty($this->_deletes)) {
			$query = 'SELECT allow_deletes'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_deletes = $this->_getList($query);
		}
		return $this->_deletes;
	}

        /**
     * @since   7.0
     */
    protected function  populateState() {
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('topic.topic_text', 'ASC');
    }
    /**
     *
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select',
                        'topic.id, topic.topic_text, topic.published, topic.params AS topic_params'));
        $query->from('#__bsms_topics AS topic');

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('topic.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(topic.published = 0 OR topic.published = 1)');
		}
       
        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }
}
?>