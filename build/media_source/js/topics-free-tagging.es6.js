/**
 * Topics field — Choices.js free-tagging support.
 *
 * Reads configuration from Joomla.getOptions('com_proclaim.topicsField'):
 *   - fieldId:        DOM id of the <select> element
 *   - existingTopics: object mapping lowercase topic names to IDs
 *   - addItemText:    placeholder text for the input
 *
 * @package    Proclaim.Admin
 * @subpackage Field
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.2.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const opts = Joomla.getOptions('com_proclaim.topicsField');
    if (!opts) {
        return;
    }

    const selectEl = document.getElementById(opts.fieldId);
    const hiddenInput = document.getElementById(opts.fieldId + '_input');
    if (!selectEl || !hiddenInput) {
        return;
    }

    const fancySelect = selectEl.closest('joomla-field-fancy-select');
    if (!fancySelect) {
        return;
    }

    const existingTopics = opts.existingTopics || {};
    const addItemText = opts.addItemText || '';

    /**
     * Sync hidden input with current Choices.js selections.
     *
     * @param {object} choices  The Choices.js instance
     */
    function syncHiddenInput(choices) {
        const items = choices.getValue();
        let values = [];

        if (Array.isArray(items)) {
            values = items.map(function (item) {
                return item.value || item;
            });
        } else if (items && items.value) {
            values = [items.value];
        }

        hiddenInput.value = values.join(',');
    }

    // Wait for Choices.js to initialise
    const checkChoices = setInterval(function () {
        if (!fancySelect.choicesInstance) {
            return;
        }

        clearInterval(checkChoices);
        const choices = fancySelect.choicesInstance;

        // Sync on change, addItem, removeItem events
        selectEl.addEventListener('change', function () {
            syncHiddenInput(choices);
        });
        selectEl.addEventListener('addItem', function () {
            setTimeout(function () { syncHiddenInput(choices); }, 10);
        });
        selectEl.addEventListener('removeItem', function () {
            setTimeout(function () { syncHiddenInput(choices); }, 10);
        });

        // Sync before form submission
        const form = selectEl.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                syncHiddenInput(choices);
            });
        }

        // Listen for Enter key to add new items
        const inputEl = fancySelect.querySelector('input.choices__input');
        if (inputEl) {
            inputEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const value = this.value.trim();
                    if (value && !existingTopics[value.toLowerCase()]) {
                        e.preventDefault();
                        e.stopPropagation();

                        choices.setChoices([{
                            value,
                            label: value,
                            selected: true,
                        }], 'value', 'label', false);

                        this.value = '';
                        choices.hideDropdown();
                        setTimeout(function () { syncHiddenInput(choices); }, 10);
                    }
                }
            });

            if (addItemText) {
                inputEl.setAttribute('placeholder', addItemText);
            }
            inputEl.style.width = '150px';
            inputEl.style.minWidth = '150px';
        }

        // Initial sync
        syncHiddenInput(choices);
    }, 100);

    // Clear interval after 5 seconds to prevent memory leak
    setTimeout(function () { clearInterval(checkChoices); }, 5000);
});
