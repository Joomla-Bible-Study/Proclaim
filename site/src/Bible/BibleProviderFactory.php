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
                sprintf('Unknown Bible provider: %s', $name)
            ),
        };

        self::$instances[$name] = $provider;

        return $provider;
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
