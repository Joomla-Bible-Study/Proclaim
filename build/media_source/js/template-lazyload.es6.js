/**
 * @package     Proclaim
 * @subpackage  com_proclaim
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Template form lazy-loading functionality
 * Loads fieldsets on-demand when accordion sections are expanded
 */

(function () {
    'use strict';

    const loadedFieldsets = new Set();
    const loadingFieldsets = new Set();
    const fieldsetHtmlCache = new Map();

    /**
     * Load a fieldset via AJAX
     * @param {string} fieldsetName - The fieldset name to load
     * @param {HTMLElement} container - The container element to populate
     * @param {number} templateId - The template ID for data binding
     * @returns {Promise} Promise that resolves when fieldset is loaded
     */
    function loadFieldset(fieldsetName, container, templateId) {
        return new Promise((resolve, reject) => {
            // If already loaded, use cached HTML
            if (loadedFieldsets.has(fieldsetName) && fieldsetHtmlCache.has(fieldsetName)) {
                container.innerHTML = fieldsetHtmlCache.get(fieldsetName);

                // Initialize any Joomla form elements
                if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                    Joomla.initCustomSelect(container);
                }

                // Trigger custom event for any additional initialization
                container.dispatchEvent(new CustomEvent('fieldsetLoaded', {
                    bubbles: true,
                    detail: { fieldset: fieldsetName }
                }));

                resolve({ alreadyLoaded: true, fieldset: fieldsetName });
                return;
            }

            if (loadingFieldsets.has(fieldsetName)) {
                // Wait for existing load to complete
                const checkInterval = setInterval(() => {
                    if (loadedFieldsets.has(fieldsetName) && fieldsetHtmlCache.has(fieldsetName)) {
                        clearInterval(checkInterval);
                        container.innerHTML = fieldsetHtmlCache.get(fieldsetName);

                        // Initialize any Joomla form elements
                        if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                            Joomla.initCustomSelect(container);
                        }

                        resolve({ alreadyLoaded: true, fieldset: fieldsetName });
                    } else if (!loadingFieldsets.has(fieldsetName)) {
                        clearInterval(checkInterval);
                        reject(new Error('Fieldset load was cancelled'));
                    }
                }, 100);
                return;
            }

            loadingFieldsets.add(fieldsetName);

            // Show loading indicator
            container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

            const url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json' +
                '&fieldset=' + encodeURIComponent(fieldsetName) +
                '&id=' + templateId +
                '&' + Joomla.getOptions('csrf.token', '') + '=1';

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingFieldsets.delete(fieldsetName);

                if (data.success) {
                    loadedFieldsets.add(fieldsetName);
                    fieldsetHtmlCache.set(fieldsetName, data.html);
                    container.innerHTML = data.html;

                    // Initialize any Joomla form elements
                    if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                        Joomla.initCustomSelect(container);
                    }

                    // Initialize TinyMCE editors in loaded content
                    initTinyMCEEditors(container);

                    // Trigger custom event for any additional initialization
                    container.dispatchEvent(new CustomEvent('fieldsetLoaded', {
                        bubbles: true,
                        detail: { fieldset: fieldsetName }
                    }));

                    resolve({ success: true, fieldset: fieldsetName });
                } else {
                    container.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Failed to load content') + '</div>';
                    reject(new Error(data.error || 'Failed to load content'));
                }
            })
            .catch(error => {
                loadingFieldsets.delete(fieldsetName);
                container.innerHTML = '<div class="alert alert-danger">Error loading content: ' + error.message + '</div>';
                console.error('Fieldset load error:', error);
                reject(error);
            });
        });
    }

    /**
     * Initialize TinyMCE editors in dynamically loaded content
     * @param {HTMLElement} container - Container with editor textareas
     */
    function initTinyMCEEditors(container) {
        if (typeof window.tinymce === 'undefined') {
            return;
        }

        const textareas = container.querySelectorAll('textarea.mce_editable');

        textareas.forEach(function(textarea) {
            const editorId = textarea.id;
            if (!editorId) { return; }

            // Check if already initialized
            const existingEditor = window.tinymce.get(editorId);
            if (existingEditor) {
                // If the editor's container is detached, remove it
                const editorContainer = existingEditor.getContainer();
                if (!editorContainer || !document.body.contains(editorContainer)) {
                    existingEditor.remove();
                } else {
                    return;
                }
            }

            // Simple config for editors
            const config = {
                target: textarea,
                menubar: true,
                toolbar: 'undo redo | bold italic underline | bullist numlist | link',
                plugins: 'link lists',
                branding: false,
                promotion: false,
                height: 300,
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            };

            // Initialize TinyMCE
            window.tinymce.init(config).then(function(editors) {
                if (editors && editors[0]) {
                    const editor = editors[0];

                    // Enable the toggle button
                    const wrapper = textarea.closest('.js-editor-tinymce');
                    const toggleBtn = wrapper ? wrapper.querySelector('.js-tiny-toggler-button') : null;
                    if (toggleBtn) {
                        toggleBtn.disabled = false;
                        toggleBtn.addEventListener('click', function() {
                            if (editor.isHidden()) {
                                editor.show();
                            } else {
                                editor.hide();
                            }
                        });
                    }

                    // Register with Joomla's editor system
                    if (window.Joomla && window.Joomla.editors && window.Joomla.editors.instances) {
                        window.Joomla.editors.instances[editorId] = {
                            id: editorId,
                            getValue: function() { return editor.getContent(); },
                            setValue: function(val) { editor.setContent(val); },
                            getSelection: function() { return editor.selection.getContent(); },
                            replaceSelection: function(val) { editor.execCommand('mceInsertContent', false, val); },
                            disable: function(state) { editor.mode.set(state ? 'readonly' : 'design'); }
                        };
                    }
                }
            });
        });
    }

    /**
     * Check if a fieldset has been loaded
     * @param {string} fieldsetName - The fieldset name to check
     * @returns {boolean}
     */
    function isFieldsetLoaded(fieldsetName) {
        return loadedFieldsets.has(fieldsetName);
    }

    /**
     * Check if a fieldset is currently loading
     * @param {string} fieldsetName - The fieldset name to check
     * @returns {boolean}
     */
    function isFieldsetLoading(fieldsetName) {
        return loadingFieldsets.has(fieldsetName);
    }

    /**
     * Initialize lazy loading for accordion items
     */
    function initAccordionLazyLoad() {
        // Find all accordion items with lazy-load data attribute
        document.querySelectorAll('[data-lazy-fieldset]').forEach(function(accordion) {
            const fieldsetName = accordion.dataset.lazyFieldset;
            const collapseTarget = accordion.querySelector('.accordion-collapse');
            const contentContainer = accordion.querySelector('.accordion-body');
            const templateId = parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

            if (!collapseTarget || !contentContainer) {
                return;
            }

            // Load when accordion is shown
            collapseTarget.addEventListener('show.bs.collapse', function() {
                loadFieldset(fieldsetName, contentContainer, templateId).catch(() => {
                    // Error already displayed in UI
                });
            });

            // If accordion is already shown (first one), load immediately
            if (collapseTarget.classList.contains('show')) {
                loadFieldset(fieldsetName, contentContainer, templateId).catch(() => {
                    // Error already displayed in UI
                });
            }
        });
    }

    /**
     * Initialize lazy loading for tabs
     */
    function initTabLazyLoad() {
        // Find all tab panes with lazy-load data attribute
        document.querySelectorAll('[data-lazy-tab]').forEach(function(tabPane) {
            const fieldsets = tabPane.dataset.lazyTab.split(',');
            const templateId = parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

            // Find the tab button that controls this pane
            const tabId = tabPane.id;
            const tabButton = document.querySelector('[data-bs-target="#' + tabId + '"], [href="#' + tabId + '"]');

            if (!tabButton) {
                return;
            }

            // Load when tab is shown
            tabButton.addEventListener('shown.bs.tab', function() {
                fieldsets.forEach(function(fieldsetName) {
                    const container = tabPane.querySelector('[data-fieldset-container="' + fieldsetName.trim() + '"]');
                    if (container) {
                        loadFieldset(fieldsetName.trim(), container, templateId).catch(() => {
                            // Error already displayed in UI
                        });
                    }
                });
            });

            // If tab is already active, load immediately
            if (tabPane.classList.contains('active') || tabPane.classList.contains('show')) {
                fieldsets.forEach(function(fieldsetName) {
                    const container = tabPane.querySelector('[data-fieldset-container="' + fieldsetName.trim() + '"]');
                    if (container) {
                        loadFieldset(fieldsetName.trim(), container, templateId).catch(() => {
                            // Error already displayed in UI
                        });
                    }
                });
            }
        });
    }

    // Export functions for use by other modules (e.g., layout editor)
    window.ProclaimLazyLoad = {
        loadFieldset: loadFieldset,
        isFieldsetLoaded: isFieldsetLoaded,
        isFieldsetLoading: isFieldsetLoading
    };

    // Initialize lazy loading on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initAccordionLazyLoad();
            initTabLazyLoad();
        });
    } else {
        initAccordionLazyLoad();
        initTabLazyLoad();
    }
})();
