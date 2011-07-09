<?php

/**
 * @version     $Id: templateslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

class biblestudyModelTemplateslist extends modelClass {

    var $_templates;

    function getTemplates() {
        if (empty($this->_templates)) {
            $query = 'SELECT * FROM #__bsms_templates ORDER BY id ASC';
            $this->_templates = $this->_getList($query);
        }
        return $this->_templates;
    }

    /**
     * Gets a list of templates types for the filter dropdown
     *
     * @return <Array>  Array of objects
     * @since   7.0
     */
    public function getTypes() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('template.type AS text');
        $query->from('#__bsms_templates AS template');
        $query->group('template.type');
        $query->order('template.type');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /*
     * @since   7.0
     */

    protected function populateState() {
        $type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
        $this->setState('filter.type', $type);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        
        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
        
        parent::populateState('template.title', 'ASC');
    }

    /**
     * Build and SQL query to load the list data
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select',
                        'template.id, template.type, template.published, template.title'));
        $query->from('#__bsms_templates AS template');

        //Filter by type
        $type = $this->getState('filter.type');
        if (!empty($type))
            $query->where('template.type = ' . $type); //@todo  $type should be escaped to prevent sql injection

        // Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('template.published = ' . (int) $published);
		}
		else if ($published === '') {
			$query->where('(template.published = 0 OR template.published = 1)');
		}
        

        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}

?>