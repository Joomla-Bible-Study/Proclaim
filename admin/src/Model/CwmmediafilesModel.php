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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * MediaFiles model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafilesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws \Exception
     * @since 7.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'mediafile.id',
                'published', 'mediafile.published',
                'ordering', 'mediafile.ordering',
                'studytitle', 'study.studytitle',
                'createdate', 'mediafile.createdate',
                'language', 'mediafile.language',
                'access', 'mediafile.access', 'access_level',
                'language', 'mediafile.language',
                'published', 'mediafile.published',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Manually joins items and returns and nested object array
     *
     * @return mixed  Array  Media files array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getItems(): mixed
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        try {
            // This is to load the server model into the Media Files Variable.
            $serverModel = new CwmserverModel();

            $items = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));

            if (!$items) {
                return false;
            }

            foreach ($items as $item) {
                if (empty($item->serverType)) {
                    $item->serverType = 'legacy';
                }

                $item->serverConfig = $serverModel->getConfig($item->serverType);

                // Convert all JSON strings to Arrays
                $registry = new Registry();
                $registry->loadString($item->params);
                $item->params = $registry;

                $registry2 = new Registry();
                $registry2->loadString($item->metadata);
                $item->metadata = $registry2;
            }

            $this->cache[$store] = $items;
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $this->cache[$store];
    }

    /**
     * Get Stored ID
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since 7.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.mediaYears');
        $id .= ':' . serialize($this->getState('filter.study_id'));
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Get Deletes
     *
     * @return array
     *
     * @since 7.0
     */
    public function getDeletes(): array
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
    protected function populateState($ordering = 'mediafile.createdate', $direction = 'desc'): void
    {
        $app = Factory::getApplication();

        // Force a language
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

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'int');
        $this->setState('filter.access', $access);

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $mediaYears = $this->getUserStateFromRequest($this->context . '.filter.mediaYears', 'filter_mediaYears');
        $this->setState('filter.mediaYears', $mediaYears);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        $formSubmitted = $app->input->post->get('form_submitted');

        // Gets the value of a user state variable and sets it in the session
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');

        if ($formSubmitted) {
            $access = $app->input->post->get('access');
            $this->setState('filter.access', $access);

            $authorId = $app->input->post->get('author_id');
            $this->setState('filter.author_id', $authorId);
        }

        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return QueryInterface|string
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function getListQuery(): QueryInterface|string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = Factory::getApplication()->getIdentity();

        $query->select(
            $this->getState(
                'list.select',
                'mediafile.* '
            )
        );

        $query->from('#__bsms_mediafiles AS mediafile');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = mediafile.language');

        // Join over the studies
        $query->select('study.studytitle AS studytitle');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = mediafile.study_id');

        // Join over servers
        $query->select('server.type as serverType');
        $query->join('LEFT', '#__bsms_servers as server ON server.id = mediafile.server_id');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = mediafile.access');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=mediafile.checked_out');

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (($published !== '*') && is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('mediafile.published') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        // Filter by access level.
        $access = $this->getState('filter.access');

        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('mediafile.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (\is_array($access)) {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('mediafile.access'), $access);
        }

        //        // Implement View Level Access
        //        if (!$user->authorise('core.cwmadmin')) {
        //            $query->whereIn($db->quoteName('mediafile.access'), $user->getAuthorisedViewLevels());
        //        }

        // Filter by study title
        //        $study = $this->getState('filter.study_id');
        //
        //        if (!empty($study)) {
        //            $query->where('mediafile.study_id LIKE "%' . $study . '%"');
        //        }

        // Filter by media years
        $mediaYears = $this->getState('filter.mediaYears');

        if (!empty($mediaYears)) {
            $query->where('YEAR(mediafile.createdate) = ' . (int)$mediaYears);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('mediafile.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('study.studytitle') . ' LIKE :search1 OR ' . $db->quoteName('study.alias') . ' LIKE :search2)'
                )
                    ->bind([':search1', ':search2'], $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('mediafile.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'mediafile.createdate');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        // Sqlsrv change
        if ($orderCol === 'study_id') {
            $orderCol = 'mediafile.study_id';
        }

        if ($orderCol === 'ordering') {
            $orderCol = 'mediafile.study_id, mediafile.ordering';
        }

        if ($orderCol === 'published') {
            $orderCol = 'mediafile.published';
        }

        if ($orderCol === 'id') {
            $orderCol = 'mediafile.id';
        }

        if ($orderCol === 'mediafile.ordering') {
            $orderCol = 'mediafile.study_id ' . $orderDirn . ', mediafile.ordering';
        }

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
