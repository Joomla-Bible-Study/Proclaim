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

use CWM\Component\Proclaim\Site\Bible\Provider\BibleGatewayProvider;
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
     * @param   string  $name  Provider name: "local", "getbible", or "biblegateway"
     *
     * @return  BibleProviderInterface
     *
     * @throws  \InvalidArgumentException  If provider name is unknown
     *
     * @since  10.1.0
     */
    public static function getProvider(string $name): BibleProviderInterface
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $provider = match ($name) {
            'local'        => new LocalProvider(),
            'getbible'     => new GetBibleProvider(),
            'biblegateway' => new BibleGatewayProvider(),
            default        => throw new \InvalidArgumentException(
                \sprintf('Unknown Bible provider: %s', $name)
            ),
        };

        self::$instances[$name] = $provider;

        return $provider;
    }

    /**
     * Find a suitable provider for a given Bible translation abbreviation.
     *
     * Checks enabled providers in priority order: local first (offline),
     * then getbible (API), then biblegateway (iframe fallback).
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
        $localEnabled        = (int) $adminParams->get('provider_local', 1) === 1;
        $getbibleEnabled     = (int) $adminParams->get('provider_getbible', 1) === 1;
        $biblegatewayEnabled = (int) $adminParams->get('provider_biblegateway', 1) === 1;

        // Check if version is locally installed
        if ($localEnabled) {
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
        }

        // Check if getbible supports this version
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
                // Fall through to next provider
            }
        }

        // Fall back to BibleGateway iframe if enabled
        if ($biblegatewayEnabled) {
            return self::getProvider('biblegateway');
        }

        // If nothing else, try getbible as a general fallback
        if ($getbibleEnabled) {
            return self::getProvider('getbible');
        }

        // Last resort: biblegateway even if disabled
        return self::getProvider('biblegateway');
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
        return ['local', 'getbible', 'biblegateway'];
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
