<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

/**
 * Model class for MessageList
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class CwmseriespodcastlistModel extends ListModel
{
    /**
     * Method to get a list of sermons.
     * Overridden to add a check for access levels.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function getItems(): mixed
    {
        $items = parent::getItems();

        // Return empty array if query failed
        if ($items === false) {
            return [];
        }

        $user   = Factory::getApplication()->getIdentity();
        $userId = $user->id;
        $guest  = $user->guest;
        $groups = $user->getAuthorisedViewLevels();

        // Convert the parameter fields into objects.
        foreach ($items as &$item) {
            $item->params = clone $this->getState('params');

            // Compute the asset access permissions.
            // Technically guest could edit an article, but lets not check that to improve performance a little.
            if (!$guest) {
                $asset = 'com_proclaim.series.' . $item->id;

                // Check general edit permission first.
                if ($user->authorise('core.edit', $asset)) {
                    $item->params->set('access-edit', true);
                } elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                    // Check for a valid user and that they are the owner.
                    if ($userId == $item->created_by) {
                        $item->params->set('access-edit', true);
                    }
                }
            }

            $access = $this->getState('filter.access');

            if ($access) {
                // If the access filter has been set, we already have only the series this user can view.
                $item->params->set('access-view', true);
            } else {
                // Series don't have categories, just check item access level
                $item->params->set('access-view', \in_array($item->access, $groups));
            }

            // Get the tags
            if ($item->params->get('show_tags')) {
                $item->tags = new TagsHelper();
                $item->tags->getItemTags('com_proclaim.series', $item->id);
            }
        }

        return $items;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return    void
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null): void
    {
        $app = Factory::getApplication();

        // List state information
        $value = $app->getInput()->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->getInput()->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $value = $app->getInput()->get('filter_tag', 0, 'uint');
        $this->setState('filter.tag', $value);

        $value = $app->getInput()->get('filter_pc_show', 1, 'uint');
        $this->setState('filter.pc_show', $value);

        $orderCol = $app->getInput()->get('filter_order', 'a.ordering');

        if (!\in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = 'a.id';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->getInput()->get('filter_order_Dir', 'ASC');

        if (!\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $params = $app->getParams();

        $user = $app->getIdentity();

        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            // Get show_archived parameter from menu, fall back to template default
            $showArchived = $params->get('show_archived', '');
            if ($showArchived === '' || $showArchived === null) {
                $template_params = Cwmparams::getTemplateparams();
                $showArchived    = $template_params->params->get('sdefault_show_archived', '0');
            }
            $this->setState('filter.show_archived', $showArchived);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());

        // Process show_noauth parameter
        if (!$params->get('show_noauth')) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        $template = Cwmparams::getTemplateparams();
        $admin    = Cwmparams::getAdmin();

        $template->params->merge($params);
        $template->params->merge($admin->params);
        $params = $template->params;

        $t = (int)$params->get('messageid');

        if (!$t) {
            $t = $app->getInput()->get('t', 1, 'int');
        }

        $template->id = $t;

        $this->setState('template', $template);
        $this->setState('params', $params);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  DatabaseQuery  A DatabaseQuery object to retrieve the data set.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function getListQuery(): DatabaseQuery
    {
        // Get the current user for authorization checks
        $user = $this->getCurrentUser();

        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select only the columns we need for performance
        $query->select(
            $this->getState(
                'list.select',
                $db->quoteName(
                    ['a.id', 'a.series_text', 'a.alias', 'a.series_thumbnail', 'a.access', 'a.created_by', 'a.published']
                )
            )
        );

        $query->from($db->quoteName('#__bsms_series', 'a'));

        // Filter by published state based on show_archived parameter
        $showArchived = $this->getState('filter.show_archived', '0');
        switch ($showArchived) {
            case '1': // Archived only
                $archived = 2;
                $query->where($db->quoteName('a.published') . ' = :published')
                    ->bind(':published', $archived, ParameterType::INTEGER);
                break;
            case '2': // Both published and archived
                $query->whereIn($db->quoteName('a.published'), [1, 2]);
                break;
            default: // Published only (backward compatible)
                $published = 1;
                $query->where($db->quoteName('a.published') . ' = :published')
                    ->bind(':published', $published, ParameterType::INTEGER);
                break;
        }

        // Filter by access level.
        if ($this->getState('filter.access')) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        $pc_show = $this->getState('filter.pc_show');

        if (is_numeric($pc_show)) {
            $pc_show = (int) $pc_show;
            $query->where($db->quoteName('pc_show') . ' = :pcShow')
                ->bind(':pcShow', $pc_show, ParameterType::INTEGER);
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $langTag = Factory::getApplication()->getLanguage()->getTag();
            $query->whereIn($db->quoteName('a.language'), [$langTag, '*'], ParameterType::STRING);
        }

        // Add the list ordering clause.
        $query->order(
            $db->escape($this->getState('list.ordering', 'a.id')) . ' ' .
            $db->escape($this->getState('list.direction', 'ASC'))
        );

        return $query;
    }
}
