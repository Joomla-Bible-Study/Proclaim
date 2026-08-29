<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Whether the permissions grid still fits inside PHP's `max_input_vars`.
 *
 * ⚠️ PHP discards inputs past the limit without an error, so the save appears
 * to succeed and changes nothing. The grid is one field per action per user
 * group, so enough groups breach it.
 *
 * @since  10.6.0
 */
final class MaxInputVarsCheck implements HealthCheckInterface
{
    /**
     * Non-grid inputs the permissions form posts. Deliberately generous:
     * over-estimating costs a spurious notice, under-estimating costs a
     * silent save failure.
     *
     * @var    int
     * @since  10.6.0
     */
    private const FORM_OVERHEAD = 25;

    /**
     * How close to the ceiling counts as too close, as a fraction.
     *
     * @var    float
     * @since  10.6.0
     */
    private const HEADROOM = 0.9;

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'environment.max-input-vars';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Environment;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_MAX_INPUT_VARS_TITLE');
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function run(): HealthResult
    {
        $limit = (int) \ini_get('max_input_vars');

        if ($limit <= 0) {
            // No number to compare against; guessing at PHP's 1000 default
            // would report a limit the host may not have.
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_MAX_INPUT_VARS_UNREADABLE')
            );
        }

        $needed = $this->widestGrid() + self::FORM_OVERHEAD;

        if ($needed <= (int) floor($limit * self::HEADROOM)) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::sprintf('JBS_HEALTH_MAX_INPUT_VARS_OK', $needed, $limit)
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::sprintf('JBS_HEALTH_MAX_INPUT_VARS_LOW', $needed, $limit),
            $needed . ':' . $limit,
            'index.php?option=com_proclaim&view=cwmpermissions',
            Text::_('JBS_HEALTH_MAX_INPUT_VARS_ACTION')
        );
    }

    /**
     * Inputs posted by the largest single permissions grid.
     *
     * One field per action per user group, for whichever section declares the
     * most actions -- the grid renders one section at a time, so the widest
     * one is what has to fit.
     *
     * @return  int
     *
     * @since   10.6.0
     */
    private function widestGrid(): int
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__usergroups'));
        $db->setQuery($query);

        $groups = (int) $db->loadResult();

        return $this->widestSection() * $groups;
    }

    /**
     * Actions declared by the section that declares the most of them.
     *
     * @return  int
     *
     * @since   10.6.0
     */
    private function widestSection(): int
    {
        $file = JPATH_ADMINISTRATOR . '/components/com_proclaim/access.xml';

        if (!is_file($file)) {
            return 0;
        }

        $doc = new \DOMDocument();

        if (!@$doc->load($file)) {
            return 0;
        }

        $widest = 0;

        foreach ((new \DOMXPath($doc))->query('/access/section') as $section) {
            $widest = max($widest, $section->getElementsByTagName('action')->length);
        }

        return $widest;
    }
}
