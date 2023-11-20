<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Locations model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationsModel extends ListModel
{
    /**
     * Number of Deletes
     *
     * @var integer
     *
     * @since 7.0
     */
    private $deletes;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since 7.0.0
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'location.id',
                'published',
                'location.published',
                'mesage_type',
                'location.message_type,',
                'ordering',
                'location.ordering',
                'access',
                'location.access',
                'access_level'
            );
        }

        parent::__construct($config);
    }

    /**
     * Get Deletes
     *
     * @return integer|object[]
     *
     * @since 7.0
     */
    public function getDeletes()
    {
        if (empty($this->deletes)) {
            $query         = 'SELECT allow_deletes'
                . ' FROM #__bsms_admin'
                . ' WHERE id = 1';
            $this->deletes = $this->_getList($query);
        }

        return $this->deletes;
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
     * @since   7.1.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
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
     * @throws  \Exception
     * @since   7.0
     */
    protected function populateState($ordering = 'location.id', $direction = 'desc')
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_proclaim');
        $this->setState('params', $params);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int');
        $this->setState('filter.access', $access);

        $formSubmited = $app->input->post->get('form_submited');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        if ($formSubmited) {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }

        // List state information.
        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
    }

    /**
     * Get List Query
     *
     * @return  \Joomla\Database\QueryInterface   A JDatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   12.2
     */
    protected function getListQuery()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getSession()->get('user');

        $query->select(
            $this->getState(
                'list.select',
                'location.id, location.published, location.access, location.location_text'
            )
        );
        $query->from('`#__bsms_locations` AS location');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = location.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $query->where('location.access = ' . (int)$access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.cwmadmin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('location.access IN (' . $groups . ')');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('location.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('location.location_text LIKE ' . $search);
            }
        }

        // Filter by published state
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('location.published = ' . (int)$published);
        } elseif ($published === '') {
            $query->where('(location.published = 0 OR location.published = 1)');
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'location.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
