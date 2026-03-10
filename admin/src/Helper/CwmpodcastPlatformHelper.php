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

use Joomla\CMS\Language\Text;

/**
 * Podcast Platform Helper — reads platform definitions from XML.
 *
 * Single source of truth: admin/forms/podcast-platforms.xml
 * Used by admin (PodcastPlatformField, edit.php) and site (Cwmpodcastsubscribe).
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmpodcastPlatformHelper
{
    /**
     * Cached platform definitions (null = not loaded yet).
     *
     * @var array<string, array{key: string, icon: string, label: string, pattern: string, url_hint: string, submit_url: string, api_class: string}>|null
     */
    private static ?array $cache = null;

    /**
     * Load and return all platform definitions from the XML config.
     *
     * Each entry is keyed by platform key and contains:
     * - key        (string) Platform identifier stored in DB
     * - icon       (string) FontAwesome 6 class
     * - label      (string) Translated display label
     * - pattern    (string) Pipe-separated URL substrings for auto-detection
     * - url_hint   (string) Example URL shown as placeholder in admin form
     * - submit_url (string) Manual submission portal URL
     * - api_class  (string) Optional PHP helper class name
     *
     * @return array<string, array{key: string, icon: string, label: string, pattern: string, url_hint: string, submit_url: string, api_class: string}>
     *
     * @since 10.1.0
     */
    public static function getPlatformDefinitions(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $file = JPATH_ADMINISTRATOR . '/components/com_proclaim/forms/podcast-platforms.xml';
        $xml  = simplexml_load_file($file);

        if ($xml === false) {
            self::$cache = [];

            return self::$cache;
        }

        self::$cache = [];

        foreach ($xml->platform as $p) {
            $key               = (string) $p['key'];
            self::$cache[$key] = [
                'key'        => $key,
                'icon'       => (string) $p['icon'],
                'label'      => Text::_((string) $p['label']),
                'pattern'    => (string) ($p['pattern'] ?? ''),
                'url_hint'   => (string) ($p['url_hint'] ?? 'https://'),
                'submit_url' => (string) ($p['submit_url'] ?? ''),
                'api_class'  => (string) ($p['api_class'] ?? ''),
            ];
        }

        return self::$cache;
    }

    /**
     * Return raw (untranslated) platform definitions for use in option lists.
     *
     * Same as getPlatformDefinitions() but returns the language key
     * instead of the translated string, so callers can pass the key
     * to Text::_() themselves or use it as an option value.
     *
     * @return array<string, array{key: string, icon: string, label_key: string}>
     *
     * @since 10.1.0
     */
    public static function getPlatformKeys(): array
    {
        $file = JPATH_ADMINISTRATOR . '/components/com_proclaim/forms/podcast-platforms.xml';
        $xml  = simplexml_load_file($file);

        if ($xml === false) {
            return [];
        }

        $result = [];

        foreach ($xml->platform as $p) {
            $key          = (string) $p['key'];
            $result[$key] = [
                'key'       => $key,
                'icon'      => (string) $p['icon'],
                'label_key' => (string) $p['label'],
            ];
        }

        return $result;
    }

    /**
     * Detect the platform icon and label for a given URL.
     *
     * @param   string  $url  The subscribe URL to match against platform patterns
     *
     * @return  array{icon: string, label: string}
     *
     * @since   10.1.0
     */
    public static function detectPlatformByUrl(string $url): array
    {
        $lower     = strtolower($url);
        $platforms = self::getPlatformDefinitions();

        foreach ($platforms as $p) {
            if (empty($p['pattern'])) {
                continue;
            }

            foreach (explode('|', $p['pattern']) as $pat) {
                if (str_contains($lower, trim($pat))) {
                    return ['icon' => $p['icon'], 'label' => $p['label']];
                }
            }
        }

        return ['icon' => 'fa-solid fa-headphones', 'label' => Text::_('JBS_PDC_PLATFORM_CUSTOM')];
    }
}
