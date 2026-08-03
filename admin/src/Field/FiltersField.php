<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Text Filters form field.
 *
 * @since  1.6
 */
class FiltersField extends FormField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since    1.6
     */
    public $type = 'Filters';

    /**
     * Method to get the field input markup.
     *
     * @return  string   The field input markup.
     *
     * @throws \Exception
     * @since    1.6
     */
    #[\Override]
    protected function getInput(): string
    {
        // Add translation string for notification
        Text::script('COM_CONFIG_TEXT_FILTERS_NOTE');

        // Add Javascript
        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('com_config.filters');

        // Get the available user groups.
        $groups = $this->getUserGroups();

        // The field declares filter="" so the stored param arrives untouched, and
        // it is not always the array the markup below assumes.
        $this->value = self::normaliseStoredValue($this->value);

        // Build the form control.
        $html = [];

        // Expandable notes — matches the joomla.content.text_filters layout style.
        $html[] = '<details>';
        $html[] = '    <summary class="filter-notes">' . Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</summary>';
        $html[] = '    <div class="filter-notes">' . Text::_('JGLOBAL_FILTER_TYPE_DESC') . '</div>';
        $html[] = '</details>';
        $html[] = '<details>';
        $html[] = '    <summary class="filter-notes">' . Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</summary>';
        $html[] = '    <div class="filter-notes">' . Text::_('JGLOBAL_FILTER_TAGS_DESC') . '</div>';
        $html[] = '</details>';
        $html[] = '<details>';
        $html[] = '    <summary class="filter-notes">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</summary>';
        $html[] = '    <div class="filter-notes">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_DESC') . '</div>';
        $html[] = '</details>';

        $html[] = '<div class="table-responsive">';

        // Open the table.
        $html[] = '<table id="filter-config" class="table">';
        $html[] = '<caption class="visually-hidden">' . Text::_('COM_CONFIG_TEXT_FILTERS') . '</caption>';

        // The table heading.
        $html[] = '	<thead>';
        $html[] = '	<tr>';
        $html[] = '		<th scope="col">';
        $html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_GROUPS_LABEL') . '</span>';
        $html[] = '		</th>';
        $html[] = '		<th scope="col">';
        $html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</span>';
        $html[] = '		</th>';
        $html[] = '		<th scope="col">';
        $html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</span>';
        $html[] = '		</th>';
        $html[] = '		<th scope="col">';
        $html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</span>';
        $html[] = '		</th>';
        $html[] = '	</tr>';
        $html[] = '	</thead>';

        // The table body.
        $html[] = '	<tbody>';

        foreach ($groups as $group) {
            $this->value[$group->value] = self::normaliseGroupFilter($this->value[$group->value] ?? null);

            $group_filter = $this->value[$group->value];

            $html[] = '	<tr>';
            $html[] = '		<th class="acl-groups left" scope="row">';
            $html[] = '			' . LayoutHelper::render(
                'joomla.html.treeprefix',
                ['level' => $group->level + 1]
            ) . htmlspecialchars($group->text, ENT_QUOTES);
            $html[] = '		</th>';
            $html[] = '		<td>';
            $html[] = '			<label for="' . $this->id . $group->value . '_filter_type" class="visually-hidden">'
                . Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</label>';
            $html[] = '				<select'
                . ' name="' . $this->name . '[' . $group->value . '][filter_type]"'
                . ' id="' . $this->id . $group->value . '_filter_type"'
                . ' data-parent="' . ($group->parent) . '" '
                . ' data-id="' . ($group->value) . '" '
                . ' class="novalidate form-select"'
                . '>';
            $html[] = '					<option value="BL"' . ($group_filter['filter_type'] == 'BL' ? ' selected="selected"' : '') . '>'
                . Text::_('COM_CONFIG_FIELD_FILTERS_DEFAULT_FORBIDDEN_LIST') . '</option>';
            $html[] = '					<option value="CBL"' . ($group_filter['filter_type'] == 'CBL' ? ' selected="selected"' : '') . '>'
                . Text::_('COM_CONFIG_FIELD_FILTERS_CUSTOM_FORBIDDEN_LIST') . '</option>';
            $html[] = '					<option value="WL"' . ($group_filter['filter_type'] == 'WL' ? ' selected="selected"' : '') . '>'
                . Text::_('COM_CONFIG_FIELD_FILTERS_ALLOWED_LIST') . '</option>';
            $html[] = '					<option value="NH"' . ($group_filter['filter_type'] == 'NH' ? ' selected="selected"' : '') . '>'
                . Text::_('COM_CONFIG_FIELD_FILTERS_NO_HTML') . '</option>';
            $html[] = '					<option value="NONE"' . ($group_filter['filter_type'] == 'NONE' ? ' selected="selected"' : '') . '>'
                . Text::_('COM_CONFIG_FIELD_FILTERS_NO_FILTER') . '</option>';
            $html[] = '				</select>';
            $html[] = '		</td>';
            $html[] = '		<td>';
            $html[] = '			<label for="' . $this->id . $group->value . '_filter_tags" class="visually-hidden">'
                . Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</label>';
            $html[] = '				<input'
                . ' name="' . $this->name . '[' . $group->value . '][filter_tags]"'
                . ' type="text"'
                . ' id="' . $this->id . $group->value . '_filter_tags" class="novalidate form-control"'
                . ' value="' . htmlspecialchars($group_filter['filter_tags'], ENT_QUOTES) . '"'
                . '>';
            $html[] = '		</td>';
            $html[] = '		<td>';
            $html[] = '			<label for="' . $this->id . $group->value . '_filter_attributes"'
                . ' class="visually-hidden">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</label>';
            $html[] = '				<input'
                . ' name="' . $this->name . '[' . $group->value . '][filter_attributes]"'
                . ' type="text"'
                . ' id="' . $this->id . $group->value . '_filter_attributes" class="novalidate form-control"'
                . ' value="' . htmlspecialchars($group_filter['filter_attributes'], ENT_QUOTES) . '"'
                . '>';
            $html[] = '		</td>';
            $html[] = '	</tr>';
        }

        $html[] = '	</tbody>';

        // Close the table and table-responsive wrapper.
        $html[] = '</table>';
        $html[] = '</div>';

        return implode("\n", $html);
    }

    /**
     * Coerce the stored field value into an array keyed by user group id.
     *
     * The field declares `filter=""`, so whatever sits in the component params
     * reaches getInput() untouched — and it is not reliably an array. Registry
     * returns a stdClass when the stored JSON is an object, and a plain string
     * when the param was saved as one.
     *
     * A string is the dangerous case: indexing it with a numeric group id yields
     * a single character rather than failing, and reading a named key off that
     * character is fatal —
     *
     *     Cannot access offset of type string on string
     *
     * which is how this surfaced, as a production site unable to open its own
     * settings screen at all.
     *
     * @param   mixed  $value  Raw value from the form/params.
     *
     * @return  array<array-key, mixed>
     *
     * @since   10.3.5
     */
    protected static function normaliseStoredValue(mixed $value): array
    {
        if (\is_object($value)) {
            $value = get_object_vars($value);
        }

        return \is_array($value) ? $value : [];
    }

    /**
     * Coerce one group's stored entry into a complete filter array.
     *
     * Presence is not enough to rely on: an entry saved as a string passes an
     * isset() check and then fatals on the first named-key read. An object entry
     * is converted rather than replaced, so a site's saved filters are not
     * silently discarded, and any missing key is defaulted so a partial entry is
     * as safe as an absent one.
     *
     * @param   mixed  $entry  Raw per-group entry.
     *
     * @return  array{filter_type: string, filter_tags: string, filter_attributes: string}
     *
     * @since   10.3.5
     */
    protected static function normaliseGroupFilter(mixed $entry): array
    {
        if (\is_object($entry)) {
            $entry = get_object_vars($entry);
        }

        if (!\is_array($entry)) {
            $entry = [];
        }

        return [
            'filter_type' => \is_string($entry['filter_type'] ?? null) && $entry['filter_type'] !== ''
                ? $entry['filter_type']
                : 'BL',
            'filter_tags'       => \is_string($entry['filter_tags'] ?? null) ? $entry['filter_tags'] : '',
            'filter_attributes' => \is_string($entry['filter_attributes'] ?? null) ? $entry['filter_attributes'] : '',
        ];
    }

    /**
     * A helper to get the list of user groups.
     *
     * @return  array
     *
     * @since    1.6
     */
    protected function getUserGroups(): array
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('a.id', 'value') . ', ' . $db->quoteName('a.title', 'text') . ', COUNT(DISTINCT ' . $db->quoteName('b.id') . ') AS ' . $db->quoteName('level') . ', ' . $db->quoteName('a.parent_id', 'parent'))
            ->from($db->quoteName('#__usergroups', 'a'))
            ->join('LEFT', $db->quoteName('#__usergroups', 'b') . ' ON ' . $db->quoteName('a.lft') . ' > ' . $db->quoteName('b.lft') . ' AND ' . $db->quoteName('a.rgt') . ' < ' . $db->quoteName('b.rgt'))
            ->group($db->quoteName(['a.id', 'a.title', 'a.lft']))
            ->order($db->quoteName('a.lft') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
