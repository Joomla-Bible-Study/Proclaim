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
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible Translation selection field.
 *
 * Shows translations from #__bsms_bible_translations.
 *
 * When the XML attribute `servable_only="true"` is set, only translations
 * that can actually be served are shown: locally installed translations
 * plus translations from enabled online providers. Use this mode for the
 * admin default bible version, which must always be resolvable.
 *
 * @since  10.1.0
 */
class BibleTranslationField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     * @since  10.1.0
     */
    protected $type = 'BibleTranslation';

    /**
     * Get the field options.
     *
     * @return  array  Array of option objects
     *
     * @since  10.1.0
     */
    protected function getOptions(): array
    {
        $options      = [];
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

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'language', 'installed', 'source']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            if (!empty($translations)) {
                foreach ($translations as $row) {
                    $installed = (int) $row->installed === 1;

                    // In servable_only mode, skip translations that can't be served
                    if ($servableOnly && !$installed && !\in_array($row->source, $enabledSources, true)) {
                        continue;
                    }

                    $label = $row->name;

                    if ($installed) {
                        $label .= ' (' . Text::_('JBS_TPL_INSTALLED') . ')';
                    }

                    $options[] = (object) [
                        'value' => $row->abbreviation,
                        'text'  => $label,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Database table might not exist yet during install
        }

        // If no translations found, provide common defaults
        if (empty($options)) {
            $defaults = [
                'kjv' => 'King James Version',
                'web' => 'World English Bible',
                'asv' => 'American Standard Version',
                'ylt' => "Young's Literal Translation",
            ];

            foreach ($defaults as $abbr => $name) {
                $options[] = (object) [
                    'value' => $abbr,
                    'text'  => $name,
                ];
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
