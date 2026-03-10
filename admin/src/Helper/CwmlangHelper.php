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

/**
 * Helper for auto-loading component language strings into JavaScript.
 *
 * Parses the component's .ini language file once and bulk-registers all keys
 * into the document's joomla.jtext script options so they are available as
 * Joomla.Text._('KEY') in JS. Caches parsed keys for the request lifetime.
 *
 * Usage (in any template):
 *   CwmlangHelper::registerAllForJs();
 *
 * @since  10.1.0
 */
class CwmlangHelper
{
    /** @var string[]|null Cached array of all component language keys
     * @since 10.1.0
     * */
    private static ?array $cachedKeys = null;

    /**
     * Bulk-register all component language keys so that every JBS_* key is
     * available in JavaScript as Joomla.Text._('KEY').
     *
     * Uses a single addScriptOptions() call instead of one Text::script() per
     * key. Text::script() calls deprecated Factory::getDocument() and
     * Factory::getLanguage() on every invocation; with ~2400 keys that produces
     * ~14 000 deprecation notices and ~150 MB of debug-plugin memory overhead.
     *
     * @return void
     * @throws \Exception
     * @since  10.1.0
     */
    public static function registerAllForJs(): void
    {
        $app  = Factory::getApplication();
        $lang = $app->getLanguage();
        $doc  = $app->getDocument();

        // Ensure the Joomla.Text JS class is loaded (WAM dependency, non-deprecated).
        $doc->getWebAssetManager()->useScript('core');

        // Merge all component keys into the existing joomla.jtext script options in
        // one pass. Text::script() would call deprecated Factory::getDocument() and
        // Factory::getLanguage() once per key (~2400 calls = ~14 000 deprecation notices).
        $existing = $doc->getScriptOptions('joomla.jtext') ?: [];

        foreach (self::getAllKeys() as $key) {
            $existing[strtoupper($key)] = $lang->_($key);
        }

        $doc->addScriptOptions('joomla.jtext', $existing, false);
    }

    /**
     * Return all component language keys, parsed from the .ini file.
     * Result is cached for the lifetime of the request.
     *
     * @return string[]
     * @throws \Exception
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
