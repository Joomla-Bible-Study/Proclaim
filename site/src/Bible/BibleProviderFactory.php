<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Bible;

use CWM\Component\Proclaim\Site\Bible\Provider\ApiBibleProvider;
use CWM\Component\Proclaim\Site\Bible\Provider\GetBibleProvider;
use CWM\Component\Proclaim\Site\Bible\Provider\LocalProvider;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory for creating Bible provider instances.
 *
 * @since  10.1.0
 */
class BibleProviderFactory
{
    /**
     * Cached provider instances.
     *
     * @var  array<string, BibleProviderInterface>
     * @since  10.1.0
     */
    private static array $instances = [];

    /**
     * Get a Bible provider by name.
     *
     * @param   string  $name    Provider name: "local", "getbible", or "api_bible"
     * @param   string  $apiKey  Optional API key (required for api_bible)
     *
     * @return  BibleProviderInterface
     *
     * @throws  \InvalidArgumentException  If provider name is unknown
     *
     * @since  10.1.0
     */
    public static function getProvider(string $name, string $apiKey = ''): BibleProviderInterface
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $provider = match ($name) {
            'local'     => new LocalProvider(),
            'getbible'  => new GetBibleProvider(),
            'api_bible' => new ApiBibleProvider($apiKey),
            default     => throw new \InvalidArgumentException(
                \sprintf('Unknown Bible provider: %s', $name)
            ),
        };

        self::$instances[$name] = $provider;

        return $provider;
    }

    /**
     * Find a suitable provider for a given Bible translation abbreviation.
     *
     * Checks enabled providers in priority order:
     *   1. Local (always enabled, offline)
     *   2. API.Bible (if enabled and translation has source='api_bible')
     *   3. GetBible (if enabled and translation has source='getbible')
     *   4. Fallback: first enabled external provider, then local
     *
     * @param   string    $version      Translation abbreviation (e.g. "kjv", "nlt")
     * @param   Registry  $adminParams  Admin component params with provider_* settings
     *
     * @return  BibleProviderInterface  The best matching provider
     *
     * @since  10.1.0
     */
    public static function getProviderForTranslation(string $version, Registry $adminParams): BibleProviderInterface
    {
        $gdprMode        = (int) $adminParams->get('gdpr_mode', 0) === 1;
        $getbibleEnabled = !$gdprMode && (int) $adminParams->get('provider_getbible', 1) === 1;
        $apiBibleEnabled = !$gdprMode && (int) $adminParams->get('provider_api_bible', 0) === 1;
        $apiBibleKey     = (string) $adminParams->get('api_bible_api_key', '');

        // Check if version is locally installed (Local is always enabled)
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_bible_verses'))
                ->where($db->quoteName('translation') . ' = :version')
                ->bind(':version', $version);
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                return self::getProvider('local');
            }
        } catch (\Throwable $e) {
            // Fall through to next provider
        }

        // Check if api_bible supports this version
        if ($apiBibleEnabled && !empty($apiBibleKey)) {
            try {
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_bible_translations'))
                    ->where($db->quoteName('abbreviation') . ' = :version')
                    ->where($db->quoteName('source') . ' = ' . $db->quote('api_bible'))
                    ->bind(':version', $version);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    return self::getProvider('api_bible', $apiBibleKey);
                }
            } catch (\Throwable $e) {
                // Fall through to next provider
            }
        }

        // Check if getbible supports this version (online API)
        if ($getbibleEnabled) {
            try {
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_bible_translations'))
                    ->where($db->quoteName('abbreviation') . ' = :version')
                    ->where($db->quoteName('source') . ' = ' . $db->quote('getbible'))
                    ->bind(':version', $version);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    return self::getProvider('getbible');
                }
            } catch (\Throwable $e) {
                // Fall through to fallback
            }
        }

        // Fallback: use getbible if enabled, otherwise local
        if ($getbibleEnabled) {
            return self::getProvider('getbible');
        }

        return self::getProvider('local');
    }

    /**
     * Get all available provider names.
     *
     * @return  array<string>
     *
     * @since  10.1.0
     */
    public static function getProviderNames(): array
    {
        return ['local', 'getbible', 'api_bible'];
    }

    /**
     * Reset cached instances (useful for testing).
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function reset(): void
    {
        self::$instances = [];
    }
}
