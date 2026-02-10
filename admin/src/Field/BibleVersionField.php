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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible Version selection field.
 *
 * Provider-agnostic, searchable version picker that aggregates available
 * translations from all enabled providers. No provider labels are shown —
 * just the version name and abbreviation.
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
     * The layout used to render the field (searchable select).
     *
     * @var  string
     * @since  10.1.0
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

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
        'asvd' => 'American Standard Version',
        'ylt'  => "Young's Literal Translation",
        'hcsb' => 'Holman Christian Standard Bible',
        'amp'  => 'Amplified Bible',
        'cev'  => 'Contemporary English Version',
        'msg'  => 'The Message',
        'gnt'  => 'Good News Translation',
        'web'  => 'World English Bible',
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
            try {
                $admin  = Cwmparams::getAdmin();
                $params = $admin->params ?? null;

                if ($params) {
                    $default = $params->get('default_bible_version', '');

                    if (!empty($default)) {
                        $this->value = $default;
                    }
                }
            } catch (\Exception $e) {
                // Ignore — no admin params available
            }
        }

        return $result;
    }

    /**
     * Get the field options.
     *
     * Aggregates translations from all enabled providers into a single
     * deduplicated, provider-agnostic list sorted alphabetically.
     *
     * @return  array  Array of option objects
     *
     * @since  10.1.0
     */
    protected function getOptions(): array
    {
        // Collected versions keyed by abbreviation to deduplicate
        $versions = [];

        // Determine which providers are enabled from admin params
        $providerGetbible = true;
        $providerApiBible = false;

        try {
            $admin  = Cwmparams::getAdmin();
            $params = $admin->params ?? null;

            if ($params) {
                $gdprMode         = (int) $params->get('gdpr_mode', 0) === 1;
                $providerGetbible = !$gdprMode && (int) $params->get('provider_getbible', 1) === 1;
                $providerApiBible = !$gdprMode && (int) $params->get('provider_api_bible', 0) === 1;
            }
        } catch (\Exception $e) {
            // Defaults apply
        }

        // Detect current site/admin language to prioritize native-language versions
        $currentLang = 'en';

        try {
            $currentLang = substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2);
        } catch (\Exception $e) {
            // Default to English
        }

        // Load translations from database (covers local + getbible + api_bible sources)
        $languages = [];

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'installed', 'source', 'language']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            if (!empty($translations)) {
                foreach ($translations as $row) {
                    $isInstalled = (int) $row->installed === 1;
                    $isGetbible  = $row->source === 'getbible';
                    $isApiBible  = $row->source === 'api_bible';

                    // Skip if the provider that can serve this is disabled
                    if (!$isInstalled && $isGetbible && !$providerGetbible) {
                        continue;
                    }

                    if (!$isInstalled && $isApiBible && !$providerApiBible) {
                        continue;
                    }

                    $abbr = $row->abbreviation;

                    // Only add if not already collected (first occurrence wins — DB has richer names)
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
                'kjv'  => 'King James Version',
                'web'  => 'World English Bible',
                'asvd' => 'American Standard Version',
                'ylt'  => "Young's Literal Translation",
            ];
        }

        // Sort: browser language versions first (alphabetically), then others (alphabetically)
        $nativeVersions = [];
        $otherVersions  = [];

        foreach ($versions as $abbr => $name) {
            $lang = substr($languages[$abbr] ?? '', 0, 2);

            if ($lang === $currentLang) {
                $nativeVersions[$abbr] = $name;
            } else {
                $otherVersions[$abbr] = $name;
            }
        }

        asort($nativeVersions);
        asort($otherVersions);

        // Build option objects — native language first, then others
        $options = [];

        foreach ($nativeVersions as $abbr => $name) {
            $options[] = (object) [
                'value' => $abbr,
                'text'  => $name . ' (' . strtoupper($abbr) . ')',
            ];
        }

        foreach ($otherVersions as $abbr => $name) {
            $options[] = (object) [
                'value' => $abbr,
                'text'  => $name . ' (' . strtoupper($abbr) . ')',
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
