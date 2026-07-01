<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Records delete and state-change (publish / unpublish / archive / trash) actions
 * for a list controller in Joomla's User Actions Log.
 *
 * A consuming AdminController sets three properties describing its entity:
 *   protected string $actionlogType        // e.g. 'serie' — the itemlink/type key
 *   protected string $actionlogTable       // e.g. '#__bsms_series'
 *   protected string $actionlogTitleColumn // e.g. 'series_text' ('' = use "#id")
 *
 * Both overrides capture the selected items' titles before delegating to the core
 * AdminController, then verify what actually changed (rows gone for a delete, or
 * the new state for a publish) so only real changes are logged. Logging is
 * best-effort and never interrupts the action.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CwmActionlogListTrait
{
    /**
     * Delete the selected items, then log each one that was actually removed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete()
    {
        $before = $this->collectActionlogTitles();

        parent::delete();

        if ($before === []) {
            return;
        }

        $stillPresent = $this->actionlogFilterIds(array_keys($before));

        foreach ($before as $id => $title) {
            if (!\in_array((int) $id, $stillPresent, true)) {
                CwmactionlogHelper::log('COM_PROCLAIM_ACTION_LOG_ITEM_DELETED', $title, $this->actionlogType, (int) $id);
            }
        }
    }

    /**
     * Change the state of the selected items, then log each one that reached the
     * intended state.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function publish()
    {
        [$value, $key] = $this->actionlogStateForTask();
        $before        = $this->collectActionlogTitles();

        parent::publish();

        if ($before === [] || $key === null) {
            return;
        }

        $matched = $this->actionlogFilterIds(array_keys($before), $value);

        foreach ($before as $id => $title) {
            if (\in_array((int) $id, $matched, true)) {
                CwmactionlogHelper::log($key, $title, $this->actionlogType, (int) $id);
            }
        }
    }

    /**
     * Map the current task to its target published value and log message key.
     *
     * @return  array{0:int, 1:?string}  [target published value, message key or null]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function actionlogStateForTask(): array
    {
        return match ($this->getTask()) {
            'publish'   => [1, 'COM_PROCLAIM_ACTION_LOG_ITEM_PUBLISHED'],
            'unpublish' => [0, 'COM_PROCLAIM_ACTION_LOG_ITEM_UNPUBLISHED'],
            'archive'   => [2, 'COM_PROCLAIM_ACTION_LOG_ITEM_ARCHIVED'],
            'trash'     => [-2, 'COM_PROCLAIM_ACTION_LOG_ITEM_TRASHED'],
            default     => [0, null],
        };
    }

    /**
     * Read the selected IDs and their titles before the action runs.
     *
     * @return  array<int,string>  id => title
     *
     * @since   __DEPLOY_VERSION__
     */
    private function collectActionlogTitles(): array
    {
        if (($this->actionlogType ?? '') === '' || ($this->actionlogTable ?? '') === '') {
            return [];
        }

        $ids = array_values(array_filter((array) $this->input->get('cid', [], 'int')));

        if ($ids === []) {
            return [];
        }

        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $column = (string) ($this->actionlogTitleColumn ?? '');
            $query  = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName($this->actionlogTable))
                ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);

            if ($column !== '') {
                $query->select($db->quoteName($column, 'title'));
            }

            $rows = $db->setQuery($query)->loadObjectList('id') ?: [];

            $titles = [];

            foreach ($ids as $id) {
                $title       = $column !== '' ? (string) ($rows[$id]->title ?? '') : '';
                $titles[$id] = $title !== '' ? $title : ('#' . $id);
            }

            return $titles;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Of the given IDs, return those still present in the table (optionally also
     * matching a published state). Used to confirm what actually changed.
     *
     * @param   int[]      $ids    Candidate IDs.
     * @param   int|null   $state  Optional published value to also match.
     *
     * @return  int[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function actionlogFilterIds(array $ids, ?int $state = null): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === [] || ($this->actionlogTable ?? '') === '') {
            return [];
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName($this->actionlogTable))
                ->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);

            if ($state !== null) {
                $query->where($db->quoteName('published') . ' = :state')
                    ->bind(':state', $state, ParameterType::INTEGER);
            }

            return array_map('intval', $db->setQuery($query)->loadColumn() ?: []);
        } catch (\Exception $e) {
            return [];
        }
    }
}
