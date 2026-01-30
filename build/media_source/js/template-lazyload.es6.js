/**
 * @package     Proclaim
 * @subpackage  com_proclaim
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Template form lazy-loading functionality
 * Loads fieldsets on-demand when accordion sections are expanded
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const loadedFieldsets = new Set();
    const loadingFieldsets = new Set();

    /**
     * Load a fieldset via AJAX
     * @param {string} fieldsetName - The fieldset name to load
     * @param {HTMLElement} container - The container element to populate
     * @param {number} templateId - The template ID for data binding
     */
    function loadFieldset(fieldsetName, container, templateId) {
        if (loadedFieldsets.has(fieldsetName) || loadingFieldsets.has(fieldsetName)) {
            return;
        }

        loadingFieldsets.add(fieldsetName);

        // Show loading indicator
        container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

        const url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json'
            + '&fieldset=' + encodeURIComponent(fieldsetName)
            + '&id=' + templateId
            + '&' + Joomla.getOptions('csrf.token', '') + '=1';

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
                container.innerHTML = data.html;

                // Initialize any Joomla form elements
                if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                    Joomla.initCustomSelect(container);
                }

                // Trigger custom event for any additional initialization
                container.dispatchEvent(new CustomEvent('fieldsetLoaded', {
                    bubbles: true,
                    detail: { fieldset: fieldsetName }
                }));
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Failed to load content') + '</div>';
            }
        })
        .catch(error => {
            loadingFieldsets.delete(fieldsetName);
            container.innerHTML = '<div class="alert alert-danger">Error loading content: ' + error.message + '</div>';
            console.error('Fieldset load error:', error);
        });
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
                loadFieldset(fieldsetName, contentContainer, templateId);
            });

            // If accordion is already shown (first one), load immediately
            if (collapseTarget.classList.contains('show')) {
                loadFieldset(fieldsetName, contentContainer, templateId);
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
                        loadFieldset(fieldsetName.trim(), container, templateId);
                    }
                });
            });

            // If tab is already active, load immediately
            if (tabPane.classList.contains('active') || tabPane.classList.contains('show')) {
                fieldsets.forEach(function(fieldsetName) {
                    const container = tabPane.querySelector('[data-fieldset-container="' + fieldsetName.trim() + '"]');
                    if (container) {
                        loadFieldset(fieldsetName.trim(), container, templateId);
                    }
                });
            }
        });
    }

    // Initialize lazy loading
    initAccordionLazyLoad();
    initTabLazyLoad();
});
