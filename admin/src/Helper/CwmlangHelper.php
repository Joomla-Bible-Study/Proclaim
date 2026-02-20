<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper for auto-loading component language strings into JavaScript.
 *
 * Parses the component's .ini language file once and registers all keys
 * via Text::script() so they are available as Joomla.Text._('KEY') in JS.
 * Also caches parsed keys for the request lifetime so multiple calls are cheap.
 *
 * Usage (in any template):
 *   CwmlangHelper::registerAllForJs();
 *
 * @since  10.1.0
 */
class CwmlangHelper
{
    /** @var string[]|null Cached array of all component language keys */
    private static ?array $cachedKeys = null;

    /**
     * Register all component language string keys via Text::script() so that
     * every JBS_* key is available in JavaScript as Joomla.Text._('KEY').
     *
     * Looks for the language file in the standard Joomla install paths and the
     * component's own language directory (dev symlink support). Falls back to
     * en-GB if the current language file is not found.
     *
     * @return void
     * @since  10.1.0
     */
    public static function registerAllForJs(): void
    {
        foreach (self::getAllKeys() as $key) {
            Text::script($key);
        }
    }

    /**
     * Return all component language keys, parsed from the .ini file.
     * Result is cached for the lifetime of the request.
     *
     * @return string[]
     * @since  10.1.0
     */
    public static function getAllKeys(): array
    {
        if (self::$cachedKeys !== null) {
            return self::$cachedKeys;
        }

        $lang     = Factory::getApplication()->getLanguage();
        $tag      = $lang->getTag();
        $baseName = $tag . '.com_proclaim.ini';
        $fallback = 'en-GB.com_proclaim.ini';

        $paths = [
            JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $baseName,
            JPATH_ADMINISTRATOR . '/language/en-GB/' . $fallback,
            JPATH_ADMINISTRATOR . '/components/com_proclaim/language/' . $tag . '/' . $baseName,
            JPATH_ADMINISTRATOR . '/components/com_proclaim/language/en-GB/' . $fallback,
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                self::$cachedKeys = array_keys(parse_ini_file($path, false, INI_SCANNER_RAW) ?: []);
                return self::$cachedKeys;
            }
        }

        self::$cachedKeys = [];
        return self::$cachedKeys;
    }
}
