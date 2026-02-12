<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
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
use Joomla\Utilities\ArrayHelper;

/**
 * Series model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmseriesModel extends ListModel
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
                'id',
                'series.id',
                'series_text',
                'series.series_text',
                'alias',
                'series.alias',
                'published',
                'series.published',
                'ordering',
                'series.ordering',
                'access',
                'series.access',
                'access_level',
                'language',
                'series.language',
            ];
        }

        parent::__construct($config);
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
    protected function populateState($ordering = 'series.series_text', $direction = 'asc'): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $forcedLanguage = $app->getInput()->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_proclaim');
        $this->setState('params', $params);

        $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', 0, 'int');
        $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');

        // List state information.
        parent::populateState($ordering, $direction);

        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
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
     * @return    string        A store id.
     *
     * @since    1.6
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Build and SQL query to load the list data
     *
     * @return  QueryInterface|string
     *
     * @throws \Exception
     * @since   7.1.0
     */
    protected function getListQuery(): QueryInterface|string
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $user  = $this->getCurrentUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                implode(', ', [
                    $db->quoteName('series.id'),
                    $db->quoteName('series.series_text'),
                    $db->quoteName('series.published'),
                    $db->quoteName('series.alias'),
                    $db->quoteName('series.language'),
                    $db->quoteName('series.access'),
                    $db->quoteName('series.ordering'),
                    $db->quoteName('series.checked_out'),
                    $db->quoteName('series.checked_out_time'),
                ])
            )
        )
            ->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('ag.title', 'access_level'),

                ]
            )
        ->from($db->quoteName('#__bsms_series', 'series'))

        // Join over the language
        ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('series.language'))

        // Join over the asset groups.
        ->join('LEFT', '#__viewlevels AS ag ON ag.id = series.access')

        // Join over the users for the checked out user.
        ->select($db->quoteName('uc.name', 'editor'))
        ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('series.checked_out'));

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('series.language') . ' = :language')
            ->bind(':language', $language);
        }

        // Filter by access level.
        $access = $this->getState('filter.access');

        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('series.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (\is_array($access)) {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('series.access'), $access);
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (($published !== '*') && is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('series.published') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $searchId = (int) substr($search, 3);
                $query->where($db->quoteName('series.id') . ' = :searchId')
                    ->bind(':searchId', $searchId, ParameterType::INTEGER);
            } else {
                $searchTerm = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('series.series_text') . ' LIKE :search1 OR ' .
                    $db->quoteName('series.alias') . ' LIKE :search2)'
                )
                    ->bind([':search1', ':search2'], $searchTerm);
            }
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'series.series_text');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
