<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible Version selection field.
 *
 * Provider-agnostic, searchable version picker that aggregates available
 * translations from all enabled providers. Shows ALL versions from all
 * languages with language labels so users can search across languages.
 * The JS search enhancement (bible-version-searchable class) adds a filter
 * input above the select for quick lookup.
 *
 * @since  10.1.0
 */
class BibleVersionField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     * @since  10.1.0
     */
    protected $type = 'BibleVersion';

    /**
     * Well-known Bible version names keyed by abbreviation.
     *
     * Used as fallback when the DB doesn't have a name for a version.
     *
     * @var  array<string, string>
     * @since  10.1.0
     */
    private const VERSION_NAMES = [
        'kjv'  => 'King James Version',
        'nlt'  => 'New Living Translation',
        'esv'  => 'English Standard Version',
        'niv'  => 'New International Version',
        'nasb' => 'New American Standard Bible',
        'nkjv' => 'New King James Version',
        'asv'  => 'American Standard Version',
        'ylt'  => "Young's Literal Translation",
        'hcsb' => 'Holman Christian Standard Bible',
        'amp'  => 'Amplified Bible',
        'cev'  => 'Contemporary English Version',
        'msg'  => 'The Message',
        'gnt'  => 'Good News Translation',
        'web'  => 'World English Bible',
    ];

    /**
     * ISO language code to human-readable name map.
     *
     * @var  array<string, string>
     * @since  10.1.0
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
     * Method to attach a Form object to the field.
     *
     * Sets the default value from the admin component's default_bible_version
     * setting when no explicit default is provided in the XML.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object
     * @param   mixed              $value    The value of the element
     * @param   string             $group    The group the field belongs to
     *
     * @return  bool  True on success
     *
     * @since  10.1.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        // If no value is set (new record), use the admin default
        if ($result && ($this->value === null || $this->value === '')) {
            $default = 'kjv';

            try {
                $admin  = Cwmparams::getAdmin();
                $params = $admin->params ?? null;

                if ($params) {
                    $adminDefault = $params->get('default_bible_version', '');

                    if (!empty($adminDefault)) {
                        $default = $adminDefault;
                    }
                }
            } catch (\Exception $e) {
                // Ignore — no admin params available, use 'kjv' fallback
            }

            $this->value = $default;
        }

        // Ensure the searchable CSS class is present (also set via XML class attribute)
        if ($result) {
            $existing    = (string) ($this->class ?? '');
            $this->class = trim($existing . ' bible-version-searchable');
        }

        return $result;
    }

    /**
     * Get the field options.
     *
     * Aggregates translations from all providers into a single deduplicated
     * list. Shows versions from all languages, sorted with the user's
     * language first, then all other languages alphabetically. Each option
     * includes a language label for cross-language searching.
     *
     * When `servable_only="true"` is set in the XML, only translations
     * that can actually be served are shown (locally installed +
     * translations from enabled providers).
     *
     * @return  array  Array of option objects
     *
     * @since  10.1.0
     */
    protected function getOptions(): array
    {
        // Collected versions keyed by abbreviation to deduplicate
        $versions = [];

        $servableOnly = ((string) ($this->element['servable_only'] ?? '')) === 'true';

        // Determine which online provider sources are enabled
        $enabledSources = [];

        if ($servableOnly) {
            try {
                $admin       = Cwmparams::getAdmin();
                $adminParams = $admin->params ?? new Registry();
            } catch (\Exception $e) {
                $adminParams = new Registry();
            }

            $gdprMode = (int) $adminParams->get('gdpr_mode', 0) === 1;

            if (!$gdprMode && (int) $adminParams->get('provider_getbible', 1) === 1) {
                $enabledSources[] = 'getbible';
            }

            if (!$gdprMode && (int) $adminParams->get('provider_api_bible', 0) === 1
                && !empty($adminParams->get('api_bible_api_key', ''))) {
                $enabledSources[] = 'api_bible';
            }
        }

        // Detect current site/admin language to prioritize native-language versions
        $currentLang = 'en';

        try {
            $currentLang = substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2);
        } catch (\Exception $e) {
            // Default to English
        }

        // Load translations from database
        $languages = [];

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'language', 'installed', 'source']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('language') . ' ASC, ' . $db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            if (!empty($translations)) {
                foreach ($translations as $row) {
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
            }
        } catch (\Exception $e) {
            // Database table might not exist yet during install
        }

        // If no translations found at all, provide common defaults
        if (empty($versions)) {
            $versions = [
                'kjv' => 'King James Version',
                'web' => 'World English Bible',
                'asv' => 'American Standard Version',
                'ylt' => "Young's Literal Translation",
            ];
            $languages = ['kjv' => 'en', 'web' => 'en', 'asv' => 'en', 'ylt' => 'en'];
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

        // Build option objects — native language first, then others with language label
        $options = [];

        foreach ($nativeVersions as $abbr => $info) {
            $options[] = (object) [
                'value' => $abbr,
                'text'  => $info['name'] . ' (' . strtoupper($abbr) . ')',
            ];
        }

        foreach ($otherVersions as $abbr => $info) {
            $langName  = self::LANGUAGE_NAMES[$info['lang']] ?? strtoupper($info['lang']);
            $options[] = (object) [
                'value' => $abbr,
                'text'  => $info['name'] . ' (' . strtoupper($abbr) . ') — ' . $langName,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
