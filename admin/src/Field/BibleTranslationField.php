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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible Translation selection field.
 *
 * Shows installed translations from #__bsms_bible_translations.
 * Falls back to common abbreviations if no local translations are installed.
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
        $options = [];

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName(['abbreviation', 'name', 'language', 'installed']))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->order($db->quoteName('name') . ' ASC');
            $db->setQuery($query);
            $translations = $db->loadObjectList();

            if (!empty($translations)) {
                foreach ($translations as $row) {
                    $label = $row->name;

                    if ((int) $row->installed === 1) {
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
                'asvd' => 'American Standard Version',
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
