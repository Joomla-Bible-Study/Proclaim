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
use CWM\Component\Proclaim\Site\Bible\Provider\BibleGatewayProvider;
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
        $providerLocal        = true;
        $providerGetbible     = true;
        $providerBiblegateway = true;

        try {
            $admin  = Cwmparams::getAdmin();
            $params = $admin->params ?? null;

            if ($params) {
                $providerLocal        = (int) $params->get('provider_local', 1) === 1;
                $providerGetbible     = (int) $params->get('provider_getbible', 1) === 1;
                $providerBiblegateway = (int) $params->get('provider_biblegateway', 1) === 1;
            }
        } catch (\Exception $e) {
            // Defaults apply
        }

        // Load translations from database (covers local + getbible sources)
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'installed', 'source']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            if (!empty($translations)) {
                foreach ($translations as $row) {
                    $isInstalled = (int) $row->installed === 1;
                    $isGetbible  = $row->source === 'getbible';

                    // Skip if the only provider that can serve this is disabled
                    if ($isInstalled && !$providerLocal && !($isGetbible && $providerGetbible)) {
                        continue;
                    }

                    if (!$isInstalled && $isGetbible && !$providerGetbible) {
                        continue;
                    }

                    $abbr = $row->abbreviation;

                    // Only add if not already collected (first occurrence wins — DB has richer names)
                    if (!isset($versions[$abbr])) {
                        $versions[$abbr] = $row->name;
                    }
                }
            }
        } catch (\Exception $e) {
            // Database table might not exist yet during install
        }

        // Add BibleGateway versions if enabled
        if ($providerBiblegateway) {
            foreach (BibleGatewayProvider::VERSION_MAP as $abbr) {
                if (!isset($versions[$abbr])) {
                    $versions[$abbr] = self::VERSION_NAMES[$abbr] ?? strtoupper($abbr);
                }
            }
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

        // Sort alphabetically by name
        asort($versions);

        // Build option objects — show "Name (ABBR)" for clarity
        $options = [];

        foreach ($versions as $abbr => $name) {
            $options[] = (object) [
                'value' => $abbr,
                'text'  => $name . ' (' . strtoupper($abbr) . ')',
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
