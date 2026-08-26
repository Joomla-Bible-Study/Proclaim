<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthQuietStore;
use CWM\Component\Proclaim\Administrator\Health\HealthRegistry;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * The System Health report.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmhealthModel extends BaseDatabaseModel
{
    /**
     * Every check, with its current result, grouped by section.
     *
     * Active checks are included and reported as not tested. The view is the
     * permanent record, so a check it declines to run still has to be listed
     * -- a report that omitted them would read as a clean bill of health it
     * never established.
     *
     * @return  array<string, array<int, array{check: HealthCheckInterface, result: HealthResult, quiet: bool}>>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getReport(): array
    {
        $results = HealthRegistry::runPassive();
        $report  = [];

        foreach (HealthRegistry::checks() as $check) {
            $result = $results[$check->getId()] ?? null;

            if ($result === null) {
                continue;
            }

            $report[$check->getGroup()->value][] = [
                'check'  => $check,
                'result' => $result,
                'quiet'  => HealthQuietStore::isQuiet($result),
            ];
        }

        // Declaration order of HealthGroup, so the report reads the same way
        // every time regardless of which checks happen to exist.
        $ordered = [];

        foreach (HealthGroup::cases() as $group) {
            if (isset($report[$group->value])) {
                $ordered[$group->value] = $this->sortRows($report[$group->value]);
            }
        }

        return $ordered;
    }

    /**
     * Count of results by status value, for the summary line.
     *
     * @param   array  $report  The grouped report.
     *
     * @return  array<string, int>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function summarise(array $report): array
    {
        $counts = [];

        foreach ($report as $rows) {
            foreach ($rows as $row) {
                $key          = $row['result']->status->value;
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Worst result first inside a section.
     *
     * @param   array  $rows  Rows for one section.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function sortRows(array $rows): array
    {
        usort(
            $rows,
            static fn ($a, $b) => $a['result']->status->weight() <=> $b['result']->status->weight()
        );

        return $rows;
    }
}
