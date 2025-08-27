<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Input\Input;

/**
 * Topics model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtopicsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'topic.id',
                'published',
                'topic.published',
                'topic_text',
                'topic.topic_text',
                'params',
                'topic.params',
            ];
        }

        parent::__construct($config);
    }

    /**
     * translate item entries: books, topics
     *
     * @param   array  $items  Items for entries
     *
     * @return array
     *
     * @since 7.0
     */
    public function getTranslated(array $items): array
    {
        foreach ($items as $item) {
            $item->topic_text = Cwmtranslated::getTopicItemTranslated($item);
        }

        return $items;
    }

    /**
     * Populate State
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   7.0
     */
    protected function populateState($ordering = 'topic.topic_text', $direction = 'ASC')
    {
        // Adjust the context to support modal layouts.
        $input  = new Input();
        $layout = $input->get('layout');

        if ($layout) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Get Stored ID
     *
     * @param   string  $id  A prefix for the store id
     *
     * @return string      A store id
     *
     * @since 7.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * Get List Query
     *
     * @return \Joomla\Database\QueryInterface
     * @since   7.0
     */
    protected function getListQuery()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select(
            $this->getState('list.select', 'topic.id, topic.topic_text, topic.published, topic.params AS topic_params')
        );
        $query->from('#__bsms_topics AS topic');

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('topic.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(topic.topic_text LIKE ' . $search . ')');
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('topic.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(topic.published = 0 OR topic.published = 1)');
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'topic.topic_text');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
