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
    const loadingPromises = new Map();
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
                    detail: { fieldset: fieldsetName },
                }));

                resolve({ alreadyLoaded: true, fieldset: fieldsetName });
                return;
            }

            if (loadingFieldsets.has(fieldsetName)) {
                // Wait for existing load to complete via shared Promise
                const pending = loadingPromises.get(fieldsetName);
                if (pending) {
                    pending.then(() => {
                        if (fieldsetHtmlCache.has(fieldsetName)) {
                            container.innerHTML = fieldsetHtmlCache.get(fieldsetName);
                            if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                                Joomla.initCustomSelect(container);
                            }
                            resolve({ alreadyLoaded: true, fieldset: fieldsetName });
                        } else {
                            reject(new Error('Fieldset load was cancelled'));
                        }
                    }).catch(reject);
                } else {
                    reject(new Error('Fieldset load was cancelled'));
                }
                return;
            }

            loadingFieldsets.add(fieldsetName);

            // Show loading indicator
            container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

            const url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json'
                + `&fieldset=${encodeURIComponent(fieldsetName)
                }&id=${templateId
                }&${Joomla.getOptions('csrf.token', '')}=1`;

            const fetchPromise = window.ProclaimFetch.fetchJson(
                url,
                { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } },
                { timeout: 30000, retries: 1 },
            )
                .then((data) => {
                    loadingFieldsets.delete(fieldsetName);
                    loadingPromises.delete(fieldsetName);

                    if (data.success) {
                        loadedFieldsets.add(fieldsetName);
                        fieldsetHtmlCache.set(fieldsetName, data.html);
                        container.innerHTML = data.html;

                        // Initialize any Joomla form elements
                        if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                            Joomla.initCustomSelect(container);
                        }

                        // Notify Joomla that new dynamic content was loaded.
                        // Joomla 6's TinyMCE plugin listens for this event and
                        // initializes editors with correct config, license key,
                        // and dark mode support.
                        container.dispatchEvent(new CustomEvent('joomla:updated', { bubbles: true }));

                        // Trigger custom event for any additional initialization
                        container.dispatchEvent(new CustomEvent('fieldsetLoaded', {
                            bubbles: true,
                            detail: { fieldset: fieldsetName },
                        }));

                        resolve({ success: true, fieldset: fieldsetName });
                    } else {
                        const errMsg = data.error || 'Failed to load content';
                        container.replaceChildren(Object.assign(document.createElement('div'), {
                            className: 'alert alert-danger',
                            textContent: errMsg,
                        }));
                        reject(new Error(errMsg));
                    }
                })
                .catch((error) => {
                    loadingFieldsets.delete(fieldsetName);
                    loadingPromises.delete(fieldsetName);
                    container.replaceChildren(Object.assign(document.createElement('div'), {
                        className: 'alert alert-danger',
                        textContent: 'Error loading content: ' + (error.message || 'Unknown error'),
                    }));
                    console.error('Fieldset load error:', error);
                    reject(error);
                });

            loadingPromises.set(fieldsetName, fetchPromise);
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
        document.querySelectorAll('[data-lazy-fieldset]').forEach((accordion) => {
            const fieldsetName = accordion.dataset.lazyFieldset;
            const collapseTarget = accordion.querySelector('.accordion-collapse');
            const contentContainer = accordion.querySelector('.accordion-body');
            const templateId = parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

            if (!collapseTarget || !contentContainer) {
                return;
            }

            // Load when accordion is shown
            collapseTarget.addEventListener('show.bs.collapse', () => {
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
        document.querySelectorAll('[data-lazy-tab]').forEach((tabPane) => {
            const fieldsets = tabPane.dataset.lazyTab.split(',');
            const templateId = parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

            // Find the tab button that controls this pane
            const tabId = tabPane.id;
            const tabButton = document.querySelector(`[data-bs-target="#${tabId}"], [href="#${tabId}"]`);

            if (!tabButton) {
                return;
            }

            // Load when tab is shown
            tabButton.addEventListener('shown.bs.tab', () => {
                fieldsets.forEach((fieldsetName) => {
                    const container = tabPane.querySelector(`[data-fieldset-container="${fieldsetName.trim()}"]`);
                    if (container) {
                        loadFieldset(fieldsetName.trim(), container, templateId).catch(() => {
                            // Error already displayed in UI
                        });
                    }
                });
            });

            // If tab is already active, load immediately
            if (tabPane.classList.contains('active') || tabPane.classList.contains('show')) {
                fieldsets.forEach((fieldsetName) => {
                    const container = tabPane.querySelector(`[data-fieldset-container="${fieldsetName.trim()}"]`);
                    if (container) {
                        loadFieldset(fieldsetName.trim(), container, templateId).catch(() => {
                            // Error already displayed in UI
                        });
                    }
                });
            }
        });
    }

    /**
     * Load the Layout Editor content via AJAX
     * @param {HTMLElement} container - The container to populate
     * @param {string} url - The URL to fetch content from
     */
    function loadLayoutEditorContent(container, url) {
        // Already loaded?
        if (container.dataset.loaded === 'true') {
            return;
        }

        container.dataset.loaded = 'true';

        window.ProclaimFetch.fetch(
            url,
            { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } },
            { timeout: 30000, retries: 1 },
        )
            .then((response) => response.text())
            .then((html) => {
                container.innerHTML = html;

                // Execute any inline scripts in the loaded content
                // (script tags don't execute automatically when set via innerHTML)
                container.querySelectorAll('script').forEach((oldScript) => {
                    const newScript = document.createElement('script');
                    // Copy attributes
                    Array.from(oldScript.attributes).forEach((attr) => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    // Copy content
                    newScript.textContent = oldScript.textContent;
                    // Replace old script with new one to trigger execution
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // Notify Joomla that new dynamic content was loaded so the
                // Joomla 6 TinyMCE plugin (and other field initialisers) can
                // hook the freshly inserted DOM nodes.
                container.dispatchEvent(new CustomEvent('joomla:updated', { bubbles: true }));

                // Dispatch a custom event to signal that layout editor content is loaded
                container.dispatchEvent(new CustomEvent('layoutEditorLoaded', {
                    bubbles: true,
                }));

                // If layout editor script is already loaded, trigger initialization
                if (typeof window.LayoutEditor !== 'undefined') {
                    const editorContainer = container.querySelector('#layout-editor-container');
                    if (editorContainer && !editorContainer.dataset.initialized) {
                    // Remove the loading placeholder if present
                        const loadingEl = document.getElementById('layout-editor-loading');
                        if (loadingEl) {
                            loadingEl.remove();
                        }

                        editorContainer.dataset.initialized = 'true';
                        const initialContext = editorContainer.dataset.context || 'messages';
                        window.proclaimLayoutEditor = new window.LayoutEditor(editorContainer, {
                            context: initialContext,
                        });
                    }
                }
            })
            .catch((error) => {
                container.replaceChildren(Object.assign(document.createElement('div'), {
                    className: 'alert alert-danger',
                    textContent: 'Failed to load Layout Editor: ' + (error.message || 'Unknown error'),
                }));
            });
    }

    /**
     * Initialize lazy loading for the Layout Editor tab
     * Loads the layout editor content via AJAX when the tab is first shown
     */
    function initLayoutEditorLazyLoad() {
        const container = document.getElementById('layout-editor-ajax-container');
        if (!container) {
            return;
        }

        const { loadUrl } = container.dataset;
        if (!loadUrl) {
            return;
        }

        // Find the tab element containing the container
        // Joomla 5 uses <joomla-tab-element> web components, not Bootstrap .tab-pane
        const tabElement = container.closest('joomla-tab-element');

        if (!tabElement) {
            // Not in a tab, load immediately
            loadLayoutEditorContent(container, loadUrl);
            return;
        }

        // Check if tab is already active
        if (tabElement.hasAttribute('active')) {
            loadLayoutEditorContent(container, loadUrl);
            return;
        }

        // Find the parent joomla-tab and listen for tab changes
        const joomlaTab = tabElement.closest('joomla-tab');

        if (!joomlaTab) {
            // Can't find tab container, load immediately
            loadLayoutEditorContent(container, loadUrl);
            return;
        }

        // Load when tab is shown for the first time
        // joomla-tab fires 'joomla.tab.shown' event when a tab is activated
        const loadOnShow = function (event) {
            // Check if the shown tab is our tab
            if (event.target === tabElement || tabElement.hasAttribute('active')) {
                loadLayoutEditorContent(container, loadUrl);
                joomlaTab.removeEventListener('joomla.tab.shown', loadOnShow);
            }
        };
        joomlaTab.addEventListener('joomla.tab.shown', loadOnShow);
    }

    /**
     * Initialize generic lazy-loading for any tab container marked with
     * `.proclaim-lazy-tab-content` and a `data-load-url` attribute. The
     * container is populated with raw HTML (fetched via ProclaimFetch) the
     * first time its enclosing tab is shown.
     *
     * Used by the Teachers edit view (Messages tab) and can be reused for
     * any future tab that wants server-rendered lazy content.
     */
    function initGenericLazyTabContent() {
        /**
         * After the container is populated, drop any hidden form placeholders
         * that were rendered as a save-time fallback. The placeholder elements
         * carry `data-bio-placeholder` (or whatever marker the consumer set
         * via `data-removes-placeholders`) and share their `name` with the
         * real form field that just got injected.
         */
        const cleanupPlaceholders = function (container) {
            const marker = container.dataset.removesPlaceholders;
            if (!marker) {
                return;
            }
            const attr = `data-${marker}-placeholder`;
            document.querySelectorAll(`[${attr}]`).forEach((el) => el.remove());
        };

        const triggerLoad = function (container, loadUrl) {
            loadLayoutEditorContent(container, loadUrl);
            // loadLayoutEditorContent fetches asynchronously — wait for the
            // joomla:updated event it dispatches once the DOM is populated.
            container.addEventListener('joomla:updated', () => cleanupPlaceholders(container), { once: true });
        };

        document.querySelectorAll('.proclaim-lazy-tab-content[data-load-url]').forEach((container) => {
            if (container.dataset.loaded === 'true') {
                return;
            }

            const { loadUrl } = container.dataset;
            if (!loadUrl) {
                return;
            }

            const tabElement = container.closest('joomla-tab-element');

            // Not inside a Joomla tab — load straight away.
            if (!tabElement) {
                triggerLoad(container, loadUrl);
                return;
            }

            // Already the active tab on initial render — load immediately.
            if (tabElement.hasAttribute('active')) {
                triggerLoad(container, loadUrl);
                return;
            }

            const joomlaTab = tabElement.closest('joomla-tab');
            if (!joomlaTab) {
                triggerLoad(container, loadUrl);
                return;
            }

            const loadOnShow = function (event) {
                if (event.target === tabElement || tabElement.hasAttribute('active')) {
                    triggerLoad(container, loadUrl);
                    joomlaTab.removeEventListener('joomla.tab.shown', loadOnShow);
                }
            };
            joomlaTab.addEventListener('joomla.tab.shown', loadOnShow);
        });
    }

    // Export functions for use by other modules (e.g., layout editor)
    window.ProclaimLazyLoad = {
        loadFieldset,
        isFieldsetLoaded,
        isFieldsetLoading,
        loadLayoutEditorContent,
        initGenericLazyTabContent,
    };

    // Initialize lazy loading on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAccordionLazyLoad();
            initTabLazyLoad();
            initLayoutEditorLazyLoad();
            initGenericLazyTabContent();
        });
    } else {
        initAccordionLazyLoad();
        initTabLazyLoad();
        initLayoutEditorLazyLoad();
        initGenericLazyTabContent();
    }
}());
