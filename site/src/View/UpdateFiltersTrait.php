<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View;

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

        foreach ($filters as $filter) {
            $set  = $input->getInt('filter_' . $filter);
            $from = $this->filterForm->getValue($filter, 'filter');

            // Update the value from a landing page call.
            if ($set !== 0) {
                $this->filterForm->setValue($filter, 'filter', $set);
            }

            // Catch active filters and update them.
            if ($from !== null) {
                $this->activeFilters[] = $filter;
            }

            // Remove from view if set to hide in the template.
            if ((int) $this->params->get('show_' . $filter . '_search', 1) === 0 && $filter !== 'language') {
                $this->filterForm->removeField($filter, 'filter');
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
