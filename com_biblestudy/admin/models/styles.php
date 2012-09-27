<?php

/**
 * Styles model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Import library dependencies for CSS Fix
JLoader::register('JBS710Update', JPATH_ADMINISTRATOR . '/components/com_biblestudy/install/updates/update710.php');

jimport('joomla.application.component.modellist');

/**
 * Styles list model class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyModelStyles extends JModelList {

    /**
     * locationslist data array
     *
     * @var array
     */
    public function __construct() {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'style.id',
                'published', 'style.published',
                'ordering', 'style.ordering',
                'access', 'style.access',
            );
        }

        parent::__construct();
    }

    /**
     * Populate State.
     *
     * @param string $ordering
     * @param string $direction
     * @since 7.1.0
     */
    protected function populateState($ordering = null, $direction = null) {

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState('style.filename', 'ASC');
    }

    /**
     * Get List Qurey
     * @return string
     */
    protected function getListQuery() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'style.id, style.published, style.filename, style.stylecode'));
        $query->from('`#__bsms_styles` AS style');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('style.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(style.published = 0 OR style.published = 1)');
        }

        return $query;
    }

    /**
     * Fix css
     * @return boolean if it fixed.
     * @since 7.1.0
     */
    public function fixcss() {
        /*
         * need to find a way to put this pull css file to fix.
         */
        $id = null;
        $filename = 'biblestudy';
        $parent = FALSE;
        JBS710Update::fixupcss($filename, $parent , null , $id);
    }

}