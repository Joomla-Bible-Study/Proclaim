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
 * @since  10.6.0
 */
class CwmhealthModel extends BaseDatabaseModel
{
    /**
     * Every check with its current result, grouped by section.
     *
     * ⚠️ Active checks are listed as not tested rather than omitted; a missing
     * check reads as a passing one.
     *
     * @return  array<string, array<int, array{check: HealthCheckInterface, result: HealthResult, quiet: bool}>>
     *
     * @since   10.6.0
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

        // HealthGroup declaration order, so the report reads the same every time.
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
     * @since   10.6.0
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
     * @since   10.6.0
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
