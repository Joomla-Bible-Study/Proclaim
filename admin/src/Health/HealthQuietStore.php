<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Health;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Which dashboard notices have been quietened, and against what finding.
 *
 * Stored as a single JSON object under `health_quiet` in the `#__bsms_admin`
 * params, mapping a check id to the fingerprint that was current when it was
 * quietened. A stored fingerprint that no longer matches means the finding
 * changed, so the notice is live again -- nothing has to expire it.
 *
 * ⚠️ Quietening only affects the dashboard. The System Health view renders
 * every check whatever this says, which is what makes clearing a banner safe.
 *
 * The map is kept as an encoded string rather than a nested Registry node
 * because check ids contain dots (`content.legacy-servers`), and Registry
 * treats a dot as a path separator.
 *
 * @since  __DEPLOY_VERSION__
 */
final class HealthQuietStore
{
    /**
     * The `#__bsms_admin` params key holding the encoded map.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const PARAM_KEY = 'health_quiet';

    /**
     * Whether this result should be hidden from the dashboard.
     *
     * A passing result is never "quiet" -- it has no fingerprint, so there is
     * nothing to compare and nothing to suppress.
     *
     * @param   HealthResult  $result  The result to test.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isQuiet(HealthResult $result): bool
    {
        if ($result->fingerprint === '') {
            return false;
        }

        return (self::read()[$result->id] ?? null) === $result->fingerprint;
    }

    /**
     * Silence a result on the dashboard until its finding changes.
     *
     * @param   HealthResult  $result  The result being cleared.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function quieten(HealthResult $result): void
    {
        if ($result->fingerprint === '') {
            return;
        }

        $map              = self::read();
        $map[$result->id] = $result->fingerprint;

        self::write($map);
    }

    /**
     * Bring a check back to the dashboard.
     *
     * @param   string  $id  The check id.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function restore(string $id): void
    {
        $map = self::read();

        if (!\array_key_exists($id, $map)) {
            return;
        }

        unset($map[$id]);

        self::write($map);
    }

    /**
     * The stored map, check id to fingerprint.
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function read(): array
    {
        // An unreadable row means nothing is quietened, which errs towards
        // showing notices rather than hiding them.
        $raw = (string) (self::readParams()?->get(self::PARAM_KEY, '') ?? '');

        if ($raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // A blob we cannot read is treated as nothing quietened, which
            // errs towards showing notices rather than hiding them. Logged
            // because the alternative reading -- everything silently
            // un-quietened -- looks like the feature stopped working.
            Log::add(
                'Proclaim health quieting state was unreadable and has been ignored: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );

            return [];
        }

        if (!\is_array($decoded)) {
            return [];
        }

        return array_filter(
            $decoded,
            static fn ($value, $key) => \is_string($key) && \is_string($value),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Persist the map back to the admin row.
     *
     * @param   array<string, string>  $map  Check id to fingerprint.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function write(array $map): void
    {
        $params = self::readParams();

        // ⚠️ Refuse rather than overwrite. Writing here means re-serialising
        // the whole params column, so proceeding from the empty fallback would
        // trade every stored setting for a cosmetic preference. Quietening a
        // banner is not worth that; repairing the row is a separate job.
        if ($params === null) {
            throw new \RuntimeException('Proclaim admin params are unreadable, so quieting state cannot be saved.');
        }

        $params->set(self::PARAM_KEY, $map === [] ? '' : json_encode($map, JSON_THROW_ON_ERROR));

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->update($db->quoteName('#__bsms_admin'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * The admin params row, read fresh, or null when it cannot be parsed.
     *
     * Not taken from `Cwmparams::getAdmin()`: that caches the row for the
     * request, so a write followed by a read in the same request would return
     * the pre-write state.
     *
     * @return  ?Registry
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function readParams(): ?Registry
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_admin'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query, 0, 1);

        try {
            return new Registry($db->loadResult() ?: '{}');
        } catch (\RuntimeException $e) {
            // ⚠️ Registry does not shrug this off. A truncated write leaves a
            // string that still starts with `{`, and Json::stringToObject()
            // throws for exactly that -- the case Cwmparams::getAdmin() and
            // setCompParams() both already guard. This runs on every admin's
            // dashboard, so an unguarded throw would turn a half-written row
            // into a dead screen.
            //
            Log::add(
                'Proclaim admin params were unreadable while checking health quieting: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );

            return null;
        }
    }
}
