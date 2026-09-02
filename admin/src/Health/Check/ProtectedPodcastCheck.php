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
use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedMove;
use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Podcast-referenced media sitting in protected storage.
 *
 * The two are mutually exclusive: a feed's <enclosure> is a direct URL and
 * the web server refuses direct requests for the protected folder, so such an
 * episode can reach no subscriber. The feed builder therefore skips it — and
 * a silently shorter feed is its own kind of wrong, which is what this check
 * exists to say out loud.
 *
 * The move action refuses podcast-referenced media, so this state is reached
 * the other way round: a file placed in the folder by hand, or a podcast
 * assigned to media that was already protected. Both are one edit away.
 *
 * @since  __DEPLOY_VERSION__
 */
final class ProtectedPodcastCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'security.protected-podcast';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Security;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_PROTECTED_PODCAST');
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
        // Candidates by address, verdict in PHP: the podcast_id column holds
        // '', '0', '-1' or a comma list, and the one reading that reads it the
        // same way the feed does lives in CwmprotectedMove.
        //
        // ⚠️ The prefilter matches the folder NAME, not the path. The params
        // column is JSON, and json_encode escapes slashes, so the stored blob
        // reads images\/biblestudy\/protected\/ — a LIKE on the plain path
        // matches nothing, silently, and this check reported Ok over a live
        // conflict until an end-to-end run caught it. The decoded-path check
        // below is what actually decides; this only trims the scan.
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'podcast_id', 'params']))
            ->from($db->quoteName('#__bsms_mediafiles'))
            // Published rows only — strictly tighter than excluding
            // CONDITION_TRASHED. The feed reads published = 1 itself, so an
            // unpublished or trashed row is in no feed and is not a finding.
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('params') . ' LIKE ' . $db->quote('%' . basename(CwmprotectedStorage::RELATIVE_PATH) . '%'));

        $offenders = [];

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
            if (!CwmprotectedMove::podcastReferenced($row->podcast_id)) {
                continue;
            }

            $filename = (string) (new Registry($row->params))->get('filename', '');

            // The LIKE matched the whole params blob; confirm it is the
            // filename that points into the folder, not some other value.
            if (!str_contains($filename, CwmprotectedStorage::RELATIVE_PATH . '/')) {
                continue;
            }

            $offenders[(int) $row->id] = basename($filename);
        }

        if ($offenders === []) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_PROTECTED_PODCAST_OK')
            );
        }

        ksort($offenders);

        $named = [];

        foreach ($offenders as $id => $name) {
            $named[] = $name . ' (#' . $id . ')';
        }

        return new HealthResult(
            $this->getId(),
            // A Warning, not a Notice: episodes are missing from published
            // feeds right now, and nothing else says so.
            HealthStatus::Warning,
            Text::plural('JBS_HEALTH_PROTECTED_PODCAST_N', \count($named), implode(', ', $named))
            . ' ' . Text::_('JBS_HEALTH_PROTECTED_PODCAST_FIX'),
            // The affected ids, so quietening today's finding does not silence
            // a different file entering this state tomorrow.
            implode(',', array_keys($offenders)),
            'index.php?option=com_proclaim&view=cwmmediafiles',
            Text::_('JBS_HEALTH_PROTECTED_PODCAST_ACTION')
        );
    }
}
