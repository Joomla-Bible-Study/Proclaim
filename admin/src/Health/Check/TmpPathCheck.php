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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Joomla's temporary directory, which backup and restore both write through.
 *
 * A backup is assembled there before it is moved, and a restore unpacks there
 * before it reads. An unwritable tmp_path does not stop either from starting —
 * it stops them part way, which on the restore side is the worse half of the
 * operation to fail in.
 *
 * Reads the path from configuration rather than from the application, so the
 * answer is the same from a scheduled task as from a screen.
 *
 * @since  __DEPLOY_VERSION__
 */
final class TmpPathCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'filesystem.tmp-path';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Filesystem;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_TMP_PATH');
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
        try {
            $path = trim((string) Factory::getContainer()->get('config')->get('tmp_path', ''));
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_TMP_PATH_UNREADABLE')
            );
        }

        if ($path === '') {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Warning,
                Text::_('JBS_HEALTH_TMP_PATH_UNSET'),
                'unset',
                'index.php?option=com_config',
                Text::_('JBS_HEALTH_TMP_PATH_ACTION')
            );
        }

        if (!is_dir($path)) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Warning,
                Text::sprintf('JBS_HEALTH_TMP_PATH_MISSING', $path),
                // The path itself, so quietening one and then changing the
                // setting does not carry the silence to a different directory.
                'missing:' . md5($path),
                'index.php?option=com_config',
                Text::_('JBS_HEALTH_TMP_PATH_ACTION')
            );
        }

        if (!is_writable($path)) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Warning,
                Text::sprintf('JBS_HEALTH_TMP_PATH_UNWRITABLE', $path),
                'unwritable:' . md5($path),
                'index.php?option=com_config',
                Text::_('JBS_HEALTH_TMP_PATH_ACTION')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Ok,
            Text::_('JBS_HEALTH_TMP_PATH_OK')
        );
    }
}
