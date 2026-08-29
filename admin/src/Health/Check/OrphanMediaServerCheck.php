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
use Joomla\Database\DatabaseInterface;

/**
 * Media files pointing at a server record that has been deleted.
 *
 * The server holds the path the file is resolved against, so without it the
 * media cannot be turned into a URL at all — the file plays nowhere and the
 * media list gives no reason. Deleting a server does not adopt or repoint the
 * media that referenced it.
 *
 * ⚠️ `server_id` is nullable and also carries 0, and neither means orphaned: a
 * media row can legitimately have no server. Only a positive id naming a row
 * that is not there is a finding.
 *
 * @since  10.6.0
 */
final class OrphanMediaServerCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'database.orphan-media-servers';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Database;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_ORPHAN_MEDIA_SERVERS');
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
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $servers = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_servers'));

            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('server_id') . ' > 0')
                ->where($db->quoteName('server_id') . ' NOT IN (' . $servers . ')');
            $db->setQuery($query);

            $orphans = (int) $db->loadResult();
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_ORPHAN_MEDIA_SERVERS_UNREADABLE')
            );
        }

        if ($orphans === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_ORPHAN_MEDIA_SERVERS_NONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            $orphans === 1
                ? Text::_('JBS_HEALTH_ORPHAN_MEDIA_SERVERS_1')
                : Text::sprintf('JBS_HEALTH_ORPHAN_MEDIA_SERVERS_N', $orphans),
            // The count, so clearing it at three raises again at four.
            (string) $orphans,
            'index.php?option=com_proclaim&view=cwmmediafiles',
            Text::_('JBS_HEALTH_ORPHAN_MEDIA_SERVERS_ACTION')
        );
    }
}
