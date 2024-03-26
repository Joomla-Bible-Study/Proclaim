<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
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

/**
 * Model class for MessageList
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class CwmpodcastlistModel extends ListModel
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
        $items  = parent::getItems();
        $user   = Factory::getApplication()->getSession()->get('user');
        $userId = $user->get('id');
        $guest  = $user->get('guest');
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
                // If the access filter has been set, we already have only the articles this user can view.
                $item->params->set('access-view', true);
            } elseif ($item->catid == 0 || $item->category_access === null) {
                $item->params->set('access-view', in_array($item->access, $groups));
            } else {
                $item->params->set(
                    'access-view',
                    in_array($item->access, $groups) && in_array($item->category_access, $groups)
                );
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
        $value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $value = $app->input->get('filter_tag', 0, 'uint');
        $this->setState('filter.tag', $value);

        $value = $app->input->get('filter_pc_show', 1, 'uint');
        $this->setState('filter.pc_show', $value);

        $orderCol = $app->input->get('filter_order', 'a.ordering');

        if (!in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = 'a.id';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $params = $app->getParams();

        $user = $app->getSession()->get('user');

        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', 1);
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
            $t = $app->input->get('t', 1, 'int');
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
        $user = Factory::getApplication()->getSession()->get('user');

        // Create a new query object.
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                '*'
            )
        );

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $query->where('a.published = ' . (int)$state);
        } else {
            $query->where('(a.published IN (0,1,2))');
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        $pc_show = $this->getState('filter.pc_show');

        if (is_numeric($pc_show)) {
            $query->where('pc_show = ' . $pc_show);
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where(
                'a.language in (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote(
                    '*'
                ) . ')'
            );
        }

        $query->from('#__bsms_series as a');

        // Add the list ordering clause.
        $query->order($this->getState('list.ordering', 'a.id') . ' ' . $this->getState('list.direction', 'ASC'));

        return $query;
    }
}
