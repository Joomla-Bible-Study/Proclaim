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

use CWM\Component\Proclaim\Administrator\Helper\CwmbibleVersionHelper;
use Joomla\CMS\Form\Field\ListField;

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
 * The translation-aggregation/grouping/sorting logic lives in
 * CwmbibleVersionHelper; this field is a thin Joomla-form adapter over it.
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

        // If no value is set (new record), use the plugin default
        if ($result && ($this->value === null || $this->value === '')) {
            $this->value = CwmbibleVersionHelper::getDefaultVersion();
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
        $servableOnly = ((string) ($this->element['servable_only'] ?? '')) === 'true';

        $options = array_map(
            static fn (array $option) => (object) $option,
            CwmbibleVersionHelper::getVersionOptions($servableOnly)
        );

        return array_merge(parent::getOptions(), $options);
    }
}
