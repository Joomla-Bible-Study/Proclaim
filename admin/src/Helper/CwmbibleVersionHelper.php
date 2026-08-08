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

use CWM\Library\Scripture\Helper\ScriptureParamsHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Business logic for listing available Bible translations, extracted from
 * Field/BibleVersionField.php so the field stays a thin Joomla-form
 * adapter over this helper. See #1484.
 *
 * @since  10.5.6
 */
class CwmbibleVersionHelper
{
    /**
     * ISO language code to human-readable name map.
     *
     * @var  array<string, string>
     * @since  10.5.6
     */
    private const LANGUAGE_NAMES = [
        'af' => 'Afrikaans',  'am' => 'Amharic',      'ar' => 'Arabic',
        'bg' => 'Bulgarian',  'bn' => 'Bengali',       'cs' => 'Czech',
        'da' => 'Danish',     'de' => 'German',        'el' => 'Greek',
        'en' => 'English',    'eo' => 'Esperanto',     'es' => 'Spanish',
        'et' => 'Estonian',   'fa' => 'Persian',        'fi' => 'Finnish',
        'fr' => 'French',     'ga' => 'Irish',         'he' => 'Hebrew',
        'hi' => 'Hindi',      'hr' => 'Croatian',      'hu' => 'Hungarian',
        'id' => 'Indonesian', 'is' => 'Icelandic',     'it' => 'Italian',
        'ja' => 'Japanese',   'ko' => 'Korean',        'la' => 'Latin',
        'lt' => 'Lithuanian', 'lv' => 'Latvian',       'mk' => 'Macedonian',
        'ml' => 'Malayalam',  'mr' => 'Marathi',       'ms' => 'Malay',
        'my' => 'Burmese',    'ne' => 'Nepali',        'nl' => 'Dutch',
        'no' => 'Norwegian',  'pl' => 'Polish',        'pt' => 'Portuguese',
        'ro' => 'Romanian',   'ru' => 'Russian',       'sk' => 'Slovak',
        'sl' => 'Slovenian',  'sq' => 'Albanian',      'sr' => 'Serbian',
        'sv' => 'Swedish',    'sw' => 'Swahili',       'ta' => 'Tamil',
        'te' => 'Telugu',     'th' => 'Thai',          'tl' => 'Tagalog',
        'tr' => 'Turkish',    'uk' => 'Ukrainian',     'ur' => 'Urdu',
        'vi' => 'Vietnamese', 'zh' => 'Chinese',
    ];

    /**
     * Common defaults used when no translations exist in the DB yet
     * (e.g. during install, before the translation sync has run).
     *
     * @var  array<string, string>
     * @since  10.5.6
     */
    private const FALLBACK_VERSIONS = [
        'kjv' => 'King James Version',
        'web' => 'World English Bible',
        'asv' => 'American Standard Version',
        'ylt' => "Young's Literal Translation",
    ];

    /**
     * Resolve the default Bible version: the admin component's configured
     * default_version plugin param, falling back to 'kjv'.
     *
     * @return  string
     *
     * @since   10.5.6
     */
    public static function getDefaultVersion(): string
    {
        $default = 'kjv';

        try {
            $pluginDefault = ScriptureParamsHelper::getParams()->get('default_version', '');

            if (!empty($pluginDefault)) {
                $default = $pluginDefault;
            }
        } catch (\Exception $e) {
            // Ignore — plugin params unavailable, use 'kjv' fallback
        }

        return $default;
    }

    /**
     * Build the list of selectable Bible version options: aggregates
     * translations from all providers into a single deduplicated list,
     * grouped with the current language's versions first, then all other
     * languages alphabetically, each option labelled with its language
     * when it isn't the current one.
     *
     * When $servableOnly is true, only translations that can actually be
     * served are included (locally installed + translations from enabled
     * providers).
     *
     * @param   bool  $servableOnly  Restrict to servable translations only.
     *
     * @return  array<int, array{value: string, text: string}>
     *
     * @since   10.5.6
     */
    public static function getVersionOptions(bool $servableOnly = false): array
    {
        $enabledSources = $servableOnly ? self::getEnabledProviderSources() : [];

        return self::buildOptions(
            self::fetchTranslationRows(),
            $servableOnly,
            $enabledSources,
            self::detectCurrentLanguage()
        );
    }

    /**
     * Determine which online provider sources are enabled (GDPR-aware),
     * for filtering to servable-only translations.
     *
     * @return  string[]
     *
     * @since   10.5.6
     */
    private static function getEnabledProviderSources(): array
    {
        try {
            $pluginParams = ScriptureParamsHelper::getParams();
        } catch (\Exception $e) {
            $pluginParams = new Registry();
        }

        $gdprMode       = (int) $pluginParams->get('gdpr_mode', 0) === 1;
        $enabledSources = [];

        if (!$gdprMode && (int) $pluginParams->get('provider_getbible', 1) === 1) {
            $enabledSources[] = 'getbible';
        }

        if (
            !$gdprMode && (int) $pluginParams->get('provider_api_bible', 0) === 1
            && !empty($pluginParams->get('api_bible_api_key', ''))
        ) {
            $enabledSources[] = 'api_bible';
        }

        return $enabledSources;
    }

    /**
     * Detect the current site/admin language (first two characters of the
     * language tag), to prioritize native-language versions.
     *
     * @return  string
     *
     * @since   10.5.6
     */
    private static function detectCurrentLanguage(): string
    {
        try {
            $tag = Factory::getApplication()->getLanguage()->getTag();

            return $tag !== null ? substr($tag, 0, 2) : 'en';
        } catch (\Exception $e) {
            return 'en';
        }
    }

    /**
     * Load raw translation rows from the database.
     *
     * @return  array<int, \stdClass>
     *
     * @since   10.5.6
     */
    private static function fetchTranslationRows(): array
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName(['abbreviation', 'name', 'language', 'installed', 'source']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('language') . ' ASC, ' . $db->quoteName('name') . ' ASC');
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Exception $e) {
            // Database table might not exist yet during install
            return [];
        }
    }

    /**
     * Pure grouping/sorting/labelling logic, isolated from I/O so it can be
     * unit-tested with synthetic rows rather than live DB content.
     *
     * @param   array<int, \stdClass>  $rows            Raw translation rows (abbreviation, name, language, installed, source).
     * @param   bool                   $servableOnly    Restrict to servable translations only.
     * @param   string[]               $enabledSources  Provider sources enabled when $servableOnly is true.
     * @param   string                 $currentLang     Current site/admin language (2-letter code).
     *
     * @return  array<int, array{value: string, text: string}>
     *
     * @since   10.5.6
     */
    private static function buildOptions(array $rows, bool $servableOnly, array $enabledSources, string $currentLang): array
    {
        // Collected versions keyed by abbreviation to deduplicate
        $versions  = [];
        $languages = [];

        foreach ($rows as $row) {
            $abbr      = $row->abbreviation;
            $installed = (int) ($row->installed ?? 0) === 1;

            // In servable_only mode, skip translations that can't be served
            if ($servableOnly && !$installed && !\in_array($row->source ?? '', $enabledSources, true)) {
                continue;
            }

            // Only add if not already collected (first occurrence wins)
            if (!isset($versions[$abbr])) {
                $versions[$abbr]  = $row->name;
                $languages[$abbr] = $row->language ?? '';
            }
        }

        // Fall back to common defaults only when there is no catalogue at all,
        // which is the documented case: a site where the translation sync has
        // not run yet.
        //
        // Not when $servableOnly emptied the list. That means a catalogue
        // exists and nothing in it can currently be served -- every provider
        // disabled under GDPR mode with nothing installed locally, for
        // instance. Offering kjv/web/asv/ylt there presents four versions as
        // selectable in a field that promises servable ones, and the reference
        // then fails to resolve at render time. An empty list is the honest
        // answer: install a translation or enable a provider.
        if ($versions === [] && $rows === []) {
            $versions  = self::FALLBACK_VERSIONS;
            $languages = array_fill_keys(array_keys(self::FALLBACK_VERSIONS), 'en');
        }

        // Group by language, user's language first
        $nativeVersions = [];
        $otherVersions  = [];

        foreach ($versions as $abbr => $name) {
            $lang = substr($languages[$abbr] ?? '', 0, 2);

            if ($lang === $currentLang) {
                $nativeVersions[$abbr] = ['name' => $name, 'lang' => $lang];
            } else {
                $otherVersions[$abbr] = ['name' => $name, 'lang' => $lang];
            }
        }

        // Sort each group by name
        uasort($nativeVersions, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        uasort($otherVersions, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        // Build options — native language first, then others with language label
        $options = [];

        foreach ($nativeVersions as $abbr => $info) {
            $options[] = [
                'value' => $abbr,
                'text'  => $info['name'] . ' (' . strtoupper($abbr) . ')',
            ];
        }

        foreach ($otherVersions as $abbr => $info) {
            $langName  = self::LANGUAGE_NAMES[$info['lang']] ?? strtoupper($info['lang']);
            $options[] = [
                'value' => $abbr,
                'text'  => $info['name'] . ' (' . strtoupper($abbr) . ') — ' . $langName,
            ];
        }

        return $options;
    }
}
