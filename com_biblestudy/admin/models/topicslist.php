<?php

/**
 * @version     $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class biblestudyModeltopicslist extends JModelList {
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