<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;

/**
 * Shared filter-update logic for list views that use filterForm + activeFilters.
 *
 * Requires the consuming class to declare:
 *  - public ?Form   $filterForm
 *  - public ?array  $activeFilters
 *  - protected ?Registry $params
 *
 * @since 10.1.0
 */
trait UpdateFiltersTrait
{
    /**
     * Update filters per landing page call and hide filters per the template settings.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    private function updateFilters(): void
    {
        $input   = Factory::getApplication()->getInput();
        $filters = ['search', 'book', 'teacher', 'series', 'messagetype', 'year', 'topic', 'location', 'language'];
        $lists   = ['fullordering', 'limit'];

        // Fix language filter
        $lang = $this->params->get('listlanguage', 'NO');

        if ($lang !== 'NO') {
            $this->params->set('show_language_search', (int) $lang);
        }

        $this->activeFilters ??= [];

        foreach ($filters as $filter) {
            // Default to 0 explicitly: Input::getInt() hands back null when the request has
            // no such key, and null passes the "was it set?" test below, which would blank
            // out every filter the form had loaded from the user state.
            $set  = $input->getInt('filter_' . $filter, 0);
            $from = $this->filterForm->getValue($filter, 'filter');

            // Update the value from a landing page call.
            if ($set !== 0) {
                $this->filterForm->setValue($filter, 'filter', $set);
                $from = $set;
            }

            // Catch active filters and update them. Keep the shape ListModel::getActiveFilters()
            // uses — an associative array of field name => value — because the searchtools layout
            // keys off it. The filter form is populated from user state, which holds an empty
            // string for every unused filter, so only a real selection counts as active here;
            // 0 is the component's "nothing selected" marker for the id based filters.
            if ($from !== null && $from !== '' && $from !== '0' && $from !== 0 && $from !== []) {
                $this->activeFilters[$filter] = $from;
            }

            // Remove from view if set to hide in the template.
            if ((int) $this->params->get('show_' . $filter . '_search', 1) === 0 && $filter !== 'language') {
                $this->filterForm->removeField($filter, 'filter');
                unset($this->activeFilters[$filter]);
            }
        }

        foreach ($lists as $list) {
            // Remove from view if set to hide in the template.
            if ((int) $this->params->get('show_' . $list . '_search', 1) === 0) {
                $this->filterForm->removeField($list, 'list');
            }
        }
    }
}
