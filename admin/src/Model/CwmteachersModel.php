<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Teachers model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteachersModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                                             $config   An optional associative array of configuration settings.
     * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null  $factory  Factory
     *
     * @throws \Exception
     * @since      1.7.0
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'teacher.id',
                'title',
                'teacher.title',
                'catid',
                'teacher.catid',
                'access',
                'teacher.access',
                'access_level',
                'published',
                'teacher.published',
                'ordering',
                'teacher.ordering',
                'teahername',
                'teacher.teachername',
                'alias',
                'teacher.alias',
                'language',
                'teacher.language',
                'access',
                'teacher.access',
                'access_level'
            );
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function populateState($ordering = 'teacher.teachername', $direction = 'asc')
    {
        $app = Factory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $access);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since 7.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\QueryInterface
     *
     * @since   7.0
     */
    protected function getListQuery()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getSession()->get('user');

        $query->select($this->getState('list.select', 'teacher.*'));
        $query->from('#__bsms_teachers AS teacher');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', '`#__languages` AS l ON l.lang_code = teacher.language');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = teacher.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('teacher.access = ' . (int)$access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.cwmadmin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('teacher.access IN (' . $groups . ')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('teacher.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(teacher.published = 0 OR teacher.published = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('teacher.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(teacher.teachername LIKE ' . $search . ' OR teacher.alias LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'teacher.teachername');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
