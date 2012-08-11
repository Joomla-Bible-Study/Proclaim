<?php

/**
 * TemplateCodes model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Template codes model class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyModelTemplatecodes extends JModelList {

    /**
     * locationslist data array
     *
     * @var array
     */
    public function __construct() {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'templatecode.id',
                'published', 'templatecode.published',
                'type', 'templatecode.type',
                'access', 'templatecode.access',
            );
        }

        parent::__construct();
    }

    /**
     * Populate State
     * @param string $ordering
     * @param string $direction
     * 
     * @since   7.1
     */
    protected function populateState($ordering = null, $direction = null) {

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('templatecode.filename', 'ASC');
    }

    /**
     * Get list query
     * @return array
     */
    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'templatecode.id, templatecode.published, templatecode.filename, templatecode.templatecode, templatecode.type'));
        $query->from('`#__bsms_templatecode` AS templatecode');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('templatecode.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(templatecode.published = 0 OR templatecode.published = 1)');
        }

        return $query;
    }

}