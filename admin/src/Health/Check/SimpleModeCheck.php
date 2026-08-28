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
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\Language\Text;

/**
 * Whether Simple Mode is hiding parts of the admin.
 *
 * Not a fault, and never a warning — someone chose it. It is here because the
 * dashboard notice can be cleared, and once cleared there was nowhere left to
 * answer "why is the field I want missing?".
 *
 * ⚠️ A hidden field is not an unused one. Descriptions and scripture entered
 * before the switch stay live on the site, so a message can be published with
 * text its editor cannot see.
 *
 * @since  __DEPLOY_VERSION__
 */
final class SimpleModeCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'configuration.simple-mode';
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
        return Text::_('JBS_HEALTH_SIMPLE_MODE');
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
        if (Cwmhelper::getSimpleView()->mode !== 1) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_SIMPLE_MODE_OFF')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Notice,
            Text::_('JBS_HEALTH_SIMPLE_MODE_ON'),
            // Constant: the finding is the setting, and it has one shape. A
            // site that quietens this has decided it knows, and only turning
            // Simple Mode off should clear it.
            'on',
            'index.php?option=com_proclaim&view=cwmadmin',
            Text::_('JBS_HEALTH_SIMPLE_MODE_ACTION')
        );
    }
}
