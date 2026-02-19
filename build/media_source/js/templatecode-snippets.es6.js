/**
 * Proclaim Template Code Snippet Insertion Panel
 *
 * Handles inserting PHP code snippets into the CodeMirror editor
 * and filtering snippet categories by the selected template type.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */

((document) => {
    'use strict';

    /**
   * Insert text into the Joomla editor at the cursor position.
   *
   * @param {string} editorId  The editor field name (e.g. 'jform_templatecode')
   * @param {string} text      The snippet text to insert
   */
    const insertSnippet = (editorId, text) => {
        const editor = Joomla.editors.instances[editorId];

        if (!editor) {
            return;
        }

        // Joomla's CodeMirror adapter provides replaceSelection
        if (typeof editor.replaceSelection === 'function') {
            editor.replaceSelection(text);
            return;
        }

        // Fallback: getValue/setValue (appends at end)
        if (typeof editor.getValue === 'function' && typeof editor.setValue === 'function') {
            const current = editor.getValue();
            editor.setValue(current + text);
        }
    };

    /**
   * Filter visible snippet categories based on the selected template type.
   *
   * @param {string} typeValue  The selected type value (1-7)
   */
    const filterSnippetsByType = (typeValue) => {
        const categories = document.querySelectorAll('.snippet-category');

        categories.forEach((cat) => {
            const types = cat.dataset.types ? cat.dataset.types.split(',') : [];

            if (types.length === 0 || types.includes(typeValue)) {
                cat.style.display = '';
            } else {
                cat.style.display = 'none';
            }
        });
    };

    /**
   * Initialize snippet panel interactions.
   */
    const init = () => {
        const panel = document.getElementById('snippetPanel');

        if (!panel) {
            return;
        }

        // Handle snippet button clicks
        panel.addEventListener('click', (e) => {
            const btn = e.target.closest('.snippet-insert-btn');

            if (!btn) {
                return;
            }

            e.preventDefault();
            const { snippet } = btn.dataset;

            if (snippet) {
                insertSnippet('jform_templatecode', snippet);

                // Brief visual feedback
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                setTimeout(() => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 400);
            }
        });

        // Handle type filtering
        const typeField = document.getElementById('jform_type');
        const { currentType } = panel.dataset;

        if (typeField) {
            const applyFilter = () => filterSnippetsByType(typeField.value);

            typeField.addEventListener('change', applyFilter);

            // Apply initial filter from the select value
            applyFilter();
        } else if (currentType) {
            // Fallback: use data attribute when no select exists
            filterSnippetsByType(currentType);
        }
    };

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(document);
