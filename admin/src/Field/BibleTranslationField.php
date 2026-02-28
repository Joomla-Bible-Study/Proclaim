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
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible Translation selection field with language-grouped optgroups.
 *
 * Shows translations from #__bsms_bible_translations grouped by language.
 * Uses Joomla's GroupedlistField to render `<optgroup>` elements, and
 * supports Choices.js search via `layout="joomla.form.field.list-fancy-select"`.
 *
 * When the XML attribute `servable_only="true"` is set, only translations
 * that can actually be served are shown: locally installed translations
 * plus translations from enabled online providers.
 *
 * @since  10.1.0
 */
class BibleTranslationField extends GroupedlistField
{
    /**
     * The field type.
     *
     * @var  string
     * @since  10.1.0
     */
    protected $type = 'BibleTranslation';

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
     * Get the field option groups.
     *
     * Returns translations grouped by language as `<optgroup>` sections.
     * The admin's current language group is sorted first.
     *
     * @return  array  Associative array of group label => option arrays
     *
     * @since  10.1.0
     */
    protected function getGroups(): array
    {
        $groups       = [];
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

        // Detect admin language for sorting priority
        $currentLang = 'en';

        try {
            $currentLang = substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2);
        } catch (\Exception $e) {
            // Default to English
        }

        // Load translations from database grouped by language
        $byLanguage = [];

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
                    $installed = (int) $row->installed === 1;

                    // In servable_only mode, skip translations that can't be served
                    if ($servableOnly && !$installed && !\in_array($row->source, $enabledSources, true)) {
                        continue;
                    }

                    $langCode = substr($row->language ?? '', 0, 2);
                    $langName = self::LANGUAGE_NAMES[$langCode] ?? ucfirst($langCode ?: 'Other');

                    $label = $row->name . ' (' . strtoupper($row->abbreviation) . ')';

                    if ($installed) {
                        $label .= ' ✓';
                    }

                    $byLanguage[$langName][] = (object) [
                        'value' => $row->abbreviation,
                        'text'  => $label,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Database table might not exist yet during install
        }

        // If no translations found, provide common defaults
        if (empty($byLanguage)) {
            $defaults = [
                'kjv' => 'King James Version',
                'web' => 'World English Bible',
                'asv' => 'American Standard Version',
                'ylt' => "Young's Literal Translation",
            ];

            foreach ($defaults as $abbr => $name) {
                $byLanguage['English'][] = (object) [
                    'value' => $abbr,
                    'text'  => $name . ' (' . strtoupper($abbr) . ')',
                ];
            }
        }

        // Sort language groups: admin language first, then alphabetical
        uksort($byLanguage, function (string $a, string $b) use ($currentLang): int {
            $aLang     = array_search($a, self::LANGUAGE_NAMES, true) ?: '';
            $bLang     = array_search($b, self::LANGUAGE_NAMES, true) ?: '';
            $aIsNative = $aLang === $currentLang;
            $bIsNative = $bLang === $currentLang;

            if ($aIsNative && !$bIsNative) {
                return -1;
            }

            if (!$aIsNative && $bIsNative) {
                return 1;
            }

            return strcasecmp($a, $b);
        });

        // Build groups array for GroupedlistField
        foreach ($byLanguage as $langName => $options) {
            $groups[$langName] = $options;
        }

        // Merge with any parent groups (adds "- Select -" placeholder from XML)
        return array_merge(parent::getGroups(), $groups);
    }
}
