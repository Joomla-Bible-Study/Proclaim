<?php

/**
 * @version $Id: serieslist.php 1 $
 * @package BibleStudy
 * @since 7.1.0
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


class biblestudyModelserieslist extends JModelList {

   
   
    /**
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);
        $series = $this->getUserStateFromRequest($this->context . '.filter.series', 'filter_series');
        $this->setState('filter.series', $series);
        parent::populateState('study.studydate', 'DESC');
    }


   
/**
     * Build an SQL query to load the list data
     *
     * @return  JDatabaseQuery
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $template_params = $this->getTemplate();
        $registry = new JRegistry;
        $registry->loadJSON($template_params->params);
        $t_params = $registry;
        $app = JFactory::getApplication('site');
        $params = $app->getParams();
        $menuparams = new JRegistry;

        if ($menu = $app->getMenu()->getActive()) {
            $menuparams->loadString($menu->params);
        }
        $query = $db->getQuery(true);
        $query->select('se.*,CASE WHEN CHAR_LENGTH(se.alias) THEN CONCAT_WS(\':\', se.id, se.alias) ELSE se.id END as slug');
        $query->from('#__bsms_series as se');
        $query->select('t.id as tid, t.teachername, t.title as teachertitle, t.thumb, t.thumbh, t.thumbw, t.teacher_thumbnail');
        $query->join('LEFT','#__bsms_teachers as t on se.teacher = t.id');
        $where = $this->_buildContentWhere();
       // $orderby = $this->_buildContentOrderBy();
        $query->where($where);
         $orderparam = $params->get('default_order');
        if (empty($orderparam)) {
            $orderparam = $t_params->get('default_order', '1');
        }
        if ($orderparam == 2) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }
        $orderstate = $this->getState('filter.order');
        if (!empty($orderstate))
            $order = $orderstate;

        $query->order('series_text ' . $order);
       // $query->order($orderby);
        return $query;
        
    }
 

    function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT *'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }


    
     /**
     * @desc Returns the Template to display the list
     * @return Array
     * @since 7.0.2
     */
    function getTemplate() {
        if (empty($this->_template)) {
            $templateid = JRequest::getVar('t', 1, 'get', 'int');
            $db = $this->getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__bsms_templates');
            $query->where('published = 1 AND id = ' . $templateid);
            $db->setQuery($query->__toString());
            $this->_template = $db->loadObject();
        }
        return $this->_template;
    }

   
    function _buildContentWhere() {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $params = JComponentHelper::getParams($option);
        $default_order = $params->get('default_order');
        $filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
        $filter_orders = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');
        $where = array();
        $where[] = ' se.published = 1';

        if ($filter_series > 0) {
            $where[] = ' se.id = ' . (int) $filter_series;
        }



        $where = ( count($where) ? implode(' AND ', $where) : '' );

        $where2 = array();
        $continue = 0;
        if ($params->get('series_id') && !$filter_series) {

            $filters = $params->get('series_id');
            switch ($filters) {
                case is_array($filters) :
                    foreach ($filters AS $filter) {
                        if ($filter == -1) {
                            break;
                        } {
                            $continue = 1;
                            $where2[] = 'se.id = ' . (int) $filter;
                        }
                    }
                    break;

                case -1:
                    break;

                default:
                    $continue = 1;
                    $where2[] = 'se.id = ' . (int) $filters;
                    break;
            }
        }
        $where2 = ( count($where2) ? ' ' . implode(' OR ', $where2) : '' );

        if ($continue > 0) {
            $where = $where . ' AND ( ' . $where2 . ')';
        }
        return $where;
    }

}