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
use Joomla\Database\ParameterType;

/**
 * Whether a plugin Proclaim depends on is installed and enabled. Disabling
 * one breaks a feature with nothing on screen to say why.
 *
 * ⚠️ Read from `#__extensions`, not `PluginHelper::isEnabled()`, which answers
 * only for the running application and cannot tell disabled from missing.
 *
 * @since  10.6.0
 */
final class PluginEnabledCheck implements HealthCheckInterface
{
    /**
     * Constructor.
     *
     * @param   string       $folder    The plugin group, e.g. `content`.
     * @param   string       $element   The plugin element, e.g. `scripturelinks`.
     * @param   string       $titleKey  Language key naming the plugin.
     * @param   HealthStatus $severity  What a missing or disabled plugin means here.
     *
     * @since   10.6.0
     */
    public function __construct(
        private readonly string $folder,
        private readonly string $element,
        private readonly string $titleKey,
        private readonly HealthStatus $severity = HealthStatus::Warning
    ) {
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'dependencies.plugin-' . $this->folder . '-' . $this->element;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Dependencies;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_($this->titleKey);
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
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // $folder/$element are readonly properties; bind() takes its value by
        // reference, so copy them into locals it can bind.
        $folder  = $this->folder;
        $element = $this->element;
        $query   = $db->createQuery()
            ->select($db->quoteName('enabled'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = :folder')
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':folder', $folder, ParameterType::STRING)
            ->bind(':element', $element, ParameterType::STRING);
        $db->setQuery($query, 0, 1);

        $enabled = $db->loadResult();

        if ($enabled === null) {
            return new HealthResult(
                $this->getId(),
                $this->severity,
                Text::sprintf('JBS_HEALTH_PLUGIN_MISSING', $this->getTitle()),
                'missing',
                'index.php?option=com_installer&view=install',
                Text::_('JBS_HEALTH_PLUGIN_INSTALL_ACTION')
            );
        }

        if ((int) $enabled === 1) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::sprintf('JBS_HEALTH_PLUGIN_ENABLED', $this->getTitle())
            );
        }

        return new HealthResult(
            $this->getId(),
            $this->severity,
            Text::sprintf('JBS_HEALTH_PLUGIN_DISABLED', $this->getTitle()),
            'disabled',
            'index.php?option=com_plugins&filter[folder]=' . $this->folder . '&filter[search]=' . $this->element,
            Text::_('JBS_HEALTH_PLUGIN_ENABLE_ACTION')
        );
    }
}
