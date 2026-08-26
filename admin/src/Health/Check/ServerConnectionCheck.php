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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Language\Text;

/**
 * Whether one media server can actually reach its platform.
 *
 * ⚠️ The only check in the registry that is not passive, and the reason
 * `isPassive()` exists at all. Running it makes an outbound API call and, on
 * YouTube, spends quota -- so a health page that ran it on load would burn
 * quota by being opened, which is worst at exactly the moment someone opens
 * the page because something looks wrong.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ServerConnectionCheck implements HealthCheckInterface
{
    /**
     * Constructor.
     *
     * @param   int     $serverId    The server record ID.
     * @param   string  $serverName  The server's name, for the report row.
     * @param   string  $serverType  The addon type backing the server.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(
        private readonly int $serverId,
        private readonly string $serverName,
        private readonly string $serverType
    ) {
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'external.server-connection-' . $this->serverId;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ExternalServices;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::sprintf('JBS_HEALTH_SERVER_CONNECTION_TITLE', $this->serverName);
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isPassive(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run(): HealthResult
    {
        // GDPR mode is switched on to stop data leaving the server. A status
        // page that made outbound calls anyway would be the exact surprise
        // that setting is meant to prevent, so the test refuses rather than
        // quietly ignoring it.
        if ($this->gdprMode()) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_SERVER_CONNECTION_GDPR')
            );
        }

        try {
            $result = CWMAddon::getInstance($this->serverType)->testConnection($this->serverId);
        } catch (\Throwable $e) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Warning,
                Text::sprintf('JBS_HEALTH_SERVER_CONNECTION_FAILED', $this->serverName, $e->getMessage()),
                'error:' . $e->getMessage(),
                'index.php?option=com_proclaim&view=cwmservers',
                Text::_('JBS_HEALTH_SERVER_CONNECTION_ACTION')
            );
        }

        if (!empty($result['success'])) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                $result['message'] ?? Text::sprintf('JBS_HEALTH_SERVER_CONNECTION_OK', $this->serverName)
            );
        }

        $error = (string) ($result['error'] ?? Text::_('JBS_HEALTH_SERVER_CONNECTION_UNKNOWN_ERROR'));

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            Text::sprintf('JBS_HEALTH_SERVER_CONNECTION_FAILED', $this->serverName, $error),
            'error:' . $error,
            'index.php?option=com_proclaim&view=cwmservers',
            Text::_('JBS_HEALTH_SERVER_CONNECTION_ACTION')
        );
    }

    /**
     * Whether the site is configured to keep data on the server.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function gdprMode(): bool
    {
        try {
            return (bool) Cwmparams::getAdmin()->params->get('gdpr_mode', '0');
        } catch (\Throwable) {
            // Unreadable params are treated as GDPR mode off, matching every
            // other reader of this setting. The button is still an explicit
            // action by an administrator, not something that fires on its own.
            return false;
        }
    }
}
