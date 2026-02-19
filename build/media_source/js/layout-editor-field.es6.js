/**
 * Layout Editor Field Initialization
 *
 * This script initializes the Layout Editor for module/template forms.
 * It handles lazy loading and tab visibility detection.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function () {
    'use strict';

    /**
     * Check if a container is in a visible/active tab
     * @param {HTMLElement} container - The container element
     * @returns {boolean} True if visible
     */
    function isContainerVisible(container) {
        // Check for Joomla 5 joomla-tab-element
        const tabElement = container.closest('joomla-tab-element');
        if (tabElement) {
            return tabElement.hasAttribute('active');
        }

        // Check for Bootstrap tab-pane
        const tabPane = container.closest('.tab-pane');
        if (tabPane) {
            return tabPane.classList.contains('active') || tabPane.classList.contains('show');
        }

        // Check for Bootstrap accordion
        const accordion = container.closest('.accordion-collapse');
        if (accordion) {
            return accordion.classList.contains('show');
        }

        // Not in a tab/accordion, assume visible
        return true;
    }

    /**
     * Initialize the Layout Editor on a container
     * @param {HTMLElement} container - The container element
     * @param {Object} config - Configuration options
     */
    function initLayoutEditor(container, config) {
        if (!container || container.dataset.initialized) {
            return;
        }

        container.dataset.initialized = 'true';

        // Remove loading indicator
        const loading = container.querySelector('#layout-editor-loading');
        if (loading) {
            loading.remove();
        }

        // Initialize the Layout Editor
        // It reads/writes to the existing jform[params][fieldname] fields defined in XML
        if (typeof window.ProclaimLayoutEditor !== 'undefined') {
            window.proclaimLayoutEditor = window.ProclaimLayoutEditor.create(container, {
                context: config.context || 'messages',
                contexts: [config.context || 'messages'],
                showViewSettings: config.showViewSettings || false,
                showContextTabs: false,
                formId: container.closest('form')?.id || 'module-form',
                paramsPrefix: config.paramsPrefix || 'jform[params]',
            });
        } else if (typeof window.LayoutEditor !== 'undefined') {
            window.proclaimLayoutEditor = new window.LayoutEditor(container, {
                context: config.context || 'messages',
                contexts: [config.context || 'messages'],
                showViewSettings: config.showViewSettings || false,
                showContextTabs: false,
                formId: container.closest('form')?.id || 'module-form',
                paramsPrefix: config.paramsPrefix || 'jform[params]',
            });
        } else {
            console.error('Layout Editor not loaded');
        }
    }

    /**
     * Set up lazy loading - wait for tab to be shown before initializing
     * @param {HTMLElement} container - The container element
     * @param {Object} config - Configuration options
     */
    function setupLazyInit(container, config) {
        if (!container) {
            return;
        }

        // If already visible, initialize now
        if (isContainerVisible(container)) {
            initLayoutEditor(container, config);
            return;
        }

        // Listen for Joomla 5 tab events
        const joomlaTab = container.closest('joomla-tab');
        if (joomlaTab) {
            const tabElement = container.closest('joomla-tab-element');
            const initOnShow = function (event) {
                if (tabElement && (event.target === tabElement || tabElement.hasAttribute('active'))) {
                    initLayoutEditor(container, config);
                    joomlaTab.removeEventListener('joomla.tab.shown', initOnShow);
                }
            };
            joomlaTab.addEventListener('joomla.tab.shown', initOnShow);
            return;
        }

        // Listen for Bootstrap tab events
        const tabPane = container.closest('.tab-pane');
        if (tabPane && tabPane.id) {
            const tabButton = document.querySelector(`[data-bs-target="#${tabPane.id}"], [href="#${tabPane.id}"]`);
            if (tabButton) {
                tabButton.addEventListener('shown.bs.tab', () => {
                    initLayoutEditor(container, config);
                }, { once: true });
                return;
            }
        }

        // Listen for Bootstrap accordion events
        const accordion = container.closest('.accordion-collapse');
        if (accordion) {
            accordion.addEventListener('shown.bs.collapse', () => {
                initLayoutEditor(container, config);
            }, { once: true });
            return;
        }

        // Fallback: just initialize
        initLayoutEditor(container, config);
    }

    /**
     * Initialize all Layout Editor fields on the page
     */
    function initAllLayoutEditorFields() {
        // Find all Layout Editor field containers
        const containers = document.querySelectorAll('.layout-editor-container[data-layout-editor-field]');

        containers.forEach((container) => {
            // Get configuration from data attributes
            const config = {
                context: container.dataset.context || 'messages',
                showViewSettings: container.dataset.showViewSettings === 'true',
                paramsPrefix: container.dataset.paramsPrefix || 'jform[params]',
                lazyLoad: container.dataset.lazyInit === 'true',
            };

            if (config.lazyLoad) {
                setupLazyInit(container, config);
            } else {
                const scheduleInit = window.requestIdleCallback || function (cb) { setTimeout(cb, 100); };
                scheduleInit(() => {
                    initLayoutEditor(container, config);
                }, { timeout: 2000 });
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllLayoutEditorFields);
    } else {
        initAllLayoutEditorFields();
    }

    // Export for external use
    window.ProclaimLayoutEditorField = {
        init: initLayoutEditor,
        setupLazy: setupLazyInit,
        isVisible: isContainerVisible,
    };
}());
