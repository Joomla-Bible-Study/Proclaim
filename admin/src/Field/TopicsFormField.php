<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Form Field class for the Topics using Choices.js with free tagging support
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TopicsFormField extends FormField
{
    /**
     * Set type to TopicsForm
     *
     * @var string
     *
     * @since 9.0.0
     */
    protected $type = 'TopicsForm';

    /**
     * Get input form - renders a Choices.js enabled select with free tagging
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    #[\Override]
    protected function getInput(): string
    {
        // Load Joomla's web assets for proper styling
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->usePreset('choicesjs');
        $wa->useScript('webcomponent.field-fancy-select');

        // Load topics field CSS
        $wa->useStyle('com_proclaim.topics-field');

        // Get all available topics
        $allTopics = $this->getAllTopics();

        // Get selected topic IDs for this message
        $selectedIds = $this->getSelectedTopicIds();

        // Build the select element wrapped in Joomla's fancy-select component
        $html = $this->buildSelectHtml($allTopics, $selectedIds);

        // Add custom script for free tagging support
        $this->addFreeTaggingScript($allTopics);

        return $html;
    }

    /**
     * Get all published topics
     *
     * @return array
     *
     * @throws \Exception
     * @since 10.1.0
     */
    protected function getAllTopics(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select($db->quoteName('id') . ', ' . $db->quoteName('topic_text') . ', ' . $db->quoteName('params', 'topic_params'))
            ->from($db->quoteName('#__bsms_topics'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('topic_text') . ' ASC');

        $db->setQuery($query);
        $topics  = $db->loadObjectList();
        $options = [];

        if ($topics) {
            foreach ($topics as $topic) {
                $text      = Cwmtranslated::getTopicItemTranslated($topic);
                $options[] = [
                    'id'   => (int)$topic->id,
                    'text' => $text,
                ];
            }
        }

        // Sort alphabetically
        usort($options, static function ($a, $b) {
            return strcmp($a['text'], $b['text']);
        });

        return $options;
    }

    /**
     * Get selected topic IDs for current message
     *
     * @return array
     *
     * @throws \Exception
     * @since 10.1.0
     */
    protected function getSelectedTopicIds(): array
    {
        // Try to get message ID from form first, then from input
        $messageId = $this->form->getValue('id');

        if (!$messageId) {
            $input     = Factory::getApplication()->getInput();
            $messageId = $input->getInt('id', 0);
        }

        if (!$messageId) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select($db->quoteName('topic_id'))
            ->from($db->quoteName('#__bsms_studytopics'))
            ->where($db->quoteName('study_id') . ' = ' . (int)$messageId);

        $db->setQuery($query);
        $result = $db->loadColumn();

        // Convert to integers for proper comparison
        if ($result) {
            return array_map('intval', $result);
        }

        return [];
    }

    /**
     * Build the HTML select element with Joomla fancy-select wrapper
     *
     * @param   array  $allTopics    All available topics
     * @param   array  $selectedIds  Selected topic IDs
     *
     * @return string
     *
     * @since 10.1.0
     */
    protected function buildSelectHtml(array $allTopics, array $selectedIds): string
    {
        $hint = Text::_('JBS_CMN_TOPIC_TAG');

        // Hidden input to store actual values for form submission
        // Use a specific name that won't conflict with fancy-select
        $html = '<input type="hidden" id="' . $this->id . '_input" name="jform[topic_ids]" value="' . implode(',', $selectedIds) . '">';

        // Start with Joomla's fancy-select web component wrapper
        $html .= '<joomla-field-fancy-select>';

        // Build the select element (without name - hidden input handles submission)
        $html .= '<select';
        $html .= ' id="' . $this->id . '"';
        $html .= ' multiple="multiple"';
        $html .= ' class="form-select proclaim-topics-field"';
        $html .= ' data-placeholder="' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '"';
        $html .= '>';

        // Add options
        foreach ($allTopics as $topic) {
            $isSelected = \in_array($topic['id'], $selectedIds, false) ? ' selected="selected"' : '';
            $html .= '<option value="' . $topic['id'] . '"' . $isSelected . '>';
            $html .= htmlspecialchars($topic['text'], ENT_QUOTES, 'UTF-8');
            $html .= '</option>';
        }

        $html .= '</select>';
        $html .= '</joomla-field-fancy-select>';

        return $html;
    }

    /**
     * Add script to enable free tagging functionality
     *
     * @param   array  $allTopics  All available topics for reference
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.1.0
     */
    protected function addFreeTaggingScript(array $allTopics): void
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        $addItemText = Text::_('JBS_CMN_PRESS_ENTER_ADD', true);

        // Create a map of existing topic names to IDs for duplicate detection
        $existingTopics = [];
        foreach ($allTopics as $topic) {
            $existingTopics[mb_strtolower($topic['text'])] = $topic['id'];
        }
        $existingJson = json_encode($existingTopics, JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = "
            document.addEventListener('DOMContentLoaded', function() {
                const selectEl = document.getElementById('" . $this->id . "');
                const hiddenInput = document.getElementById('" . $this->id . "_input');
                if (!selectEl || !hiddenInput) return;

                const fancySelect = selectEl.closest('joomla-field-fancy-select');
                if (!fancySelect) return;

                // Function to sync hidden input with current selections
                function syncHiddenInput(choices) {
                    // Get all selected items
                    const items = choices.getValue();
                    let values = [];

                    if (Array.isArray(items)) {
                        values = items.map(function(item) {
                            return item.value || item;
                        });
                    } else if (items && items.value) {
                        values = [items.value];
                    }

                    hiddenInput.value = values.join(',');
                    console.log('Topics synced:', hiddenInput.value);
                }

                // Wait for Choices.js to initialize
                const checkChoices = setInterval(function() {
                    if (fancySelect.choicesInstance) {
                        clearInterval(checkChoices);
                        const choices = fancySelect.choicesInstance;
                        const existingTopics = " . $existingJson . ";

                        // Sync on any change event
                        selectEl.addEventListener('change', function() {
                            syncHiddenInput(choices);
                        });

                        // Also sync on addItem and removeItem events
                        selectEl.addEventListener('addItem', function() {
                            setTimeout(function() { syncHiddenInput(choices); }, 10);
                        });
                        selectEl.addEventListener('removeItem', function() {
                            setTimeout(function() { syncHiddenInput(choices); }, 10);
                        });

                        // Sync before form submission
                        const form = selectEl.closest('form');
                        if (form) {
                            form.addEventListener('submit', function() {
                                syncHiddenInput(choices);
                            });
                        }

                        // Listen for search/input to enable adding new items
                        const inputEl = fancySelect.querySelector('input.choices__input');
                        if (inputEl) {
                            inputEl.addEventListener('keydown', function(e) {
                                if (e.key === 'Enter') {
                                    const value = this.value.trim();
                                    if (value && !existingTopics[value.toLowerCase()]) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        // Add as new choice and select it
                                        choices.setChoices([{
                                            value: value,
                                            label: value,
                                            selected: true
                                        }], 'value', 'label', false);

                                        // Clear input and sync
                                        this.value = '';
                                        choices.hideDropdown();
                                        setTimeout(function() { syncHiddenInput(choices); }, 10);
                                    }
                                }
                            });

                            // Show hint for adding new items
                            inputEl.setAttribute('placeholder', '" . $addItemText . "');
                            // Fix width to show full placeholder
                            inputEl.style.width = '150px';
                            inputEl.style.minWidth = '150px';
                        }

                        // Initial sync
                        syncHiddenInput(choices);
                    }
                }, 100);

                // Clear interval after 5 seconds to prevent memory leak
                setTimeout(function() { clearInterval(checkChoices); }, 5000);
            });
        ";

        $wa->addInlineScript($script);
    }
}
