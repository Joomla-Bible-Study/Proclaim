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

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Media restricted in Proclaim that the web server will still hand to anyone.
 *
 * ⚠️ An access level gates the route, not the file. A media file stored under
 * the web root is served by the web server without Joomla ever being asked, so
 * a restricted item is readable by anyone holding the address — and no admin
 * screen says so.
 *
 * Restriction is decided against the levels a *logged-out* visitor holds, read
 * for user 0 explicitly rather than from whoever is looking. A check has to
 * answer the same way from a scheduled task as from a screen.
 *
 * @since  10.6.0
 */
final class RestrictedMediaCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getId(): string
    {
        return 'security.restricted-media';
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::Security;
    }

    /**
     * @inheritDoc
     *
     * @since  10.6.0
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_RESTRICTED_MEDIA');
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
        // ⚠️ First, because the answer is otherwise a confident wrong one.
        // Deciding a URL is "ours" means comparing it to the site root, and
        // from a scheduled task with no live_site configured there is no root
        // to compare with — every file then looks like somebody else's, and
        // the check would report a clean site from cron.
        if (!CwmmediaProtectionHelper::canResolveSiteRoot()) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_RESTRICTED_MEDIA_NO_ROOT')
            );
        }

        try {
            $guestLevels = Access::getAuthorisedViewLevels(0);
            $exposed     = $this->countExposed($guestLevels);
        } catch (\Exception) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Unknown,
                Text::_('JBS_HEALTH_RESTRICTED_MEDIA_UNREADABLE')
            );
        }

        if ($exposed === 0) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_RESTRICTED_MEDIA_NONE')
            );
        }

        return new HealthResult(
            $this->getId(),
            HealthStatus::Warning,
            ($exposed === 1
                ? Text::_('JBS_HEALTH_RESTRICTED_MEDIA_1')
                : Text::sprintf('JBS_HEALTH_RESTRICTED_MEDIA_N', $exposed))
                // ⚠️ No filtered destination, deliberately. Which files are
                // affected is decided per row by isRestrictedButReachable(),
                // from the resolved URL — it is not a column and cannot become
                // one, so there is nothing for a list filter to match on.
                // Saying what to look for beats a link that implies a filter
                // the list does not have.
                . ' ' . Text::_('JBS_HEALTH_RESTRICTED_MEDIA_FIX'),
            // The count, so quietening at one does not hide the second.
            (string) $exposed,
            'index.php?option=com_proclaim&view=cwmmediafiles',
            Text::_('JBS_HEALTH_RESTRICTED_MEDIA_ACTION')
        );
    }

    /**
     * How many restricted media files resolve to a URL this site serves.
     *
     * The restriction half is done in SQL so the row set is already small
     * before any URL is built; the reachability half needs the resolved URL and
     * is done in PHP, as it is everywhere else.
     *
     * @param   array<int>  $guestLevels  View levels a logged-out visitor holds.
     *
     * @return  int
     *
     * @since   10.6.0
     */
    private function countExposed(array $guestLevels): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Server paths first: a handful of rows, decoded once, rather than one
        // JSON decode per media file for a value they mostly share.
        $serverPaths = [];

        $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['id', 'params']))
                ->from($db->quoteName('#__bsms_servers'))
        );

        foreach ($db->loadObjectList() as $server) {
            $serverPaths[(int) $server->id] = (string) (new Registry($server->params ?? ''))->get('path', '');
        }

        $levels = implode(',', array_map('intval', $guestLevels)) ?: '0';

        $query = $db->createQuery()
            ->select(
                $db->quoteName('m.params') . ', ' . $db->quoteName('m.server_id') . ', '
                . $db->quoteName('m.access') . ', '
                . $db->quoteName('study.access', 'study_access') . ', '
                . $db->quoteName('series.access', 'series_access')
            )
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->leftJoin(
                $db->quoteName('#__bsms_studies', 'study')
                . ' ON (' . $db->quoteName('study.id') . ' = ' . $db->quoteName('m.study_id') . ')'
            )
            ->leftJoin(
                $db->quoteName('#__bsms_series', 'series')
                . ' ON (' . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id') . ')'
            )
            // ⚠️ The three access tests are one bracketed group. where() joins
            // with AND and adds no brackets, so a top-level OR here would bind
            // looser than every condition before it. Written inline rather than
            // built into a variable first, because WhereClauseContractTest reads
            // the argument at the call site and a variable is invisible to it.
            ->where(
                '(' . $db->quoteName('m.access') . ' NOT IN (' . $levels . ')'
                . ' OR ' . $db->quoteName('study.access') . ' NOT IN (' . $levels . ')'
                . ' OR ' . $db->quoteName('series.access') . ' NOT IN (' . $levels . '))'
            )
            // A media file in the trash is not an exposure to act on.
            ->where($db->quoteName('m.published') . ' <> ' . ProclaimComponent::CONDITION_TRASHED)
            // ⚠️ Bracketed with an IS NULL, because study is a LEFT JOIN: a
            // media row whose study_id matches nothing joins to NULL, and
            // `NULL <> -2` is NULL, so a bare comparison would drop those rows
            // from the count entirely — hiding real exposures rather than
            // trashed ones. Written inline: WhereClauseContractTest reads the
            // argument at the call site, and a variable is invisible to it.
            ->where(
                '(' . $db->quoteName('study.published') . ' IS NULL'
                . ' OR ' . $db->quoteName('study.published') . ' <> '
                . ProclaimComponent::CONDITION_TRASHED . ')'
            );

        // ⚠️ Deliberately no filter on series.published. A trashed series whose
        // sermon is still live does not make that sermon's media unreachable,
        // so excluding it here would hide a genuine exposure.

        $db->setQuery($query);

        $exposed = 0;

        foreach ($db->loadObjectList() as $row) {
            $params   = new Registry($row->params ?? '');
            $filename = (string) $params->get('filename', '');

            if ($filename === '') {
                continue;
            }

            $url = Cwmhelper::mediaBuildUrl(
                $serverPaths[(int) $row->server_id] ?? '',
                $filename,
                $params,
                true
            );

            if (CwmmediaProtectionHelper::isRestrictedButReachable($row, $url, $guestLevels)) {
                $exposed++;
            }
        }

        return $exposed;
    }
}
