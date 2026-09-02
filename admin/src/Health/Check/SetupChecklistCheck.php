<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use Joomla\CMS\Language\Text;

/**
 * Setup steps the site has not finished.
 *
 * ⚠️ Deliberately not gated on `setup_checklist_dismissed`. The dashboard
 * banner is, and it is dismissed permanently — press it once and both the
 * checklist and which of its items are still outstanding have nowhere left to
 * be seen. That is the pattern System Health exists to remove: a notice may be
 * quietened, but the information behind it has to remain findable.
 *
 * Never more than a Notice. An unfinished checklist on a working site is
 * information, not a fault, and nothing here is broken.
 *
 * @since  __DEPLOY_VERSION__
 */
final class SetupChecklistCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'configuration.setup-checklist';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Configuration;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_SETUP_CHECKLIST');
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run(): HealthResult
    {
        $items = CwmsetupwizardHelper::getChecklistItems();

        // No checklist to report on. getChecklistItems() returns nothing when
        // the setup wizard has not run, and a site that never ran it has not
        // left anything unfinished — it has not started.
        if ($items === []) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_SETUP_CHECKLIST_NONE')
            );
        }

        $outstanding = [];

        foreach ($items as $item) {
            if (!empty($item['done'])) {
                continue;
            }

            $label = (string) ($item['label'] ?? '');

            if ($label !== '') {
                $outstanding[] = Text::_($label);
            }
        }

        if ($outstanding === []) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_SETUP_CHECKLIST_DONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::plural(
                'JBS_HEALTH_SETUP_CHECKLIST_OUTSTANDING_N',
                \count($outstanding),
                implode(', ', $outstanding)
            ),
            // ⚠️ Keyed on which items are outstanding, not on a constant. A
            // site that quietens this has decided about the steps it had left
            // then; finishing one, or a later release adding another, is a
            // different statement and deserves to be seen.
            implode(',', array_map(
                static fn (array $item): string => (string) ($item['key'] ?? ''),
                array_filter($items, static fn (array $item): bool => empty($item['done']))
            )),
            'index.php?option=com_proclaim&view=cwmadmin',
            Text::_('JBS_HEALTH_SETUP_CHECKLIST_ACTION')
        );
    }
}
