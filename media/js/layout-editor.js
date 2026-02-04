(function () {
    'use strict';

    /**
     * Layout Editor for Proclaim Templates
     *
     * @package    Proclaim.Admin
     * @copyright  (C) 2026 CWM Team All rights reserved
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     */

    /* jshint esversion: 11, browser: true */
    /* globals Sortable, bootstrap */

    (function () {

        // =====================================================================
            // Configuration Loading Functions
            // All options are loaded from PHP via Joomla.getOptions() for automatic
            // translation support. Fallback to English if PHP options not available.
            // =====================================================================

            /**
             * Get element definitions from PHP or use fallback
             * @returns {Object} Element definitions by context
             */
            const getElementDefinitions = () => {
                if (window.Joomla && typeof Joomla.getOptions === 'function') {
                    const phpOptions = Joomla.getOptions('com_proclaim.elementDefinitions');
                    if (phpOptions && typeof phpOptions === 'object') {
                        return phpOptions;
                    }
                }
                // Fallback (English only)
                return {
                    messages: {
                        label: 'Messages List', prefix: '',
                        elements: [
                            { id: 'scripture1', label: 'Scripture 1' }, { id: 'scripture2', label: 'Scripture 2' },
                            { id: 'title', label: 'Title' }, { id: 'date', label: 'Date' },
                            { id: 'teacher', label: 'Teacher' }, { id: 'teacherimage', label: 'Teacher Image' },
                            { id: 'teacher-title', label: 'Teacher Title + Name' }, { id: 'duration', label: 'Duration' },
                            { id: 'studyintro', label: 'Study Intro' }, { id: 'series', label: 'Series' },
                            { id: 'seriesthumbnail', label: 'Series Thumbnail' }, { id: 'seriesdescription', label: 'Series Description' },
                            { id: 'jbsmedia', label: 'Media' }, { id: 'topic', label: 'Topics' },
                            { id: 'locations', label: 'Locations' }, { id: 'hits', label: 'Hits' },
                            { id: 'downloads', label: 'Downloads' }, { id: 'studynumber', label: 'Study Number' },
                            { id: 'messagetype', label: 'Message Type' }, { id: 'thumbnail', label: 'Thumbnail' },
                            { id: 'custom', label: 'Custom' }
                        ]
                    },
                    details: {
                        label: 'Study Details', prefix: 'd',
                        elements: [
                            { id: 'scripture1', label: 'Scripture 1' }, { id: 'scripture2', label: 'Scripture 2' },
                            { id: 'title', label: 'Title' }, { id: 'date', label: 'Date' },
                            { id: 'teacher', label: 'Teacher' }, { id: 'teacherimage', label: 'Teacher Image' },
                            { id: 'teacher-title', label: 'Teacher Title + Name' }, { id: 'duration', label: 'Duration' },
                            { id: 'studyintro', label: 'Study Intro' }, { id: 'series', label: 'Series' },
                            { id: 'seriesthumbnail', label: 'Series Thumbnail' }, { id: 'seriesdescription', label: 'Series Description' },
                            { id: 'jbsmedia', label: 'Media' }, { id: 'topic', label: 'Topics' },
                            { id: 'locations', label: 'Locations' }, { id: 'hits', label: 'Hits' },
                            { id: 'downloads', label: 'Downloads' }, { id: 'studynumber', label: 'Study Number' },
                            { id: 'messagetype', label: 'Message Type' }, { id: 'thumbnail', label: 'Thumbnail' },
                            { id: 'custom', label: 'Custom' }
                        ]
                    },
                    teachers: {
                        label: 'Teachers List', prefix: 'ts',
                        elements: [
                            { id: 'teacher', label: 'Teacher Name' }, { id: 'teacherimage', label: 'Teacher Image' },
                            { id: 'teacher-title', label: 'Teacher Title + Name' }, { id: 'teacheremail', label: 'Email' },
                            { id: 'teacherweb', label: 'Website' }, { id: 'teacherphone', label: 'Phone' },
                            { id: 'teacherfb', label: 'Facebook' }, { id: 'teachertw', label: 'Twitter' },
                            { id: 'teacherblog', label: 'Blog' }, { id: 'teachershort', label: 'Short Bio' },
                            { id: 'teacherallinone', label: 'All in One' }, { id: 'custom', label: 'Custom' }
                        ]
                    },
                    teacherDetails: {
                        label: 'Teacher Details', prefix: 'td',
                        elements: [
                            { id: 'teacher', label: 'Teacher Name' }, { id: 'teacherimage', label: 'Teacher Image' },
                            { id: 'teacher-title', label: 'Teacher Title + Name' }, { id: 'teacheremail', label: 'Email' },
                            { id: 'teacherweb', label: 'Website' }, { id: 'teacherphone', label: 'Phone' },
                            { id: 'teacherfb', label: 'Facebook' }, { id: 'teachertw', label: 'Twitter' },
                            { id: 'teacherblog', label: 'Blog' }, { id: 'teachershort', label: 'Short Bio' },
                            { id: 'teacherlong', label: 'Full Bio' }, { id: 'teacherlargeimage', label: 'Large Image' },
                            { id: 'teacherallinone', label: 'All in One' }, { id: 'custom', label: 'Custom' }
                        ]
                    },
                    series: {
                        label: 'Series List', prefix: 's',
                        elements: [
                            { id: 'series', label: 'Series Title' }, { id: 'description', label: 'Description' },
                            { id: 'seriesthumbnail', label: 'Thumbnail' }, { id: 'teacher', label: 'Teacher' },
                            { id: 'dcustom', label: 'Custom' }
                        ]
                    },
                    seriesDetails: {
                        label: 'Series Details', prefix: 'sd',
                        elements: [
                            { id: 'series', label: 'Series Title' }, { id: 'description', label: 'Description' },
                            { id: 'seriesthumbnail', label: 'Thumbnail' }, { id: 'teacher', label: 'Teacher' },
                            { id: 'custom', label: 'Custom' }
                        ]
                    },
                    landingPage: {
                        label: 'Landing Page', prefix: '', isOrderOnly: true,
                        elements: [
                            { id: 'teachers', label: 'Teachers', showParam: 'showteachers', labelParam: 'teacherslabel' },
                            { id: 'series', label: 'Series', showParam: 'showseries', labelParam: 'serieslabel' },
                            { id: 'books', label: 'Books', showParam: 'showbooks', labelParam: 'bookslabel' },
                            { id: 'topics', label: 'Topics', showParam: 'showtopics', labelParam: 'topicslabel' },
                            { id: 'locations', label: 'Locations', showParam: 'showlocations', labelParam: 'locationslabel' },
                            { id: 'messagetypes', label: 'Message Types', showParam: 'showmessagetypes', labelParam: 'messagetypeslabel' },
                            { id: 'years', label: 'Years', showParam: 'showyears', labelParam: 'yearslabel' }
                        ]
                    }
                };
            };

            /**
             * Get link type options from PHP based on context
             * @param {string} context - Current context (messages, details, teachers, teacherDetails, series, seriesDetails)
             * @returns {Array} Link type options appropriate for the context
             */
            const getLinkTypeOptions = (context) => {
                if (window.Joomla && typeof Joomla.getOptions === 'function') {
                    // Teachers List and Teacher Details use limited options
                    if (context === 'teachers' || context === 'teacherDetails') {
                        const teacherOptions = Joomla.getOptions('com_proclaim.teacherLinkTypeOptions');
                        if (teacherOptions && Array.isArray(teacherOptions)) {
                            return teacherOptions;
                        }
                        // Fallback for teachers
                        return [
                            { value: '0', label: 'No Link' },
                            { value: '3', label: "Link to Teacher's Profile" }
                        ];
                    }

                    // Series List and Series Details use limited options
                    if (context === 'series' || context === 'seriesDetails') {
                        const seriesOptions = Joomla.getOptions('com_proclaim.seriesLinkTypeOptions');
                        if (seriesOptions && Array.isArray(seriesOptions)) {
                            return seriesOptions;
                        }
                        // Fallback for series
                        return [
                            { value: '0', label: 'No Link' },
                            { value: '1', label: 'Link to Details' }
                        ];
                    }

                    // Messages List and Study Details use full options
                    const phpOptions = Joomla.getOptions('com_proclaim.linkTypeOptions');
                    if (phpOptions && Array.isArray(phpOptions)) {
                        return phpOptions;
                    }
                }
                // Fallback (English only) - core options (VirtueMart/DOCman require PHP config)
                return [
                    { value: '0', label: 'No Link' }, { value: '1', label: 'Link to Details' },
                    { value: '4', label: 'Link to Details (Tooltip)' }, { value: '2', label: 'Link to Media' },
                    { value: '9', label: 'Link to Download' }, { value: '5', label: 'Link to Media (Tooltip)' },
                    { value: '3', label: "Link to Teacher's Profile" }, { value: '6', label: 'Link to First Article' }
                ];
            };

            /**
             * Get element type options (HTML wrapper) from PHP or use fallback
             * @returns {Array} Element type options
             */
            const getElementTypeOptions = () => {
                if (window.Joomla && typeof Joomla.getOptions === 'function') {
                    const phpOptions = Joomla.getOptions('com_proclaim.elementTypeOptions');
                    if (phpOptions && Array.isArray(phpOptions)) {
                        return phpOptions;
                    }
                }
                // Fallback (English only)
                return [
                    { value: '0', label: 'None' }, { value: '1', label: 'Paragraph' },
                    { value: '2', label: 'Header 1' }, { value: '3', label: 'Header 2' },
                    { value: '4', label: 'Header 3' }, { value: '5', label: 'Header 4' },
                    { value: '6', label: 'Header 5' }, { value: '7', label: 'Blockquote' }
                ];
            };

            /**
             * Get date format options from PHP or use fallback
             * @returns {Array} Date format options
             */
            const getDateFormatOptions = () => {
                if (window.Joomla && typeof Joomla.getOptions === 'function') {
                    const phpOptions = Joomla.getOptions('com_proclaim.dateFormatOptions');
                    if (phpOptions && Array.isArray(phpOptions)) {
                        return phpOptions;
                    }
                }
                // Fallback (English only)
                return [
                    { value: '', label: 'Use Global Setting' }, { value: '0', label: 'Sep 1, 2012' },
                    { value: '1', label: 'Sep 1' }, { value: '2', label: '9/1/2012' },
                    { value: '3', label: '9/1' }, { value: '4', label: 'Saturday, September 1, 2010' },
                    { value: '5', label: 'September 1, 2012' }, { value: '6', label: '1 September 2012' },
                    { value: '7', label: '1/9/2010' }, { value: '8', label: 'Use Joomla Global' },
                    { value: '9', label: '2012/09/01' }
                ];
            };

            // Cache the loaded configurations (loaded once on first use)
            let ELEMENT_DEFINITIONS = null;
            const getElementDefs = () => {
                if (!ELEMENT_DEFINITIONS) {
                    ELEMENT_DEFINITIONS = getElementDefinitions();
                }
                return ELEMENT_DEFINITIONS;
            };

            /**
             * Layout Editor Class
             */
            class LayoutEditor {
                /**
                 * Constructor
                 * @param {HTMLElement} container - The container element
                 * @param {Object} options - Configuration options
                 */
                constructor(container, options = {}) {
                    this.container = container;
                    this.options = {
                        maxRows: 10,
                        numCols: 12,
                        context: 'messages',
                        formId: 'item-form',
                        paramsPrefix: 'jform[params]',
                        ...options
                    };

                    // State: Map<elementId, {row, col, colspan, element, custom, linktype}>
                    this.state = new Map();
                    this.currentContext = this.options.context;
                    this.sortableInstances = [];
                    this.paletteSortable = null;

                    // Undo/Redo history
                    this.undoStack = [];
                    this.redoStack = [];
                    this.maxHistory = 50;

                    // UI state
                    this.showGrid = false;
                    this.isResizing = false;
                    this.viewSettingsLoaded = new Set();
                    this.isDirty = false;
                    this.beforeUnloadHandler = null;
                    this.originalModalValues = null; // Stores values when modal opens for change detection
                    this.viewSettingsHasChanges = false; // Tracks if View Settings modal has unsaved changes
                    this.sectionSettingsHasChanges = false; // Tracks if Section Settings modal has unsaved changes

                    // Language strings helper
                    this.trans = (key) => {
                        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
                            return Joomla.Text._(key);
                        }
                        return key;
                    };

                    // Bind resize event handlers
                    this.handleResize = this.handleResize.bind(this);
                    this.endResize = this.endResize.bind(this);

                    this.init();
                }

                /**
                 * Initialize the editor
                 */
                init() {
                    this.createStructure();
                    this.initSidebar();
                    this.initCanvas();
                    this.loadFromParams();
                    this.initSortable();
                    this.cleanupRows(); // Ensure empty drop zone exists
                    this.bindEvents();
                }

                /**
                 * Create the basic HTML structure
                 */
                createStructure() {
                    this.container.innerHTML = `
                <div class="layout-help alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span>
                    ${this.trans('JBS_TPL_LAYOUT_HELP') || 'Drag elements from the sidebar onto rows to arrange your layout. Click the gear icon to configure element settings.'}
                </div>
                <div class="layout-context-tabs"></div>
                <div class="layout-toolbar">
                    <div class="layout-toolbar-group">
                        <button type="button" class="btn btn-secondary btn-undo" title="Undo (Ctrl+Z)" disabled>
                            <span class="icon-undo" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-redo" title="Redo (Ctrl+Y)" disabled>
                            <span class="icon-redo" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="layout-toolbar-group">
                        <button type="button" class="btn btn-secondary btn-grid" title="Toggle Grid">
                            <span class="icon-grid-view" aria-hidden="true"></span>
                            <span class="btn-text">Grid</span>
                        </button>
                    </div>
                    <div class="layout-toolbar-group layout-toolbar-spacer"></div>
                    <div class="layout-toolbar-group">
                        <button type="button" class="btn btn-secondary btn-view-settings" title="${this.trans('JBS_TPL_VIEW_SETTINGS') || 'View Settings'}">
                            <span class="icon-cog" aria-hidden="true"></span>
                            <span class="btn-text">${this.trans('JBS_TPL_VIEW_SETTINGS') || 'View Settings'}</span>
                        </button>
                    </div>
                </div>
                <div class="layout-editor">
                    <aside class="layout-sidebar">
                        <h4>${this.trans('JBS_TPL_AVAILABLE_ELEMENTS') || 'Available Elements'}</h4>
                        <div class="element-palette"></div>
                    </aside>
                    <main class="layout-canvas"></main>
                </div>
            `;

                    this.sidebar = this.container.querySelector('.layout-sidebar');
                    this.palette = this.container.querySelector('.element-palette');
                    this.canvas = this.container.querySelector('.layout-canvas');
                    this.contextTabs = this.container.querySelector('.layout-context-tabs');
                    this.toolbar = this.container.querySelector('.layout-toolbar');
                    this.editor = this.container.querySelector('.layout-editor');

                    // Create context tabs
                    this.createContextTabs();

                    // Create element settings modal
                    this.createSettingsModal();

                    // Create view settings modal
                    this.createViewSettingsModal();

                    // Create section settings modal (for landing page section gear buttons)
                    this.createSectionSettingsModal();
                }

                /**
                 * Create context selector tabs
                 * Labels come from PHP-provided elementDefinitions for automatic translation
                 */
                createContextTabs() {
                    const defs = getElementDefs();
                    const contextOrder = ['messages', 'details', 'teachers', 'teacherDetails', 'series', 'seriesDetails', 'landingPage'];

                    contextOrder.forEach(contextId => {
                        const contextDef = defs[contextId];
                        if (!contextDef) { return; }

                        const tab = document.createElement('button');
                        tab.type = 'button';
                        const isActive = contextId === this.currentContext;
                        tab.className = 'btn layout-context-tab ' + (isActive ? 'btn-primary' : 'btn-secondary');
                        tab.dataset.context = contextId;
                        tab.textContent = contextDef.label;
                        tab.addEventListener('click', () => this.switchContext(contextId));
                        this.contextTabs.appendChild(tab);
                    });
                }

                /**
                 * Switch to a different context
                 * @param {string} context - The context to switch to
                 */
                switchContext(context) {
                    if (context === this.currentContext) { return; }

                    // Sync current state to form before switching
                    this.syncToForm();

                    // Update tab button states - swap primary/secondary classes
                    this.contextTabs.querySelectorAll('.layout-context-tab').forEach(tab => {
                        const isActive = tab.dataset.context === context;
                        tab.classList.toggle('btn-primary', isActive);
                        tab.classList.toggle('btn-secondary', !isActive);
                    });

                    this.currentContext = context;

                    // Clear state and reload
                    this.state.clear();
                    this.destroySortables();

                    // Check if this is an order-only context (like landing page)
                    const contextDef = getElementDefs()[context];
                    const isOrderOnly = contextDef?.isOrderOnly || false;

                    // Update toolbar visibility for order-only contexts
                    this.updateToolbarForContext(isOrderOnly);

                    // Reinitialize for new context
                    if (isOrderOnly) {
                        this.initLandingPageSidebar();
                        this.initLandingPageCanvas();
                        this.loadLandingPageFromParams();
                        this.initLandingPageSortable();
                    } else {
                        this.initSidebar();
                        this.initCanvas();
                        this.loadFromParams();
                        this.initSortable();
                        this.cleanupRows(); // Ensure empty drop zone exists
                    }

                }

                /**
                 * Update toolbar visibility based on context type
                 * @param {boolean} isOrderOnly - Whether this is an order-only context
                 */
                updateToolbarForContext(isOrderOnly) {
                    if (!this.toolbar) { return; }

                    // Hide grid button for order-only contexts (like Landing Page)
                    const gridBtn = this.toolbar.querySelector('.btn-grid');
                    if (gridBtn) { gridBtn.style.display = isOrderOnly ? 'none' : ''; }
                }

                // =====================================================================
                // View Settings Modal Methods
                // =====================================================================

                /**
                 * Create the view settings modal
                 */
                createViewSettingsModal() {
                    // Check if modal already exists
                    if (document.getElementById('viewSettingsModal')) {
                        this.viewSettingsModal = document.getElementById('viewSettingsModal');
                        return;
                    }

                    const modal = document.createElement('div');
                    modal.className = 'modal fade view-settings-modal';
                    modal.id = 'viewSettingsModal';
                    modal.tabIndex = -1;
                    modal.setAttribute('aria-labelledby', 'viewSettingsModalLabel');
                    modal.inert = true;

                    modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewSettingsModalLabel">
                                <span class="icon-cog" aria-hidden="true"></span>
                                ${this.trans('JBS_TPL_VIEW_SETTINGS') || 'View Settings'}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${this.trans('JCLOSE') || 'Close'}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="accordion" id="viewSettingsAccordion"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${this.trans('JCANCEL') || 'Cancel'}</button>
                            <button type="button" class="btn btn-success view-settings-apply" id="viewSettingsApply">
                                <span class="icon-check" aria-hidden="true"></span>
                                ${this.trans('JAPPLY') || 'Apply'}
                            </button>
                        </div>
                    </div>
                </div>
            `;

                    // Append modal to the main form so fields are submitted with form
                    // Joomla 5 uses id="item-form" name="adminForm", older versions use id="adminForm"
                    const mainForm = document.getElementById('item-form')
                        || document.getElementById('adminForm')
                        || document.querySelector('form[name="adminForm"]')
                        || document.body;
                    mainForm.appendChild(modal);
                    this.viewSettingsModal = modal;
                    this.viewSettingsAccordion = modal.querySelector('#viewSettingsAccordion');
                    this.bsViewSettingsModal = null;

                    // Add Apply button event handler
                    const applyBtn = modal.querySelector('#viewSettingsApply');
                    if (applyBtn) {
                        applyBtn.addEventListener('click', () => this.applyViewSettings());
                    }

                    // Track changes in the modal
                    modal.addEventListener('input', () => {
                        this.viewSettingsHasChanges = true;
                    });
                    modal.addEventListener('change', () => {
                        this.viewSettingsHasChanges = true;
                    });

                    // Intercept modal close to warn about unsaved changes
                    modal.addEventListener('hide.bs.modal', (e) => {
                        // Check if we have unsaved changes (changes without clicking Apply)
                        if (this.viewSettingsHasChanges) {
                            const message = this.trans('JBS_TPL_MODAL_UNSAVED_CHANGES') || 'You have unsaved changes in this dialog. Discard changes?';
                            if (!window.confirm(message)) {
                                e.preventDefault();
                                return;
                            }
                        }
                        // Reset flag when modal closes
                        this.viewSettingsHasChanges = false;
                    });
                }

                /**
                 * Apply view settings and close modal
                 * Syncs TinyMCE editors and closes the modal
                 */
                applyViewSettings() {
                    // Sync all TinyMCE editors to their textareas before closing
                    if (typeof window.tinymce !== 'undefined' && this.viewSettingsModal) {
                        this.viewSettingsModal.querySelectorAll('textarea.mce_editable').forEach(textarea => {
                            const editor = window.tinymce.get(textarea.id);
                            if (editor) {
                                editor.save();
                            }
                        });
                    }

                    // Mark as dirty since view settings were changed
                    this.markDirty();

                    // Clear the modal changes flag since we're applying changes
                    this.viewSettingsHasChanges = false;

                    // Close the modal
                    const modalInstance = this.getViewSettingsModalInstance();
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }

                /**
                 * Get or create Bootstrap modal instance for view settings
                 * @returns {Object|null}
                 */
                getViewSettingsModalInstance() {
                    if (this.bsViewSettingsModal) {
                        return this.bsViewSettingsModal;
                    }

                    if (!this.viewSettingsModal) {
                        return null;
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        this.bsViewSettingsModal = new bootstrap.Modal(this.viewSettingsModal);
                        return this.bsViewSettingsModal;
                    }

                    return null;
                }

                /**
                 * Open the view settings modal
                 */
                openViewSettings() {
                    // Reset change tracking flag
                    this.viewSettingsHasChanges = false;

                    // Initialize accordion content for current context
                    this.initViewSettingsAccordion();

                    // Show modal
                    const modalInstance = this.getViewSettingsModalInstance();
                    if (modalInstance) {
                        if (this.viewSettingsModal) {
                            this.viewSettingsModal.inert = false;
                        }
                        modalInstance.show();
                    }
                }

                /**
                 * Initialize the view settings accordion for current context
                 */
                initViewSettingsAccordion() {
                    if (!this.viewSettingsAccordion) { return; }

                    // Get settings configuration from Joomla script options
                    const settingsConfig = (window.Joomla?.getOptions?.('com_proclaim.settingsConfig')) || {};
                    const contextSettings = settingsConfig[this.currentContext] || [];

                    // Update modal title to show context
                    const contextDef = getElementDefs()[this.currentContext];
                    const modalTitle = this.viewSettingsModal?.querySelector('.modal-title');
                    if (modalTitle && contextDef) {
                        modalTitle.innerHTML = `
                    <span class="icon-cog" aria-hidden="true"></span>
                    ${this.trans('JBS_TPL_VIEW_SETTINGS') || 'View Settings'}: ${contextDef.label}
                `;
                    }

                    // Clear existing accordion items and reset loaded tracking
                    this.viewSettingsAccordion.innerHTML = '';
                    this.viewSettingsLoaded.clear();

                    if (contextSettings.length === 0) {
                        this.viewSettingsAccordion.innerHTML = `
                    <div class="alert alert-info mb-0">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        ${this.trans('JBS_TPL_NO_VIEW_SETTINGS') || 'No additional settings available for this view.'}
                    </div>
                `;
                        return;
                    }

                    // Create accordion items for each fieldset
                    contextSettings.forEach((setting, index) => {
                        const itemId = `view-settings-${this.currentContext}-${index}`;
                        const collapseId = `collapse-${itemId}`;
                        const isFirst = index === 0;

                        const accordionItem = document.createElement('div');
                        accordionItem.className = 'accordion-item';
                        accordionItem.dataset.viewSettingsFieldset = setting.fieldset;

                        accordionItem.innerHTML = `
                    <h2 class="accordion-header" id="heading-${itemId}">
                        <button class="accordion-button${isFirst ? '' : ' collapsed'}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                                aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="${collapseId}">
                            ${setting.label}
                        </button>
                    </h2>
                    <div id="${collapseId}" class="accordion-collapse collapse${isFirst ? ' show' : ''}"
                         aria-labelledby="heading-${itemId}" data-bs-parent="#viewSettingsAccordion">
                        <div class="accordion-body">
                            <div class="text-center p-4">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                ${this.trans('JBS_TPL_PLEASE_WAIT') || 'Please wait...'}
                            </div>
                        </div>
                    </div>
                `;

                        this.viewSettingsAccordion.appendChild(accordionItem);

                        // Add event listener for lazy loading
                        const collapseEl = accordionItem.querySelector('.accordion-collapse');
                        if (collapseEl) {
                            collapseEl.addEventListener('show.bs.collapse', () => {
                                this.loadViewSettingsFieldset(setting.fieldset, accordionItem.querySelector('.accordion-body'));
                            });
                        }

                        // Load first fieldset immediately
                        if (isFirst) {
                            this.loadViewSettingsFieldset(setting.fieldset, accordionItem.querySelector('.accordion-body'));
                        }
                    });
                }

                /**
                 * Load a fieldset into the view settings modal
                 * @param {string} fieldsetName - The fieldset name to load
                 * @param {HTMLElement} container - The container to load into
                 */
                loadViewSettingsFieldset(fieldsetName, container) {
                    // Create a unique key for this context + fieldset combination
                    const loadKey = `${this.currentContext}-${fieldsetName}`;

                    // Check if already loaded
                    if (this.viewSettingsLoaded.has(loadKey)) {
                        return;
                    }

                    // Get template ID from Joomla options or form
                    const templateId = (window.Joomla?.getOptions?.('com_proclaim.templateId')) ||
                        parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

                    // Use the global ProclaimLazyLoad if available
                    if (window.ProclaimLazyLoad && typeof window.ProclaimLazyLoad.loadFieldset === 'function') {
                        window.ProclaimLazyLoad.loadFieldset(fieldsetName, container, templateId)
                            .then(() => {
                                this.viewSettingsLoaded.add(loadKey);
                                // Initialize TinyMCE editors in the loaded content
                                this.fixHiddenEditors(container);
                            })
                            .catch(error => {
                                console.error('Failed to load view settings fieldset:', error);
                            });
                    } else {
                        // Fallback: load directly via fetch
                        this.loadFieldsetDirect(fieldsetName, container, templateId);
                    }
                }

                /**
                 * Direct fieldset loading (fallback if ProclaimLazyLoad is not available)
                 * @param {string} fieldsetName - The fieldset name to load
                 * @param {HTMLElement} container - The container to load into
                 * @param {number} templateId - The template ID
                 */
                loadFieldsetDirect(fieldsetName, container, templateId) {
                    container.innerHTML = `
                <div class="text-center p-4">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    ${this.trans('JBS_TPL_LOADING') || 'Loading...'}
                </div>
            `;

                    const csrfToken = (window.Joomla?.getOptions?.('csrf.token')) || '';
                    const url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json' +
                        '&fieldset=' + encodeURIComponent(fieldsetName) +
                        '&id=' + templateId +
                        '&' + csrfToken + '=1';

                    fetch(url, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const loadKey = `${this.currentContext}-${fieldsetName}`;
                            this.viewSettingsLoaded.add(loadKey);
                            container.innerHTML = data.html;

                            // Initialize Joomla form elements
                            if (window.Joomla?.initCustomSelect) {
                                window.Joomla.initCustomSelect(container);
                            }

                            // Fix TinyMCE editors after they initialize (needs delay)
                            this.fixHiddenEditors(container);
                        } else {
                            container.innerHTML = `<div class="alert alert-danger">${data.error || 'Failed to load content'}</div>`;
                        }
                    })
                    .catch(error => {
                        container.innerHTML = `<div class="alert alert-danger">Error loading content: ${error.message}</div>`;
                        console.error('Fieldset load error:', error);
                    });
                }

                /**
                 * Initialize TinyMCE editors in dynamically loaded content
                 * Joomla's TinyMCE only initializes on page load, so AJAX content needs manual init
                 * @param {HTMLElement} container - Container with editor textareas
                 */
                fixHiddenEditors(container) {
                    // Check if there are any editor textareas
                    const textareas = container.querySelectorAll('textarea.mce_editable');
                    if (textareas.length === 0) {
                        return;
                    }

                    // Initialize TinyMCE for each textarea
                    this.initTinyMCEEditors(container);
                }

                /**
                 * Initialize TinyMCE for textareas in a container
                 * @param {HTMLElement} container - Container with editor textareas
                 */
                initTinyMCEEditors(container) {
                    if (typeof window.tinymce === 'undefined') {
                        return;
                    }

                    const textareas = container.querySelectorAll('textarea.mce_editable');

                    textareas.forEach(textarea => {
                        const editorId = textarea.id;
                        if (!editorId) { return; }

                        // Check if already initialized
                        const existingEditor = window.tinymce.get(editorId);
                        if (existingEditor) {
                            // If the editor's container is detached or different, remove it
                            const editorContainer = existingEditor.getContainer();
                            if (!editorContainer || !document.body.contains(editorContainer)) {
                                existingEditor.remove();
                            } else {
                                // Editor is valid, skip
                                return;
                            }
                        }

                        // Simple config that works
                        const config = {
                            target: textarea,
                            menubar: true,
                            toolbar: 'undo redo | bold italic underline | bullist numlist | link',
                            plugins: 'link lists',
                            branding: false,
                            promotion: false,
                            height: 300,
                            setup: (editor) => {
                                editor.on('change', () => {
                                    editor.save();
                                });
                            }
                        };

                        // Initialize TinyMCE
                        window.tinymce.init(config).then(editors => {
                            if (editors && editors[0]) {
                                const editor = editors[0];

                                // Enable the toggle button
                                const wrapper = textarea.closest('.js-editor-tinymce');
                                const toggleBtn = wrapper?.querySelector('.js-tiny-toggler-button');
                                if (toggleBtn) {
                                    toggleBtn.disabled = false;
                                    // Add click handler for toggle
                                    toggleBtn.addEventListener('click', () => {
                                        if (editor.isHidden()) {
                                            editor.show();
                                        } else {
                                            editor.hide();
                                        }
                                    });
                                }

                                // Register with Joomla's editor system
                                if (window.Joomla?.editors?.instances) {
                                    window.Joomla.editors.instances[editorId] = {
                                        id: editorId,
                                        getValue: () => editor.getContent(),
                                        setValue: (val) => editor.setContent(val),
                                        getSelection: () => editor.selection.getContent(),
                                        replaceSelection: (val) => editor.execCommand('mceInsertContent', false, val),
                                        disable: (state) => editor.mode.set(state ? 'readonly' : 'design')
                                    };
                                }
                            }
                        }).catch(err => {
                            console.error('[Layout Editor] TinyMCE initialization failed for', editorId, err);
                        });
                    });
                }

                // =====================================================================
                // Landing Page Context Methods (Order-Only Mode)
                // =====================================================================

                /**
                 * Initialize the sidebar for landing page context (hidden)
                 */
                initLandingPageSidebar() {
                    // Hide the sidebar for landing page - all sections are shown in canvas
                    if (this.sidebar) {
                        this.sidebar.style.display = 'none';
                    }
                }

                /**
                 * Initialize the canvas for landing page context
                 * Shows a single-column sortable list of sections with toggle switches
                 */
                initLandingPageCanvas() {
                    if (!this.canvas) { return; }

                    // Show sidebar (in case it was hidden)
                    if (this.sidebar) {
                        this.sidebar.style.display = 'none';
                    }

                    this.canvas.innerHTML = '';
                    this.canvas.classList.add('landing-page-canvas');

                    // Create header with instructions
                    const header = document.createElement('div');
                    header.className = 'landing-page-header alert alert-info';
                    header.innerHTML = `
                <span class="icon-info-circle" aria-hidden="true"></span>
                ${this.trans('JBS_TPL_DRAG_TO_REORDER') || 'Drag sections to reorder. Toggle visibility with the switch.'}
            `;
                    this.canvas.appendChild(header);

                    // Create sortable section list
                    const sectionList = document.createElement('div');
                    sectionList.className = 'landing-section-list';
                    sectionList.id = 'landing-section-list';

                    const contextDef = ELEMENT_DEFINITIONS.landingPage;
                    if (contextDef && contextDef.elements) {
                        // Initially add all sections - will be reordered by loadLandingPageFromParams
                        contextDef.elements.forEach(element => {
                            const card = this.createLandingSectionCard(element);
                            sectionList.appendChild(card);
                        });
                    }

                    this.canvas.appendChild(sectionList);
                    this.landingSectionList = sectionList;
                }

                /**
                 * Create a landing page section card
                 * @param {Object} element - Section element definition
                 * @param {boolean} enabled - Whether section is enabled
                 * @returns {HTMLElement}
                 */
                createLandingSectionCard(element, enabled = true) {
                    const card = document.createElement('div');
                    card.className = 'landing-section-card' + (enabled ? '' : ' disabled');
                    card.dataset.section = element.id;
                    card.tabIndex = 0;

                    const label = element.label;
                    const toggleId = `landing-toggle-${element.id}`;

                    card.innerHTML = `
                <span class="section-handle">
                    <span class="icon-menu" aria-hidden="true"></span>
                </span>
                <span class="section-name">${label}</span>
                <div class="section-toggle">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="${toggleId}"
                               ${enabled ? 'checked' : ''} role="switch"
                               aria-label="${this.trans('JBS_TPL_TOGGLE_SECTION') || 'Toggle section visibility'}">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary btn-section-settings"
                        title="${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings'}">
                    <span class="icon-options" aria-hidden="true"></span>
                </button>
            `;

                    // Add toggle event listener
                    const toggle = card.querySelector('.form-check-input');
                    if (toggle) {
                        toggle.addEventListener('change', () => {
                            const isEnabled = toggle.checked;
                            card.classList.toggle('disabled', !isEnabled);
                            this.updateLandingSectionState(element.id, isEnabled);
                        });
                    }

                    // Add settings button event listener
                    const settingsBtn = card.querySelector('.btn-section-settings');
                    if (settingsBtn) {
                        settingsBtn.addEventListener('click', () => {
                            this.openLandingSectionSettings(element.id);
                        });
                    }

                    return card;
                }

                /**
                 * Update landing section state in internal state map and sync to form field
                 * @param {string} sectionId - Section ID
                 * @param {boolean} enabled - Whether enabled
                 */
                updateLandingSectionState(sectionId, enabled) {
                    let data = this.state.get(sectionId);
                    if (!data) {
                        data = { enabled: enabled, order: this.state.size + 1 };
                        this.state.set(sectionId, data);
                    } else {
                        data.enabled = enabled;
                    }

                    // Immediately sync to form field (e.g., showteachers, showseries)
                    const contextDef = ELEMENT_DEFINITIONS.landingPage;
                    const element = contextDef?.elements?.find(el => el.id === sectionId);
                    if (element?.showParam) {
                        this.syncShowParamToForm(element.showParam, enabled);
                    }

                    // Update settings button state on the card
                    this.updateSectionSettingsButton(sectionId, enabled);
                }

                /**
                 * Sync a show* param to its hidden form field
                 * @param {string} showParam - Parameter name (e.g., 'showteachers')
                 * @param {boolean} enabled - Whether enabled
                 */
                syncShowParamToForm(showParam, enabled) {
                    const fieldName = `${this.options.paramsPrefix}[${showParam}]`;
                    const field = document.querySelector(`[name="${fieldName}"]`);

                    if (field) {
                        field.value = enabled ? '1' : '0';
                    }
                }

                /**
                 * Read a show* param value from its hidden form field
                 * @param {string} showParam - Parameter name (e.g., 'showteachers')
                 * @returns {boolean} Whether the section is enabled
                 */
                readShowParamFromForm(showParam) {
                    const fieldName = `${this.options.paramsPrefix}[${showParam}]`;
                    const field = document.querySelector(`[name="${fieldName}"]`);

                    if (field) {
                        return field.value === '1';
                    }

                    return true; // Default to enabled if field not found
                }

                /**
                 * Update the settings button state based on section enabled state
                 * @param {string} sectionId - Section ID
                 * @param {boolean} enabled - Whether enabled
                 */
                updateSectionSettingsButton(sectionId, enabled) {
                    if (!this.landingSectionList) { return; }

                    const card = this.landingSectionList.querySelector(
                        `.landing-section-card[data-section="${sectionId}"]`
                    );
                    if (!card) { return; }

                    const settingsBtn = card.querySelector('.btn-section-settings');
                    if (settingsBtn) {
                        settingsBtn.disabled = !enabled;
                        settingsBtn.classList.toggle('disabled', !enabled);
                        settingsBtn.title = enabled ?
                            (this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings') :
                            (this.trans('JBS_TPL_SECTION_DISABLED') || 'Enable section to edit settings');
                    }
                }

                /**
                 * Create the section settings modal for landing page sections
                 */
                createSectionSettingsModal() {
                    // Check if modal already exists
                    if (document.getElementById('sectionSettingsModal')) {
                        this.sectionSettingsModal = document.getElementById('sectionSettingsModal');
                        return;
                    }

                    const modal = document.createElement('div');
                    modal.className = 'modal fade section-settings-modal';
                    modal.id = 'sectionSettingsModal';
                    modal.tabIndex = -1;
                    modal.setAttribute('aria-labelledby', 'sectionSettingsModalLabel');
                    modal.inert = true;

                    modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sectionSettingsModalLabel">
                                <span class="icon-cog" aria-hidden="true"></span>
                                ${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Section Settings'}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${this.trans('JCLOSE') || 'Close'}"></button>
                        </div>
                        <div class="modal-body">
                            <div id="sectionSettingsContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${this.trans('JCANCEL') || 'Cancel'}</button>
                            <button type="button" class="btn btn-success section-settings-apply" id="sectionSettingsApply">
                                <span class="icon-check" aria-hidden="true"></span>
                                ${this.trans('JAPPLY') || 'Apply'}
                            </button>
                        </div>
                    </div>
                </div>
            `;

                    // Append modal to the main form so fields are submitted with form
                    // Joomla 5 uses id="item-form" name="adminForm", older versions use id="adminForm"
                    const mainForm = document.getElementById('item-form')
                        || document.getElementById('adminForm')
                        || document.querySelector('form[name="adminForm"]')
                        || document.body;
                    mainForm.appendChild(modal);
                    this.sectionSettingsModal = modal;
                    this.sectionSettingsContent = modal.querySelector('#sectionSettingsContent');
                    this.bsSectionSettingsModal = null;

                    // Add Apply button event handler
                    const applyBtn = modal.querySelector('#sectionSettingsApply');
                    if (applyBtn) {
                        applyBtn.addEventListener('click', () => this.applySectionSettings());
                    }

                    // Track changes in the modal
                    modal.addEventListener('input', () => {
                        this.sectionSettingsHasChanges = true;
                    });
                    modal.addEventListener('change', () => {
                        this.sectionSettingsHasChanges = true;
                    });

                    // Intercept modal close to warn about unsaved changes
                    modal.addEventListener('hide.bs.modal', (e) => {
                        if (this.sectionSettingsHasChanges) {
                            const message = this.trans('JBS_TPL_MODAL_UNSAVED_CHANGES') || 'You have unsaved changes in this dialog. Discard changes?';
                            if (!window.confirm(message)) {
                                e.preventDefault();
                                return;
                            }
                        }
                        this.sectionSettingsHasChanges = false;
                    });
                }

                /**
                 * Apply section settings and close modal
                 * Syncs TinyMCE editors and closes the modal
                 */
                applySectionSettings() {
                    // Sync all TinyMCE editors to their textareas before closing
                    if (typeof window.tinymce !== 'undefined' && this.sectionSettingsModal) {
                        this.sectionSettingsModal.querySelectorAll('textarea.mce_editable').forEach(textarea => {
                            const editor = window.tinymce.get(textarea.id);
                            if (editor) {
                                editor.save();
                            }
                        });
                    }

                    // Mark as dirty since section settings were changed
                    this.markDirty();

                    // Clear the modal changes flag since we're applying changes
                    this.sectionSettingsHasChanges = false;

                    // Close the modal
                    const modalInstance = this.getSectionSettingsModalInstance();
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }

                /**
                 * Get or create Bootstrap modal instance for section settings
                 * @returns {Object|null}
                 */
                getSectionSettingsModalInstance() {
                    if (this.bsSectionSettingsModal) {
                        return this.bsSectionSettingsModal;
                    }

                    if (!this.sectionSettingsModal) {
                        return null;
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        this.bsSectionSettingsModal = new bootstrap.Modal(this.sectionSettingsModal);
                        return this.bsSectionSettingsModal;
                    }

                    return null;
                }

                /**
                 * Open settings modal for a landing page section
                 * @param {string} sectionId - Section ID (teachers, series, books, etc.)
                 */
                openLandingSectionSettings(sectionId) {
                    // Reset section settings change tracking
                    this.sectionSettingsHasChanges = false;

                    // Get section-specific fieldset from config
                    const sectionSettings = (window.Joomla?.getOptions?.('com_proclaim.landingSectionSettings')) || {};
                    const sectionConfig = sectionSettings[sectionId];

                    // Handle both object format {fieldset, label} and legacy string format
                    const fieldsetName = typeof sectionConfig === 'object' ? sectionConfig.fieldset : sectionConfig;
                    const settingsLabel = typeof sectionConfig === 'object' ? sectionConfig.label : 'Settings';

                    if (!fieldsetName) {
                        console.warn('No fieldset configured for section:', sectionId);
                        return;
                    }

                    // Create modal if needed
                    if (!this.sectionSettingsModal) {
                        this.createSectionSettingsModal();
                    }

                    // Update modal title with section name
                    const elementDef = getElementDefs().landingPage?.elements?.find(e => e.id === sectionId);
                    const sectionLabel = elementDef ? elementDef.label : sectionId;
                    const modalTitle = this.sectionSettingsModal?.querySelector('.modal-title');
                    if (modalTitle) {
                        modalTitle.innerHTML = `
                    <span class="icon-cog" aria-hidden="true"></span>
                    ${sectionLabel} ${settingsLabel}
                `;
                    }

                    // Load fieldset content
                    const container = this.sectionSettingsContent;
                    if (container) {
                        const templateId = parseInt(document.querySelector('input[name="jform[id]"]')?.value || '0', 10);

                        // Use ProclaimLazyLoad if available
                        if (window.ProclaimLazyLoad?.loadFieldset) {
                            window.ProclaimLazyLoad.loadFieldset(fieldsetName, container, templateId)
                                .catch(err => {
                                    console.error('Error loading section settings:', err);
                                });
                        } else {
                            // Fallback: load directly via fetch
                            this.loadSectionFieldset(fieldsetName, container, templateId);
                        }
                    }

                    // Show modal
                    const modalInstance = this.getSectionSettingsModalInstance();
                    if (modalInstance) {
                        if (this.sectionSettingsModal) {
                            this.sectionSettingsModal.inert = false;
                        }
                        modalInstance.show();
                    }
                }

                /**
                 * Load section fieldset via AJAX (fallback if ProclaimLazyLoad not available)
                 * @param {string} fieldsetName - Fieldset name to load
                 * @param {HTMLElement} container - Container to populate
                 * @param {number} templateId - Template ID
                 */
                loadSectionFieldset(fieldsetName, container, templateId) {
                    container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

                    const url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json' +
                        '&fieldset=' + encodeURIComponent(fieldsetName) +
                        '&id=' + templateId +
                        '&' + (window.Joomla?.getOptions?.('csrf.token') || '') + '=1';

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            container.innerHTML = data.html;
                            // Initialize any Joomla form elements
                            if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
                                Joomla.initCustomSelect(container);
                            }
                        } else {
                            container.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Failed to load settings') + '</div>';
                        }
                    })
                    .catch(error => {
                        container.innerHTML = '<div class="alert alert-danger">Error loading settings: ' + error.message + '</div>';
                        console.error('Section fieldset load error:', error);
                    });
                }

                /**
                 * Initialize sortable for landing page sections
                 */
                initLandingPageSortable() {
                    if (typeof Sortable === 'undefined' || !this.landingSectionList) {
                        return;
                    }

                    const self = this;

                    this.landingPageSortable = Sortable.create(this.landingSectionList, {
                        animation: 150,
                        handle: '.section-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onStart: function () {
                            self.saveStateForUndo();
                        },
                        onEnd: function () {
                            self.updateLandingPageOrder();
                        }
                    });
                }

                /**
                 * Update landing page section order after drag
                 */
                updateLandingPageOrder() {
                    if (!this.landingSectionList) { return; }

                    const cards = this.landingSectionList.querySelectorAll('.landing-section-card');
                    cards.forEach((card, index) => {
                        const sectionId = card.dataset.section;
                        let data = this.state.get(sectionId);
                        if (!data) {
                            const toggle = card.querySelector('.form-check-input');
                            data = { enabled: toggle?.checked ?? true, order: index + 1 };
                            this.state.set(sectionId, data);
                        } else {
                            data.order = index + 1;
                        }
                    });
                }

                /**
                 * Load landing page state from params
                 */
                loadLandingPageFromParams() {
                    const contextDef = ELEMENT_DEFINITIONS.landingPage;
                    if (!contextDef) { return; }

                    // Get params from Joomla script options
                    const templateParams = (window.Joomla?.getOptions?.('com_proclaim.templateParams')) || {};

                    // Try to load from new landing_layout JSON field first
                    const landingLayoutJson = templateParams.landing_layout;
                    let landingLayout = null;

                    if (landingLayoutJson) {
                        try {
                            landingLayout = (typeof landingLayoutJson === 'string') ?
                                JSON.parse(landingLayoutJson) :
                                landingLayoutJson;
                        } catch (e) {
                            console.warn('Failed to parse landing_layout JSON:', e);
                        }
                    }

                    if (landingLayout && Array.isArray(landingLayout) && landingLayout.length > 0) {
                        // Use new format
                        this.loadLandingPageFromNewFormat(landingLayout);
                    } else {
                        // Fall back to legacy headingorder_* fields
                        this.loadLandingPageFromLegacyFormat(templateParams);
                    }

                    // Reorder cards in DOM to match state order
                    this.reorderLandingPageCards();
                }

                /**
                 * Load landing page from new JSON format
                 * @param {Array} landingLayout - Array of {id, enabled} objects
                 */
                loadLandingPageFromNewFormat(landingLayout) {
                    const contextDef = ELEMENT_DEFINITIONS.landingPage;

                    landingLayout.forEach((item, index) => {
                        const element = contextDef.elements.find(el => el.id === item.id);
                        if (element) {
                            this.state.set(item.id, {
                                enabled: item.enabled !== false,
                                order: index + 1
                            });
                        }
                    });

                    // Add any missing sections (in case new sections were added)
                    contextDef.elements.forEach((element, index) => {
                        if (!this.state.has(element.id)) {
                            this.state.set(element.id, {
                                enabled: false,
                                order: landingLayout.length + index + 1
                            });
                        }
                    });
                }

                /**
                 * Load landing page from legacy headingorder_* format
                 * @param {Object} params - Template params
                 */
                loadLandingPageFromLegacyFormat(params) {
                    const contextDef = ELEMENT_DEFINITIONS.landingPage;
                    const sectionOrder = [];
                    const usedSections = new Set();

                    // Read headingorder_1 through headingorder_7
                    for (let i = 1; i <= 7; i++) {
                        const sectionId = params['headingorder_' + i];
                        if (sectionId && !usedSections.has(sectionId)) {
                            // Check if section is enabled - prefer form field over params
                            const element = contextDef.elements.find(el => el.id === sectionId);
                            const showParam = element?.showParam || ('show' + sectionId);

                            // Try to read from form field first (more accurate), fall back to params
                            let enabled = this.readShowParamFromForm(showParam);
                            if (enabled === undefined) {
                                enabled = parseInt(params[showParam], 10) === 1;
                            }

                            sectionOrder.push({ id: sectionId, enabled: enabled });
                            usedSections.add(sectionId);
                        }
                    }

                    // Set state for sections in order
                    sectionOrder.forEach((item, index) => {
                        this.state.set(item.id, {
                            enabled: item.enabled,
                            order: index + 1
                        });
                    });

                    // Add any missing sections at the end (disabled)
                    contextDef.elements.forEach((element, index) => {
                        if (!this.state.has(element.id)) {
                            this.state.set(element.id, {
                                enabled: false,
                                order: sectionOrder.length + index + 1
                            });
                        }
                    });
                }

                /**
                 * Reorder landing page cards in DOM based on state
                 */
                reorderLandingPageCards() {
                    if (!this.landingSectionList) { return; }

                    const cards = Array.from(this.landingSectionList.querySelectorAll('.landing-section-card'));

                    // Sort cards by order in state
                    cards.sort((a, b) => {
                        const dataA = this.state.get(a.dataset.section);
                        const dataB = this.state.get(b.dataset.section);
                        return (dataA?.order || 999) - (dataB?.order || 999);
                    });

                    // Re-append in sorted order and update toggle states
                    cards.forEach(card => {
                        const sectionId = card.dataset.section;
                        const data = this.state.get(sectionId);
                        const enabled = data?.enabled ?? true;

                        // Update toggle checkbox
                        const toggle = card.querySelector('.form-check-input');
                        if (toggle) {
                            toggle.checked = enabled;
                        }

                        // Update card class
                        card.classList.toggle('disabled', !enabled);

                        // Update settings button state
                        const settingsBtn = card.querySelector('.btn-section-settings');
                        if (settingsBtn) {
                            settingsBtn.disabled = !enabled;
                            settingsBtn.classList.toggle('disabled', !enabled);
                        }

                        // Re-append to reorder
                        this.landingSectionList.appendChild(card);
                    });
                }

                /**
                 * Sync landing page state to form
                 */
                syncLandingPageToForm() {
                    const form = document.getElementById(this.options.formId);
                    if (!form) { return; }

                    const contextDef = ELEMENT_DEFINITIONS.landingPage;
                    if (!contextDef) { return; }

                    // Build landing_layout JSON array
                    const landingLayout = [];
                    const orderedSections = [];

                    this.state.forEach((data, sectionId) => {
                        orderedSections.push({ id: sectionId, ...data });
                    });

                    // Sort by order
                    orderedSections.sort((a, b) => (a.order || 999) - (b.order || 999));

                    orderedSections.forEach(section => {
                        landingLayout.push({
                            id: section.id,
                            enabled: section.enabled
                        });
                    });

                    // Write new format to hidden field
                    const landingLayoutField = this.getOrCreateHiddenField(
                        `${this.options.paramsPrefix}[landing_layout]`,
                        form
                    );
                    if (landingLayoutField) {
                        landingLayoutField.value = JSON.stringify(landingLayout);
                    }

                    // Also write legacy format for backward compatibility
                    orderedSections.forEach((section, index) => {
                        const headingOrderField = this.getOrCreateHiddenField(
                            `${this.options.paramsPrefix}[headingorder_${index + 1}]`,
                            form
                        );
                        if (headingOrderField) {
                            headingOrderField.value = section.id;
                        }
                    });
                    // Note: show* params are synced immediately via updateLandingSectionState
                    // when toggles change, so no need to sync them here on form submit
                }

                /**
                 * Helper to get or create a hidden form field
                 * @param {string} name - Field name
                 * @param {HTMLFormElement} form - Form element
                 * @returns {HTMLInputElement|null}
                 */
                getOrCreateHiddenField(name, form) {
                    let field = document.querySelector(`[name="${name}"]`);
                    if (!field && form) {
                        field = document.createElement('input');
                        field.type = 'hidden';
                        field.name = name;
                        form.appendChild(field);
                    }
                    return field;
                }

                /**
                 * Initialize the sidebar with available elements
                 */
                initSidebar() {
                    const contextDef = getElementDefs()[this.currentContext];
                    if (!contextDef || !this.palette) {
                        return;
                    }

                    this.palette.innerHTML = '';

                    // Group elements into categories (simplified - all in one group for MVP)
                    const group = document.createElement('div');
                    group.className = 'element-group';

                    const groupTitle = document.createElement('div');
                    groupTitle.className = 'element-group-title';
                    groupTitle.textContent = contextDef.label;
                    group.appendChild(groupTitle);

                    const paletteItems = document.createElement('div');
                    paletteItems.className = 'element-palette-items';
                    paletteItems.dataset.sortableGroup = 'elements';

                    contextDef.elements.forEach(element => {
                        const card = this.createElementCard(element, true);
                        paletteItems.appendChild(card);
                    });

                    group.appendChild(paletteItems);
                    this.palette.appendChild(group);
                }

                /**
                 * Create an element card
                 * @param {Object} element - Element definition
                 * @param {boolean} isPalette - Whether this is a palette card (draggable clone source)
                 * @returns {HTMLElement}
                 */
                createElementCard(element, isPalette = false) {
                    const card = document.createElement('div');
                    card.className = 'element-card';
                    card.dataset.element = element.id;

                    if (isPalette) {
                        card.dataset.paletteItem = 'true';
                    }

                    card.innerHTML = `
                <span class="element-handle"><span class="icon-menu" aria-hidden="true"></span></span>
                <span class="element-name">${element.label}</span>
                ${!isPalette ? `
                    <span class="element-info">Col 1</span>
                    <button type="button" class="btn-settings" title="${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings'}">
                        <span class="icon-options" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn-remove" title="${this.trans('JBS_TPL_REMOVE_ELEMENT') || 'Remove'}">
                        <span class="icon-cancel" aria-hidden="true"></span>
                    </button>
                ` : ''}
            `;

                    return card;
                }

                /**
                 * Initialize the canvas with one empty drop zone row
                 * Additional rows are created dynamically as needed
                 */
                initCanvas() {
                    this.canvas.innerHTML = '';
                    // Start with one empty row as drop zone
                    this.addRow(1);
                }

                /**
                 * Add a new row to the canvas
                 * @param {number} rowNum - The row number to create
                 * @returns {HTMLElement} The created row element
                 */
                addRow(rowNum) {
                    if (rowNum > this.options.maxRows) {
                        return null;
                    }

                    const rowEl = document.createElement('div');
                    rowEl.className = 'layout-row';
                    rowEl.dataset.row = rowNum;

                    rowEl.innerHTML = `
                <div class="row-label">${this.trans('JBS_TPL_ROW') || 'Row'} ${rowNum}</div>
                <div class="row-elements" data-row="${rowNum}" data-empty-text="${this.trans('JBS_TPL_DROP_ELEMENTS_HERE') || 'Drop elements here'}"></div>
            `;

                    this.canvas.appendChild(rowEl);
                    return rowEl;
                }

                /**
                 * Ensure a row exists, creating it if necessary
                 * @param {number} rowNum - The row number to ensure exists
                 * @returns {HTMLElement} The row element
                 */
                ensureRowExists(rowNum) {
                    let rowEl = this.canvas.querySelector(`.layout-row[data-row="${rowNum}"]`);
                    if (!rowEl && rowNum <= this.options.maxRows) {
                        // Create any missing rows up to this one
                        const existingRows = this.canvas.querySelectorAll('.layout-row');
                        let maxExisting = 0;
                        existingRows.forEach(r => {
                            const num = parseInt(r.dataset.row, 10);
                            if (num > maxExisting) { maxExisting = num; }
                        });

                        for (let i = maxExisting + 1; i <= rowNum; i++) {
                            rowEl = this.addRow(i);
                        }
                    }
                    return rowEl;
                }

                /**
                 * Get the current number of rows
                 * @returns {number}
                 */
                getRowCount() {
                    return this.canvas.querySelectorAll('.layout-row').length;
                }

                /**
                 * Ensure there's always one empty drop zone row at the bottom
                 * Remove empty rows in the middle (but keep at least one row)
                 */
                cleanupRows() {
                    const rows = this.canvas.querySelectorAll('.layout-row');
                    const emptyRows = [];
                    let hasContent = false;

                    // Find empty rows
                    rows.forEach(rowEl => {
                        const elements = rowEl.querySelector('.row-elements');
                        const hasElements = elements && elements.querySelectorAll('.element-card').length > 0;
                        if (hasElements) {
                            hasContent = true;
                        } else {
                            emptyRows.push(rowEl);
                        }
                    });

                    // If we have content, remove all but the last empty row
                    if (hasContent && emptyRows.length > 1) {
                        // Keep only the last empty row
                        for (let i = 0; i < emptyRows.length - 1; i++) {
                            emptyRows[i].remove();
                        }
                        // Renumber remaining rows
                        this.renumberRows();
                    }

                    // Ensure there's at least one empty row at the end for dropping
                    const currentRows = this.canvas.querySelectorAll('.layout-row');
                    const lastRow = currentRows[currentRows.length - 1];
                    if (lastRow) {
                        const lastRowElements = lastRow.querySelector('.row-elements');
                        const lastRowHasElements = lastRowElements &&
                            lastRowElements.querySelectorAll('.element-card').length > 0;

                        if (lastRowHasElements && this.getRowCount() < this.options.maxRows) {
                            // Add a new empty row
                            const newRowNum = this.getRowCount() + 1;
                            const newRow = this.addRow(newRowNum);
                            // Initialize sortable for the new row
                            if (newRow) {
                                this.initSortableForRow(newRow.querySelector('.row-elements'));
                            }
                        }
                    }
                }

                /**
                 * Renumber rows after removing empty ones
                 */
                renumberRows() {
                    const rows = this.canvas.querySelectorAll('.layout-row');
                    rows.forEach((rowEl, index) => {
                        const newRowNum = index + 1;
                        const oldRowNum = parseInt(rowEl.dataset.row, 10);

                        if (oldRowNum !== newRowNum) {
                            rowEl.dataset.row = newRowNum;
                            const label = rowEl.querySelector('.row-label');
                            if (label) {
                                label.textContent = `${this.trans('JBS_TPL_ROW') || 'Row'} ${newRowNum}`;
                            }
                            const elements = rowEl.querySelector('.row-elements');
                            if (elements) {
                                elements.dataset.row = newRowNum;
                            }

                            // Update state for elements in this row
                            this.state.forEach((data, elementId) => {
                                if (data.row === oldRowNum) {
                                    data.row = newRowNum;
                                }
                            });
                        }
                    });
                }

                /**
                 * Initialize sortable for a single row element container
                 * @param {HTMLElement} rowEl - The .row-elements container
                 */
                initSortableForRow(rowEl) {
                    if (!rowEl) { return; }

                    const self = this;
                    const sortable = Sortable.create(rowEl, {
                        group: 'elements',
                        direction: 'horizontal',
                        animation: 80,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.element-handle',
                        swapThreshold: 0.5,
                        invertSwap: true,
                        delay: 0,
                        delayOnTouchOnly: true,
                        touchStartThreshold: 3,
                        onStart: function () {
                            self.saveStateForUndo();
                        },
                        onAdd: function (evt) {
                            self.onElementAdded(evt);
                            evt.item.classList.add('just-dropped');
                            setTimeout(() => evt.item.classList.remove('just-dropped'), 300);
                            // Check if we need to add a new drop zone row
                            self.cleanupRows();
                        },
                        onUpdate: function (evt) {
                            self.onElementMoved(evt);
                        },
                        onRemove: function (evt) {
                            self.onElementRemoved(evt);
                            // Clean up empty rows
                            self.cleanupRows();
                        }
                    });
                    this.sortableInstances.push(sortable);
                }

                /**
                 * Create settings modal
                 */
                createSettingsModal() {
                    // Check if modal already exists
                    if (document.getElementById('layoutSettingsModal')) {
                        this.modal = document.getElementById('layoutSettingsModal');
                        return;
                    }

                    const modal = document.createElement('div');
                    modal.className = 'modal fade layout-settings-modal';
                    modal.id = 'layoutSettingsModal';
                    modal.tabIndex = -1;
                    modal.setAttribute('aria-labelledby', 'layoutSettingsModalLabel');
                    // Use inert attribute instead of aria-hidden to prevent focus issues
                    // See: https://w3c.github.io/aria/#aria-hidden
                    modal.inert = true;

                    modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="layoutSettingsModalLabel">${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings'}</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" for="layout-colspan">${this.trans('JBS_TPL_COLSPAN') || 'Column Span'}</label>
                                <select class="form-select" id="layout-colspan">
                                    ${Array.from({ length: 12 }, (_, i) => `<option value="${i + 1}">${i + 1} ${i === 0 ? 'column' : 'columns'}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_COLSPAN_DESC') || 'Number of columns this element should span (1-12)'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-element-type">${this.trans('JBS_TPL_ELEMENT') || 'Element Type'}</label>
                                <select class="form-select" id="layout-element-type">
                                    ${getElementTypeOptions().map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_ELEMENT_DESC') || 'HTML element type to wrap this content'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-link-type">${this.trans('JBS_TPL_TYPE_OF_LINK') || 'Link Type'}</label>
                                <select class="form-select" id="layout-link-type">
                                    ${getLinkTypeOptions().map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_TYPE_OF_LINK_DESC') || 'How this element should link to the detail view'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-custom-class">${this.trans('JBS_TPL_CUSTOMCLASS') || 'Custom CSS Class'}</label>
                                <input type="text" class="form-control" id="layout-custom-class" placeholder="e.g., my-custom-class">
                                <div class="form-text">${this.trans('JBS_TPL_CUSTOMCLASS_DESC') || 'Additional CSS class to apply to this element'}</div>
                            </div>
                            <div class="form-group" id="layout-date-format-group" style="display:none;">
                                <label class="form-label" for="layout-date-format">${this.trans('JBS_TPL_DATE_FORMAT') || 'Date Format'}</label>
                                <select class="form-select" id="layout-date-format">
                                    ${getDateFormatOptions().map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_DATE_FORMAT_DESC') || 'Choose a date format for this element, or use global template setting'}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">${this.trans('JCANCEL') || 'Cancel'}</button>
                            <button type="button" class="btn btn-success" id="layout-settings-save">${this.trans('JAPPLY') || 'Apply'}</button>
                        </div>
                    </div>
                </div>
            `;

                    document.body.appendChild(modal);
                    this.modal = modal;

                    // Initialize Bootstrap modal - defer until needed
                    this.bsModal = null;
                }

                /**
                 * Get or create Bootstrap modal instance
                 * @returns {Object|null}
                 */
                getModalInstance() {
                    if (this.bsModal) {
                        return this.bsModal;
                    }

                    if (!this.modal) {
                        return null;
                    }

                    // Try to create Bootstrap modal instance
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        this.bsModal = new bootstrap.Modal(this.modal);
                        return this.bsModal;
                    }

                    // Fallback: try to get existing instance from element
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal && bootstrap.Modal.getInstance) {
                        this.bsModal = bootstrap.Modal.getInstance(this.modal) || new bootstrap.Modal(this.modal);
                        return this.bsModal;
                    }

                    return null;
                }

                /**
                 * Initialize Sortable.js instances
                 */
                initSortable() {
                    if (typeof Sortable === 'undefined') {
                        console.error('Sortable.js is not loaded');
                        // Show user-facing error message
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'alert alert-danger';
                        errorMsg.innerHTML = `
                    <span class="icon-warning" aria-hidden="true"></span>
                    ${this.trans('JBS_TPL_SORTABLE_NOT_LOADED') || 'Drag and drop functionality is unavailable. Please refresh the page or contact support.'}
                `;
                        this.container.insertBefore(errorMsg, this.container.firstChild);
                        return;
                    }

                    const self = this;

                    // Initialize sortable for palette items (clone mode)
                    const paletteItems = this.palette.querySelector('.element-palette-items');
                    if (paletteItems) {
                        this.paletteSortable = Sortable.create(paletteItems, {
                            group: {
                                name: 'elements',
                                pull: 'clone',
                                put: false
                            },
                            sort: false,
                            animation: 80, // Faster animation for snappier feel
                            ghostClass: 'sortable-ghost',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'sortable-drag',
                            delay: 0,
                            delayOnTouchOnly: true,
                            touchStartThreshold: 3,
                            onStart: function () {
                                // Save state for undo when starting to drag from palette
                                self.saveStateForUndo();
                            },
                            onClone: function (evt) {
                                // Mark clone as coming from palette - buttons will be added in onAdd handler
                                // Do NOT add buttons here as evt.clone stays in the palette
                                const clone = evt.clone;
                                clone.dataset.paletteItem = 'true';
                            }
                        });
                    }

                    // Initialize sortable for each row
                    this.canvas.querySelectorAll('.row-elements').forEach(rowEl => {
                        this.initSortableForRow(rowEl);
                    });
                }

                /**
                 * Destroy all Sortable instances
                 */
                destroySortables() {
                    if (this.paletteSortable) {
                        this.paletteSortable.destroy();
                        this.paletteSortable = null;
                    }
                    if (this.landingPageSortable) {
                        this.landingPageSortable.destroy();
                        this.landingPageSortable = null;
                    }
                    this.sortableInstances.forEach(instance => instance.destroy());
                    this.sortableInstances = [];

                    // Reset canvas classes
                    if (this.canvas) {
                        this.canvas.classList.remove('landing-page-canvas');
                    }

                    // Show sidebar if it was hidden
                    if (this.sidebar) {
                        this.sidebar.style.display = '';
                    }
                }

                /**
                 * Handle element added to a row
                 * @param {Event} evt - Sortable event
                 */
                onElementAdded(evt) {
                    const elementId = evt.item.dataset.element;
                    const row = parseInt(evt.to.dataset.row, 10);
                    const col = this.calculateColumn(evt.to, evt.newIndex);

                    // Check if element already exists in state for this context
                    if (this.state.has(elementId)) {
                        // Move existing element to new position
                        const data = this.state.get(elementId);
                        data.row = row;
                        data.col = col;
                    } else {
                        // Add new element with default values
                        this.state.set(elementId, {
                            row: row,
                            col: col,
                            colspan: '1',
                            element: '1', // Default: Paragraph
                            custom: '',
                            linktype: '0' // Default: No link
                        });
                    }

                    // Make sure the item has proper buttons if it came from palette
                    if (evt.item.dataset.paletteItem === 'true' || evt.item.dataset.paletteItem === '') {
                        delete evt.item.dataset.paletteItem;
                        const element = this.getElementDefinition(elementId);
                        if (element) {
                            evt.item.innerHTML = `
                        <span class="element-handle"><span class="icon-menu" aria-hidden="true"></span></span>
                        <span class="element-name">${element.label}</span>
                        <span class="element-info">Col ${col}</span>
                        <button type="button" class="btn-settings" title="${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings'}">
                            <span class="icon-options" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn-remove" title="${this.trans('JBS_TPL_REMOVE_ELEMENT') || 'Remove'}">
                            <span class="icon-cancel" aria-hidden="true"></span>
                        </button>
                    `;
                        }

                        // Add resize handles and make focusable
                        evt.item.tabIndex = 0;
                        this.addResizeHandles(evt.item);
                    }

                    this.updateElementInfo(evt.item);
                    // Auto-distribute colspans in the target row
                    this.distributeColspans(evt.to);

                    // Mark as dirty since we added an element
                    this.markDirty();
                }

                /**
                 * Handle element moved within or between rows
                 * @param {Event} evt - Sortable event
                 */
                onElementMoved(evt) {
                    // Redistribute colspans in affected rows
                    this.distributeColspans(evt.to);
                    if (evt.from !== evt.to) {
                        this.distributeColspans(evt.from);
                    }

                    // Mark as dirty since we moved an element
                    this.markDirty();
                }

                /**
                 * Handle element removed from a row (moved to another row)
                 * @param {Event} evt - Sortable event
                 */
                onElementRemoved(evt) {
                    // Redistribute colspans in the source row
                    this.distributeColspans(evt.from);
                }

                /**
                 * Calculate column position based on index
                 * @param {HTMLElement} rowEl - Row element
                 * @param {number} index - Element index
                 * @returns {number}
                 */
                calculateColumn(rowEl, index) {
                    const children = Array.from(rowEl.children).filter(el => !el.classList.contains('sortable-ghost'));
                    let col = 1;

                    for (let i = 0; i < index && i < children.length; i++) {
                        const elementId = children[i].dataset.element;
                        const data = this.state.get(elementId);
                        col += data ? parseInt(data.colspan, 10) || 1 : 1;
                    }

                    return Math.min(col, this.options.numCols);
                }

                /**
                 * Recalculate column positions for all elements in a row
                 * Validates that col + colspan doesn't exceed grid width (12 columns)
                 * @param {HTMLElement} rowEl - Row element
                 */
                recalculateColumns(rowEl) {
                    const row = parseInt(rowEl.dataset.row, 10);
                    const children = Array.from(rowEl.children).filter(el => !el.classList.contains('sortable-ghost'));
                    let col = 1;

                    children.forEach(card => {
                        const elementId = card.dataset.element;
                        const data = this.state.get(elementId);

                        if (data) {
                            data.row = row;
                            data.col = col;

                            // Validate colspan doesn't exceed remaining grid space
                            let colspan = parseInt(data.colspan, 10) || 1;
                            const remainingCols = this.options.numCols - col + 1;
                            if (colspan > remainingCols) {
                                colspan = remainingCols;
                                data.colspan = String(colspan);
                                card.dataset.colspan = String(colspan);
                            }

                            col += colspan;
                        }

                        this.updateElementInfo(card);
                    });
                }

                /**
                 * Auto-distribute colspans evenly across elements in a row
                 * First element gets full width (12), additional elements split evenly
                 * @param {HTMLElement} rowEl - Row element
                 * @param {boolean} preserveManual - If true, don't change manually set colspans
                 */
                distributeColspans(rowEl, preserveManual = false) {
                    const children = Array.from(rowEl.children).filter(el =>
                        !el.classList.contains('sortable-ghost') &&
                        el.classList.contains('element-card')
                    );

                    const count = children.length;
                    if (count === 0) { return; }

                    const totalCols = this.options.numCols; // 12

                    if (count === 1) {
                        // Single element gets full width
                        const data = this.state.get(children[0].dataset.element);
                        if (data && (!preserveManual || !data.manualColspan)) {
                            data.colspan = String(totalCols);
                            children[0].dataset.colspan = String(totalCols);
                        }
                    } else {
                        // Multiple elements - distribute evenly
                        const baseColspan = Math.floor(totalCols / count);
                        const remainder = totalCols % count;

                        children.forEach((card, index) => {
                            const data = this.state.get(card.dataset.element);
                            if (data && (!preserveManual || !data.manualColspan)) {
                                // First 'remainder' elements get +1 colspan
                                const colspan = baseColspan + (index < remainder ? 1 : 0);
                                data.colspan = String(colspan);
                                card.dataset.colspan = String(colspan);
                            }
                        });
                    }

                    // Recalculate column positions
                    this.recalculateColumns(rowEl);
                }

                /**
                 * Update element info display
                 * @param {HTMLElement} card - Element card
                 */
                updateElementInfo(card) {
                    const elementId = card.dataset.element;
                    const data = this.state.get(elementId);

                    if (!data) { return; }

                    const infoEl = card.querySelector('.element-info');
                    if (infoEl) {
                        const colspan = parseInt(data.colspan, 10) || 1;
                        const endCol = data.col + colspan - 1;
                        infoEl.textContent = colspan > 1 ? `Col ${data.col}-${endCol}` : `Col ${data.col}`;
                    }

                    // Update colspan data attribute for CSS styling
                    card.dataset.colspan = data.colspan || '1';
                }

                /**
                 * Get element definition by ID
                 * @param {string} elementId - Element ID
                 * @returns {Object|null}
                 */
                getElementDefinition(elementId) {
                    const contextDef = getElementDefs()[this.currentContext];
                    if (!contextDef) { return null; }
                    return contextDef.elements.find(el => el.id === elementId) || null;
                }

                /**
                 * Bind event listeners
                 */
                bindEvents() {
                    // Settings button click
                    this.container.addEventListener('click', (e) => {
                        const settingsBtn = e.target.closest('.btn-settings');
                        if (settingsBtn) {
                            const card = settingsBtn.closest('.element-card');
                            if (card) {
                                this.openSettings(card.dataset.element);
                            }
                        }

                        const removeBtn = e.target.closest('.btn-remove');
                        if (removeBtn) {
                            const card = removeBtn.closest('.element-card');
                            if (card) {
                                this.saveStateForUndo();
                                this.removeElement(card);
                            }
                        }
                    });

                    // Settings modal save
                    const saveBtn = document.getElementById('layout-settings-save');
                    if (saveBtn) {
                        saveBtn.addEventListener('click', () => this.saveSettings());
                    }

                    // Settings modal close (for fallback when Bootstrap modal not available)
                    if (this.modal) {
                        const closeBtn = this.modal.querySelector('.btn-close');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', () => this.closeSettingsModal());
                        }

                        // Handle Bootstrap modal events for accessibility
                        // Before hide: move focus out to prevent aria-hidden warning
                        this.modal.addEventListener('hide.bs.modal', () => {
                            const triggerElement = this.currentSettingsElement ?
                                this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"] .btn-settings`) :
                                null;
                            if (triggerElement) {
                                triggerElement.focus();
                            }
                        });

                        // After hide: set inert to prevent future focus issues
                        this.modal.addEventListener('hidden.bs.modal', () => {
                            this.modal.inert = true;
                            this.currentSettingsElement = null;
                        });

                        // Use MutationObserver to remove aria-hidden whenever Bootstrap sets it
                        // We use inert instead, so aria-hidden is not needed and causes warnings
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                                    if (this.modal.getAttribute('aria-hidden') === 'true') {
                                        this.modal.removeAttribute('aria-hidden');
                                    }
                                }
                            });
                        });
                        observer.observe(this.modal, { attributes: true, attributeFilter: ['aria-hidden'] });
                    }

                    // Form submit - sync state to form fields and mark clean
                    const form = document.getElementById(this.options.formId);
                    if (form) {
                        form.addEventListener('submit', () => {
                            this.syncToForm();
                            this.markClean(); // Clear dirty state on save
                        });
                    }

                    // Also sync before Joomla toolbar actions (Save, Apply, Save & New, Save as Copy)
                    document.addEventListener('click', (e) => {
                        const toolbarBtn = e.target.closest('.button-apply, .button-save, .button-save-new, .button-save-copy');
                        if (toolbarBtn) {
                            this.syncToForm();
                            this.markClean(); // Clear dirty state on save
                        }
                    });

                    // Intercept Close/Cancel button to warn about unsaved changes
                    document.addEventListener('click', (e) => {
                        const cancelBtn = e.target.closest('.button-cancel');
                        if (cancelBtn && this.isDirty) {
                            const message = this.trans('JBS_TPL_UNSAVED_CHANGES_CONFIRM') || 'You have unsaved changes. Are you sure you want to leave without saving?';
                            if (!window.confirm(message)) {
                                e.preventDefault();
                                e.stopPropagation();
                                e.stopImmediatePropagation();
                            }
                        }
                    }, true); // Use capture phase to intercept before Joomla's handler

                    // Toolbar buttons
                    if (this.toolbar) {
                        // Undo button
                        const undoBtn = this.toolbar.querySelector('.btn-undo');
                        if (undoBtn) {
                            undoBtn.addEventListener('click', () => this.undo());
                        }

                        // Redo button
                        const redoBtn = this.toolbar.querySelector('.btn-redo');
                        if (redoBtn) {
                            redoBtn.addEventListener('click', () => this.redo());
                        }

                        // Grid toggle button
                        const gridBtn = this.toolbar.querySelector('.btn-grid');
                        if (gridBtn) {
                            gridBtn.addEventListener('click', () => this.toggleGrid());
                        }

                        // View settings button
                        const viewSettingsBtn = this.toolbar.querySelector('.btn-view-settings');
                        if (viewSettingsBtn) {
                            viewSettingsBtn.addEventListener('click', () => this.openViewSettings());
                        }
                    }

                    // Keyboard shortcuts
                    document.addEventListener('keydown', (e) => this.handleKeyboard(e));
                }

                /**
                 * Remove an element from the layout
                 * @param {HTMLElement} card - Element card to remove
                 */
                removeElement(card) {
                    const elementId = card.dataset.element;
                    const rowEl = card.closest('.row-elements');

                    // Remove from state
                    this.state.delete(elementId);

                    // Remove from DOM
                    card.remove();

                    // Redistribute colspans for remaining elements
                    if (rowEl) {
                        this.distributeColspans(rowEl);
                    }

                    // Mark as dirty since we removed an element
                    this.markDirty();
                }

                /**
                 * Mark the editor as having unsaved changes
                 * Adds a beforeunload warning to prevent accidental navigation
                 */
                markDirty() {
                    if (this.isDirty) {
                        return; // Already dirty
                    }

                    this.isDirty = true;

                    // Add beforeunload handler to warn about unsaved changes
                    if (!this.beforeUnloadHandler) {
                        this.beforeUnloadHandler = (e) => {
                            if (this.isDirty) {
                                const message = this.trans('JBS_TPL_UNSAVED_CHANGES') || 'You have unsaved changes. Are you sure you want to leave?';
                                e.preventDefault();
                                e.returnValue = message; // Required for Chrome
                                return message;
                            }
                        };
                        window.addEventListener('beforeunload', this.beforeUnloadHandler);
                    }
                }

                /**
                 * Mark the editor as clean (no unsaved changes)
                 * Removes the beforeunload warning
                 */
                markClean() {
                    this.isDirty = false;

                    // Remove beforeunload handler
                    if (this.beforeUnloadHandler) {
                        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
                        this.beforeUnloadHandler = null;
                    }
                }

                /**
                 * Open settings modal for an element
                 * @param {string} elementId - Element ID
                 */
                openSettings(elementId) {
                    this.currentSettingsElement = elementId;
                    let data = this.state.get(elementId);

                    // If no state exists, create default state
                    if (!data) {
                        data = {
                            row: 1,
                            col: 1,
                            colspan: '1',
                            element: '1',
                            custom: '',
                            linktype: '0',
                            date_format: ''
                        };
                        this.state.set(elementId, data);
                    }

                    // Update modal title to show element name
                    const elementDef = this.getElementDefinition(elementId);
                    const modalTitle = document.getElementById('layoutSettingsModalLabel');
                    if (modalTitle && elementDef) {
                        modalTitle.textContent = `${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings'}: ${elementDef.label}`;
                    }

                    // Populate modal fields
                    const colspanEl = document.getElementById('layout-colspan');
                    const elementTypeEl = document.getElementById('layout-element-type');
                    const linkTypeEl = document.getElementById('layout-link-type');
                    const customClassEl = document.getElementById('layout-custom-class');
                    const dateFormatEl = document.getElementById('layout-date-format');
                    const dateFormatGroup = document.getElementById('layout-date-format-group');

                    // Rebuild link type options based on current context
                    if (linkTypeEl) {
                        const contextLinkOptions = getLinkTypeOptions(this.currentContext);
                        linkTypeEl.innerHTML = contextLinkOptions.map(opt =>
                            `<option value="${opt.value}">${opt.label}</option>`
                        ).join('');
                    }

                    if (colspanEl) { colspanEl.value = String(data.colspan) || '1'; }
                    if (elementTypeEl) { elementTypeEl.value = String(data.element) || '1'; }
                    if (linkTypeEl) { linkTypeEl.value = String(data.linktype) || '0'; }
                    if (customClassEl) { customClassEl.value = data.custom || ''; }
                    if (dateFormatEl) { dateFormatEl.value = data.date_format || ''; }

                    // Store original values for change detection when modal closes
                    this.originalModalValues = {
                        colspan: colspanEl ? colspanEl.value : '',
                        element: elementTypeEl ? elementTypeEl.value : '',
                        linktype: linkTypeEl ? linkTypeEl.value : '',
                        custom: customClassEl ? customClassEl.value : '',
                        date_format: dateFormatEl ? dateFormatEl.value : ''
                    };

                    // Show date format field only for date-related elements
                    const isDateElement = elementId.toLowerCase().includes('date') ||
                        elementId.toLowerCase().includes('studydate');
                    if (dateFormatGroup) {
                        dateFormatGroup.style.display = isDateElement ? 'block' : 'none';
                    }

                    // Show modal
                    const modalInstance = this.getModalInstance();
                    if (modalInstance) {
                        // Remove inert before Bootstrap shows the modal
                        if (this.modal) {
                            this.modal.inert = false;
                        }
                        modalInstance.show();
                    } else {
                        // Fallback: manually show modal with CSS classes
                        if (this.modal) {
                            // Remove inert to allow interaction
                            this.modal.inert = false;
                            this.modal.classList.add('show');
                            this.modal.style.display = 'block';
                            this.modal.setAttribute('aria-modal', 'true');
                            this.modal.setAttribute('role', 'dialog');
                            document.body.classList.add('modal-open');

                            // Create backdrop
                            let backdrop = document.querySelector('.modal-backdrop');
                            if (!backdrop) {
                                backdrop = document.createElement('div');
                                backdrop.className = 'modal-backdrop fade show';
                                document.body.appendChild(backdrop);
                            }

                            // Focus the first focusable element in the modal after a brief delay
                            // to ensure the modal is fully visible
                            requestAnimationFrame(() => {
                                const firstFocusable = this.modal.querySelector('select, input, button, [href], textarea, [tabindex]:not([tabindex="-1"])');
                                if (firstFocusable) {
                                    firstFocusable.focus();
                                }
                            });
                        }
                    }
                }

                /**
                 * Save settings from modal
                 */
                saveSettings() {
                    if (!this.currentSettingsElement) { return; }

                    const data = this.state.get(this.currentSettingsElement);
                    if (!data) { return; }

                    // Get values from modal
                    let newColspan = parseInt(document.getElementById('layout-colspan').value, 10) || 1;

                    // Validate colspan is within bounds (1-12)
                    newColspan = Math.max(1, Math.min(this.options.numCols, newColspan));

                    // Mark colspan as manually set if changed by user
                    if (data.colspan !== String(newColspan)) {
                        data.manualColspan = true;
                    }

                    data.colspan = String(newColspan);
                    data.element = document.getElementById('layout-element-type').value;
                    data.linktype = document.getElementById('layout-link-type').value;
                    data.custom = document.getElementById('layout-custom-class').value;
                    data.date_format = document.getElementById('layout-date-format').value;

                    // Update visual display
                    const card = this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"]`);
                    if (card) {
                        this.updateElementInfo(card);

                        // Recalculate columns for the row
                        const rowEl = card.closest('.row-elements');
                        if (rowEl) {
                            this.recalculateColumns(rowEl);
                        }
                    }

                    // Mark as dirty since settings were changed
                    this.markDirty();

                    // Close modal - global event listeners in bindEvents() handle focus and inert
                    const modalInstance = this.getModalInstance();
                    if (modalInstance) {
                        modalInstance.hide();
                    } else if (this.modal) {
                        // Fallback: manually hide modal
                        // Move focus out first
                        const triggerElement = this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"] .btn-settings`);
                        if (triggerElement) {
                            triggerElement.focus();
                        } else {
                            document.body.focus();
                        }

                        this.modal.classList.remove('show');
                        this.modal.style.display = 'none';
                        this.modal.removeAttribute('aria-modal');
                        this.modal.removeAttribute('role');
                        this.modal.inert = true;
                        document.body.classList.remove('modal-open');

                        // Remove backdrop
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }

                        this.currentSettingsElement = null;
                    }
                }

                /**
                 * Close settings modal without saving (for close button)
                 */
                closeSettingsModal() {
                    // Check if any values have changed
                    if (this.originalModalValues && this.hasModalChanges()) {
                        const message = this.trans('JBS_TPL_MODAL_UNSAVED_CHANGES') || 'You have unsaved changes in this dialog. Discard changes?';
                        if (!window.confirm(message)) {
                            return; // User cancelled, keep modal open
                        }
                    }

                    // Clear original values
                    this.originalModalValues = null;

                    // Close modal - global event listeners in bindEvents() handle focus and inert
                    const modalInstance = this.getModalInstance();
                    if (modalInstance) {
                        modalInstance.hide();
                    } else if (this.modal) {
                        // Fallback: manually hide modal
                        // Move focus out first
                        const triggerElement = this.currentSettingsElement ?
                            this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"] .btn-settings`) :
                            null;
                        if (triggerElement) {
                            triggerElement.focus();
                        } else {
                            document.body.focus();
                        }

                        this.modal.classList.remove('show');
                        this.modal.style.display = 'none';
                        this.modal.removeAttribute('aria-modal');
                        this.modal.removeAttribute('role');
                        this.modal.inert = true;
                        document.body.classList.remove('modal-open');

                        // Remove backdrop
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }

                        this.currentSettingsElement = null;
                    }
                }

                /**
                 * Check if modal field values have changed from original
                 * @returns {boolean}
                 */
                hasModalChanges() {
                    if (!this.originalModalValues) {
                        return false;
                    }

                    const colspanEl = document.getElementById('layout-colspan');
                    const elementTypeEl = document.getElementById('layout-element-type');
                    const linkTypeEl = document.getElementById('layout-link-type');
                    const customClassEl = document.getElementById('layout-custom-class');
                    const dateFormatEl = document.getElementById('layout-date-format');

                    const current = {
                        colspan: colspanEl ? colspanEl.value : '',
                        element: elementTypeEl ? elementTypeEl.value : '',
                        linktype: linkTypeEl ? linkTypeEl.value : '',
                        custom: customClassEl ? customClassEl.value : '',
                        date_format: dateFormatEl ? dateFormatEl.value : ''
                    };

                    // Compare each field
                    return current.colspan !== this.originalModalValues.colspan ||
                        current.element !== this.originalModalValues.element ||
                        current.linktype !== this.originalModalValues.linktype ||
                        current.custom !== this.originalModalValues.custom ||
                        current.date_format !== this.originalModalValues.date_format;
                }

                /**
                 * Load state from existing form params
                 */
                loadFromParams() {
                    const contextDef = getElementDefs()[this.currentContext];
                    if (!contextDef) { return; }

                    const prefix = contextDef.prefix;

                    // Get params from Joomla script options (passed from PHP)
                    // Use defensive check in case Joomla object is not available
                    const templateParams = (window.Joomla?.getOptions?.('com_proclaim.templateParams')) || {};

                    contextDef.elements.forEach(element => {
                        const fieldPrefix = prefix + element.id;

                        // Read from script options first, fall back to form fields
                        let row = parseInt(templateParams[fieldPrefix + 'row'], 10) || 0;
                        let col = parseInt(templateParams[fieldPrefix + 'col'], 10) || 1;
                        let colspan = templateParams[fieldPrefix + 'colspan'] || '1';
                        let elementType = templateParams[fieldPrefix + 'element'];
                        if (elementType === undefined || elementType === null || elementType === '') {
                            elementType = '1';
                        }
                        let custom = templateParams[fieldPrefix + 'custom'] || '';
                        let linktype = templateParams[fieldPrefix + 'linktype'] || '0';
                        let dateFormat = templateParams[fieldPrefix + 'date_format'] || '';

                        // Try form fields as fallback (in case they're loaded)
                        const rowField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}row]"]`);
                        if (rowField) {
                            row = parseInt(rowField.value, 10) || row;
                            const colField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}col]"]`);
                            const colspanField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}colspan]"]`);
                            const elementField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}element]"]`);
                            const customField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}custom]"]`);
                            const linktypeField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}linktype]"]`);
                            const dateFormatField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}date_format]"]`);

                            if (colField) { col = parseInt(colField.value, 10) || col; }
                            if (colspanField) { colspan = colspanField.value || colspan; }
                            if (elementField) { elementType = elementField.value || elementType; }
                            if (customField) { custom = customField.value || custom; }
                            if (linktypeField) { linktype = linktypeField.value || linktype; }
                            if (dateFormatField) { dateFormat = dateFormatField.value || dateFormat; }
                        }

                        // Only add to canvas if row > 0 (element is visible)
                        if (row > 0) {
                            const data = {
                                row: row,
                                col: col,
                                colspan: colspan,
                                element: elementType,
                                custom: custom,
                                linktype: linktype,
                                date_format: dateFormat
                            };

                            this.state.set(element.id, data);

                            // Add card to canvas
                            this.addElementToCanvas(element, data);
                        }
                    });

                    // Sort elements in each row by column
                    this.sortCanvasElements();

                    // Migrate legacy templates with irregular colspans
                    this.migrateLegacyColspans();
                }

                /**
                 * Add an element to the canvas
                 * @param {Object} element - Element definition
                 * @param {Object} data - Element state data
                 */
                addElementToCanvas(element, data) {
                    // Ensure the row exists (creates it if needed)
                    this.ensureRowExists(data.row);

                    const rowEl = this.canvas.querySelector(`.row-elements[data-row="${data.row}"]`);
                    if (!rowEl) { return; }

                    const card = this.createElementCard(element, false);
                    card.dataset.colspan = data.colspan;
                    card.tabIndex = 0; // Make focusable for keyboard navigation
                    rowEl.appendChild(card);

                    // Add resize handles
                    this.addResizeHandles(card);

                    this.updateElementInfo(card);
                }

                /**
                 * Sort elements in canvas rows by column
                 */
                sortCanvasElements() {
                    this.canvas.querySelectorAll('.row-elements').forEach(rowEl => {
                        const cards = Array.from(rowEl.querySelectorAll('.element-card'));

                        cards.sort((a, b) => {
                            const dataA = this.state.get(a.dataset.element);
                            const dataB = this.state.get(b.dataset.element);
                            return (dataA?.col || 0) - (dataB?.col || 0);
                        });

                        cards.forEach(card => rowEl.appendChild(card));
                    });
                }

                /**
                 * Migrate legacy templates with irregular colspan values
                 * Legacy templates may have:
                 * - Colspans that don't sum to 12
                 * - Single elements with small colspans (not using full width)
                 * - Inconsistent column distributions
                 *
                 * This method normalizes the layout by auto-distributing colspans.
                 */
                migrateLegacyColspans() {
                    // Group elements by row
                    const rowGroups = new Map();

                    this.state.forEach((data, elementId) => {
                        if (data.row > 0) {
                            if (!rowGroups.has(data.row)) {
                                rowGroups.set(data.row, []);
                            }
                            rowGroups.get(data.row).push({ elementId, data });
                        }
                    });

                    // Check each row for legacy patterns
                    rowGroups.forEach((elements, row) => {
                        const totalColspan = elements.reduce((sum, el) => sum + (parseInt(el.data.colspan, 10) || 1), 0);
                        const elementCount = elements.length;

                        // Legacy pattern detection:
                        // 1. Single element not using full width (colspan < 12)
                        // 2. Multiple elements not filling the row (total < 12)
                        // 3. Elements overflowing the row (total > 12)
                        const needsMigration = (
                            (elementCount === 1 && totalColspan < 12) ||
                            (elementCount > 1 && totalColspan !== 12)
                        );

                        if (needsMigration) {
                            // Get the row element and redistribute
                            const rowEl = this.canvas.querySelector(`.row-elements[data-row="${row}"]`);
                            if (rowEl) {
                                // Don't preserve manual colspans during migration
                                this.distributeColspans(rowEl, false);
                            }
                        }
                    });
                }

                /**
                 * Sync state to hidden form fields before save
                 */
                syncToForm() {
                    const contextDef = getElementDefs()[this.currentContext];
                    if (!contextDef) { return; }

                    // Use landing page sync for order-only contexts
                    if (contextDef.isOrderOnly) {
                        this.syncLandingPageToForm();
                        return;
                    }

                    const prefix = contextDef.prefix;
                    const form = document.getElementById(this.options.formId);

                    contextDef.elements.forEach(element => {
                        const fieldPrefix = prefix + element.id;
                        const data = this.state.get(element.id);

                        // Field names for this element
                        const fieldNames = {
                            row: `${this.options.paramsPrefix}[${fieldPrefix}row]`,
                            col: `${this.options.paramsPrefix}[${fieldPrefix}col]`,
                            colspan: `${this.options.paramsPrefix}[${fieldPrefix}colspan]`,
                            element: `${this.options.paramsPrefix}[${fieldPrefix}element]`,
                            custom: `${this.options.paramsPrefix}[${fieldPrefix}custom]`,
                            linktype: `${this.options.paramsPrefix}[${fieldPrefix}linktype]`,
                            date_format: `${this.options.paramsPrefix}[${fieldPrefix}date_format]`
                        };

                        // Helper to get or create a hidden input field
                        const getOrCreateField = (name, value) => {
                            let field = document.querySelector(`[name="${name}"]`);
                            if (!field && form) {
                                // Create hidden input if form field doesn't exist (lazy-loaded)
                                field = document.createElement('input');
                                field.type = 'hidden';
                                field.name = name;
                                form.appendChild(field);
                            }
                            if (field) {
                                field.value = value;
                            }
                            return field;
                        };

                        if (data) {
                            // Element is in layout - set all values
                            getOrCreateField(fieldNames.row, data.row);
                            getOrCreateField(fieldNames.col, data.col);
                            getOrCreateField(fieldNames.colspan, data.colspan);
                            getOrCreateField(fieldNames.element, data.element);
                            getOrCreateField(fieldNames.custom, data.custom);
                            getOrCreateField(fieldNames.linktype, data.linktype);
                            getOrCreateField(fieldNames.date_format, data.date_format || '');
                        } else {
                            // Element not in layout - set row to 0 (hidden)
                            getOrCreateField(fieldNames.row, '0');
                        }
                    });
                }

                /**
                 * Get current state (for debugging or export)
                 * @returns {Object}
                 */
                getState() {
                    const state = {};
                    this.state.forEach((value, key) => {
                        state[key] = { ...value };
                    });
                    return state;
                }

                // =====================================================================
                // Undo/Redo Functionality
                // =====================================================================

                /**
                 * Save current state to undo stack
                 */
                saveStateForUndo() {
                    // Clone current state
                    const stateCopy = new Map();
                    this.state.forEach((value, key) => {
                        stateCopy.set(key, { ...value });
                    });

                    this.undoStack.push(stateCopy);

                    // Limit history size
                    if (this.undoStack.length > this.maxHistory) {
                        this.undoStack.shift();
                    }

                    // Clear redo stack when new action is performed
                    this.redoStack = [];

                    this.updateToolbarState();
                }

                /**
                 * Undo last action
                 */
                undo() {
                    if (this.undoStack.length === 0) { return; }

                    // Save current state to redo stack
                    const currentState = new Map();
                    this.state.forEach((value, key) => {
                        currentState.set(key, { ...value });
                    });
                    this.redoStack.push(currentState);

                    // Restore previous state
                    this.state = this.undoStack.pop();

                    // Rebuild canvas
                    this.rebuildCanvas();
                    this.updateToolbarState();
                }

                /**
                 * Redo last undone action
                 */
                redo() {
                    if (this.redoStack.length === 0) { return; }

                    // Save current state to undo stack
                    const currentState = new Map();
                    this.state.forEach((value, key) => {
                        currentState.set(key, { ...value });
                    });
                    this.undoStack.push(currentState);

                    // Restore next state
                    this.state = this.redoStack.pop();

                    // Rebuild canvas
                    this.rebuildCanvas();
                    this.updateToolbarState();
                }

                /**
                 * Rebuild canvas from state
                 */
                rebuildCanvas() {
                    // Clear all elements from canvas rows
                    this.canvas.querySelectorAll('.row-elements').forEach(rowEl => {
                        rowEl.innerHTML = '';
                    });

                    // Re-add elements from state
                    const contextDef = getElementDefs()[this.currentContext];
                    if (!contextDef) { return; }

                    this.state.forEach((data, elementId) => {
                        const element = contextDef.elements.find(el => el.id === elementId);
                        if (element && data.row > 0) {
                            this.addElementToCanvas(element, data);
                        }
                    });

                    this.sortCanvasElements();
                }

                /**
                 * Update toolbar button states
                 */
                updateToolbarState() {
                    const undoBtn = this.toolbar?.querySelector('.btn-undo');
                    const redoBtn = this.toolbar?.querySelector('.btn-redo');

                    if (undoBtn) {
                        undoBtn.disabled = this.undoStack.length === 0;
                    }
                    if (redoBtn) {
                        redoBtn.disabled = this.redoStack.length === 0;
                    }
                }

                // =====================================================================
                // Grid Toggle
                // =====================================================================

                /**
                 * Toggle grid overlay visibility
                 */
                toggleGrid() {
                    this.showGrid = !this.showGrid;

                    if (this.editor) {
                        this.editor.classList.toggle('show-grid', this.showGrid);
                    }

                    // Swap primary/secondary classes for grid button
                    const gridBtn = this.toolbar?.querySelector('.btn-grid');
                    if (gridBtn) {
                        gridBtn.classList.toggle('btn-primary', this.showGrid);
                        gridBtn.classList.toggle('btn-secondary', !this.showGrid);
                    }
                }

                // =====================================================================
                // Resize Handles
                // =====================================================================

                /**
                 * Add resize handles to an element card
                 * @param {HTMLElement} card - Element card
                 */
                addResizeHandles(card) {
                    // Only add to placed elements, not palette items
                    if (card.dataset.paletteItem !== undefined) { return; }

                    // Right handle - affects element to the right
                    const rightHandle = document.createElement('div');
                    rightHandle.className = 'resize-handle resize-handle-right';
                    rightHandle.addEventListener('mousedown', (e) => this.startResize(e, card, 'right'));
                    card.appendChild(rightHandle);

                    // Left handle - affects element to the left
                    const leftHandle = document.createElement('div');
                    leftHandle.className = 'resize-handle resize-handle-left';
                    leftHandle.addEventListener('mousedown', (e) => this.startResize(e, card, 'left'));
                    card.appendChild(leftHandle);
                }

                /**
                 * Start resizing an element
                 * @param {MouseEvent} e - Mouse event
                 * @param {HTMLElement} card - Element card
                 * @param {string} direction - 'left' or 'right'
                 */
                startResize(e, card, direction) {
                    e.preventDefault();
                    e.stopPropagation();

                    const rowEl = card.closest('.row-elements');
                    if (!rowEl) { return; }

                    // Get all element cards in order
                    const children = Array.from(rowEl.children).filter(el =>
                        !el.classList.contains('sortable-ghost') &&
                        el.classList.contains('element-card')
                    );

                    const cardIndex = children.indexOf(card);

                    // Find the neighbor element based on direction
                    let neighborCard = null;
                    let isOuterEdge = false;

                    if (direction === 'right') {
                        if (cardIndex < children.length - 1) {
                            neighborCard = children[cardIndex + 1];
                        } else {
                            // Right edge of last element - outer edge resize
                            isOuterEdge = true;
                        }
                    } else if (direction === 'left') {
                        if (cardIndex > 0) {
                            neighborCard = children[cardIndex - 1];
                        } else {
                            // Left edge of first element - outer edge resize
                            isOuterEdge = true;
                        }
                    }

                    // For outer edge, we need at least 2 elements to redistribute
                    if (isOuterEdge && children.length < 2) { return; }

                    this.isResizing = true;
                    this.resizeCard = card;
                    this.resizeNeighborCard = neighborCard; // null for outer edge
                    this.resizeDirection = direction;
                    this.resizeIsOuterEdge = isOuterEdge;
                    this.resizeStartX = e.clientX;
                    this.resizeRowEl = rowEl;

                    const data = this.state.get(card.dataset.element);
                    this.resizeStartColspan = parseInt(data?.colspan || 1, 10);

                    if (neighborCard) {
                        const neighborData = this.state.get(neighborCard.dataset.element);
                        this.resizeNeighborStartColspan = parseInt(neighborData?.colspan || 1, 10);
                    } else {
                        // For outer edge, track other elements' colspans
                        this.resizeOtherElements = children.filter(el => el !== card);
                        this.resizeOtherStartColspans = this.resizeOtherElements.map(el => {
                            const d = this.state.get(el.dataset.element);
                            return parseInt(d?.colspan || 1, 10);
                        });
                    }

                    // Calculate column width
                    this.columnWidth = rowEl.offsetWidth / 12;

                    // Save state for undo
                    this.saveStateForUndo();

                    card.classList.add('resizing');
                    if (neighborCard) {
                        neighborCard.classList.add('resizing');
                    }

                    // Add mousemove and mouseup listeners
                    document.addEventListener('mousemove', this.handleResize);
                    document.addEventListener('mouseup', this.endResize);
                }

                /**
                 * Handle resize drag
                 * Resizing affects either the immediate neighbor or redistributes other elements for outer edges
                 * @param {MouseEvent} e - Mouse event
                 */
                handleResize(e) {
                    if (!this.isResizing || !this.resizeCard) { return; }

                    const deltaX = e.clientX - this.resizeStartX;
                    let colsDelta = Math.round(deltaX / this.columnWidth);

                    // For left handle, invert the delta (dragging left = growing current element)
                    if (this.resizeDirection === 'left') {
                        colsDelta = -colsDelta;
                    }

                    if (this.resizeIsOuterEdge) {
                        // Outer edge resize - redistribute among other elements
                        this.handleOuterEdgeResize(colsDelta);
                    } else if (this.resizeNeighborCard) {
                        // Inner edge resize - only affect neighbor
                        this.handleInnerEdgeResize(colsDelta);
                    }
                }

                /**
                 * Handle inner edge resize (between two adjacent elements)
                 * @param {number} colsDelta - Column change
                 */
                handleInnerEdgeResize(colsDelta) {
                    // Calculate new colspans for both elements
                    // Total combined colspan stays the same
                    const combinedColspan = this.resizeStartColspan + this.resizeNeighborStartColspan;

                    let newColspan = this.resizeStartColspan + colsDelta;
                    let newNeighborColspan = combinedColspan - newColspan;

                    // Ensure both elements have at least 1 column
                    if (newColspan < 1) {
                        newColspan = 1;
                        newNeighborColspan = combinedColspan - 1;
                    }
                    if (newNeighborColspan < 1) {
                        newNeighborColspan = 1;
                        newColspan = combinedColspan - 1;
                    }

                    // Update current element
                    const data = this.state.get(this.resizeCard.dataset.element);
                    if (data) {
                        data.colspan = String(newColspan);
                        data.manualColspan = true;
                        this.resizeCard.dataset.colspan = String(newColspan);
                        this.updateElementInfo(this.resizeCard);
                    }

                    // Update neighbor element
                    const neighborData = this.state.get(this.resizeNeighborCard.dataset.element);
                    if (neighborData) {
                        neighborData.colspan = String(newNeighborColspan);
                        neighborData.manualColspan = true;
                        this.resizeNeighborCard.dataset.colspan = String(newNeighborColspan);
                        this.updateElementInfo(this.resizeNeighborCard);
                    }
                }

                /**
                 * Handle outer edge resize (first element's left or last element's right)
                 * Redistributes remaining space among other elements
                 * @param {number} colsDelta - Column change
                 */
                handleOuterEdgeResize(colsDelta) {
                    const otherCount = this.resizeOtherElements.length;
                    if (otherCount === 0) { return; }

                    // Calculate new colspan for the resized element
                    let newColspan = this.resizeStartColspan + colsDelta;

                    // Min: 1, Max: 12 minus minimum space for other elements (1 each)
                    const maxColspan = 12 - otherCount;
                    newColspan = Math.max(1, Math.min(maxColspan, newColspan));

                    // Calculate remaining space for other elements
                    const remainingCols = 12 - newColspan;

                    // Distribute remaining space proportionally based on original ratios
                    const originalOtherTotal = this.resizeOtherStartColspans.reduce((a, b) => a + b, 0);

                    // Update current element
                    const data = this.state.get(this.resizeCard.dataset.element);
                    if (data) {
                        data.colspan = String(newColspan);
                        data.manualColspan = true;
                        this.resizeCard.dataset.colspan = String(newColspan);
                        this.updateElementInfo(this.resizeCard);
                    }

                    // Distribute remaining space among other elements proportionally
                    let distributed = 0;
                    this.resizeOtherElements.forEach((card, index) => {
                        const otherData = this.state.get(card.dataset.element);
                        if (otherData) {
                            let newOtherColspan;
                            if (index === otherCount - 1) {
                                // Last element gets the remainder to ensure total = 12
                                newOtherColspan = remainingCols - distributed;
                            } else {
                                // Distribute proportionally based on original ratio
                                const ratio = this.resizeOtherStartColspans[index] / originalOtherTotal;
                                newOtherColspan = Math.max(1, Math.round(remainingCols * ratio));
                                distributed += newOtherColspan;
                            }

                            newOtherColspan = Math.max(1, newOtherColspan);
                            otherData.colspan = String(newOtherColspan);
                            card.dataset.colspan = String(newOtherColspan);
                            this.updateElementInfo(card);
                        }
                    });
                }

                /**
                 * End resize operation
                 */
                endResize() {
                    if (!this.isResizing) { return; }

                    this.isResizing = false;

                    // Remove resizing class from elements
                    if (this.resizeCard) {
                        this.resizeCard.classList.remove('resizing');
                    }
                    if (this.resizeNeighborCard) {
                        this.resizeNeighborCard.classList.remove('resizing');
                    }
                    if (this.resizeOtherElements) {
                        this.resizeOtherElements.forEach(card => card.classList.remove('resizing'));
                    }

                    // Recalculate column positions
                    if (this.resizeRowEl) {
                        this.recalculateColumns(this.resizeRowEl);
                    }

                    // Clean up
                    this.resizeCard = null;
                    this.resizeNeighborCard = null;
                    this.resizeRowEl = null;
                    this.resizeIsOuterEdge = false;
                    this.resizeOtherElements = null;
                    this.resizeOtherStartColspans = null;

                    document.removeEventListener('mousemove', this.handleResize);
                    document.removeEventListener('mouseup', this.endResize);
                }

                // =====================================================================
                // Keyboard Navigation
                // =====================================================================

                /**
                 * Handle keyboard events
                 * @param {KeyboardEvent} e - Keyboard event
                 */
                handleKeyboard(e) {
                    // Check if we're in an input field
                    if (e.target.matches('input, textarea, select')) { return; }

                    // Undo: Ctrl+Z
                    if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
                        e.preventDefault();
                        this.undo();
                        return;
                    }

                    // Redo: Ctrl+Y or Ctrl+Shift+Z
                    if ((e.ctrlKey && e.key === 'y') || (e.ctrlKey && e.shiftKey && e.key === 'z')) {
                        e.preventDefault();
                        this.redo();
                        return;
                    }

                    // Open view settings: Ctrl+Shift+S
                    if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        this.openViewSettings();
                        return;
                    }

                    // Delete: Delete or Backspace on focused element
                    if ((e.key === 'Delete' || e.key === 'Backspace') && document.activeElement?.classList.contains('element-card')) {
                        const card = document.activeElement;
                        if (card.closest('.row-elements')) {
                            e.preventDefault();
                            this.saveStateForUndo();
                            this.removeElement(card);
                        }
                        return;
                    }

                    // Arrow keys for navigation
                    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                        const focused = document.activeElement;
                        if (focused?.classList.contains('element-card') && focused.closest('.row-elements')) {
                            e.preventDefault();
                            this.navigateElements(focused, e.key);
                        }
                    }
                }

                /**
                 * Navigate between elements with arrow keys
                 * @param {HTMLElement} current - Currently focused element
                 * @param {string} direction - Arrow key direction
                 */
                navigateElements(current, direction) {
                    const rowEl = current.closest('.row-elements');
                    const allRows = Array.from(this.canvas.querySelectorAll('.row-elements'));
                    const rowIndex = allRows.indexOf(rowEl);

                    let targetCard = null;

                    switch (direction) {
                        case 'ArrowLeft': {
                            targetCard = current.previousElementSibling;
                            break;
                        }
                        case 'ArrowRight': {
                            targetCard = current.nextElementSibling;
                            break;
                        }
                        case 'ArrowUp': {
                            if (rowIndex > 0) {
                                const prevRow = allRows[rowIndex - 1];
                                const cards = prevRow.querySelectorAll('.element-card');
                                if (cards.length > 0) {
                                    targetCard = cards[0];
                                }
                            }
                            break;
                        }
                        case 'ArrowDown': {
                            if (rowIndex < allRows.length - 1) {
                                const nextRow = allRows[rowIndex + 1];
                                const cards = nextRow.querySelectorAll('.element-card');
                                if (cards.length > 0) {
                                    targetCard = cards[0];
                                }
                            }
                            break;
                        }
                    }

                    if (targetCard?.classList.contains('element-card')) {
                        targetCard.focus();
                    }
                }
            }

            // Export to global scope
        window.LayoutEditor = LayoutEditor;

        /**
         * Initialize the layout editor
         */
        function initLayoutEditor() {
            const container = document.getElementById('layout-editor-container');
            if (container && !container.dataset.initialized) {
                container.dataset.initialized = 'true';

                // Get initial context from data attribute or default to 'messages'
                const initialContext = container.dataset.context || 'messages';

                window.proclaimLayoutEditor = new LayoutEditor(container, {
                    context: initialContext
                });
            }
        }

        // Auto-initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLayoutEditor);
        } else {
            // DOM is already ready
            initLayoutEditor();
        }
    })();

})();
