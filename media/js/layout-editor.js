(function () {
    'use strict';

    /**
     * Layout Editor for Proclaim Templates
     *
     * @package    Proclaim.Admin
     * @copyright  (C) 2026 CWM Team All rights reserved
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     */

    (function () {

        /**
         * Display elements configuration by context
         * Each element has: id, label, and language key
         */
        const ELEMENT_DEFINITIONS = {
            // Messages List (no prefix)
            messages: {
                label: 'Messages List',
                elements: [
                    { id: 'scripture1', label: 'Scripture 1', langKey: 'JBS_CMN_SCRIPTURE' },
                    { id: 'scripture2', label: 'Scripture 2', langKey: 'JBS_CMN_SCRIPTURE2' },
                    { id: 'title', label: 'Title', langKey: 'JBS_CMN_TITLE' },
                    { id: 'date', label: 'Date', langKey: 'JBS_CMN_DATE' },
                    { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'teacherimage', label: 'Teacher Image', langKey: 'JBS_CMN_TEACHER_IMAGE' },
                    { id: 'teacher-title', label: 'Teacher Title', langKey: 'JBS_CMN_TEACHER_TITLE' },
                    { id: 'duration', label: 'Duration', langKey: 'JBS_CMN_DURATION' },
                    { id: 'studyintro', label: 'Study Intro', langKey: 'JBS_CMN_STUDYINTRO' },
                    { id: 'series', label: 'Series', langKey: 'JBS_CMN_SERIES' },
                    { id: 'seriesthumbnail', label: 'Series Thumbnail', langKey: 'JBS_CMN_SERIES_THUMBNAIL' },
                    { id: 'seriesdescription', label: 'Series Description', langKey: 'JBS_CMN_SERIES_DESCRIPTION' },
                    { id: 'jbsmedia', label: 'Media', langKey: 'JBS_CMN_MEDIA' },
                    { id: 'topic', label: 'Topics', langKey: 'JBS_CMN_TOPICS' },
                    { id: 'locations', label: 'Locations', langKey: 'JBS_CMN_LOCATIONS' },
                    { id: 'hits', label: 'Hits', langKey: 'JBS_CMN_HITS' },
                    { id: 'downloads', label: 'Downloads', langKey: 'JBS_CMN_DOWNLOADS' },
                    { id: 'studynumber', label: 'Study Number', langKey: 'JBS_CMN_STUDYNUMBER' },
                    { id: 'messagetype', label: 'Message Type', langKey: 'JBS_CMN_MESSAGETYPE' },
                    { id: 'thumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                    { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: ''
            },
            // Study Details (d prefix)
            details: {
                label: 'Study Details',
                elements: [
                    { id: 'scripture1', label: 'Scripture 1', langKey: 'JBS_CMN_SCRIPTURE' },
                    { id: 'scripture2', label: 'Scripture 2', langKey: 'JBS_CMN_SCRIPTURE2' },
                    { id: 'title', label: 'Title', langKey: 'JBS_CMN_TITLE' },
                    { id: 'date', label: 'Date', langKey: 'JBS_CMN_DATE' },
                    { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'teacherimage', label: 'Teacher Image', langKey: 'JBS_CMN_TEACHER_IMAGE' },
                    { id: 'teacher-title', label: 'Teacher Title', langKey: 'JBS_CMN_TEACHER_TITLE' },
                    { id: 'duration', label: 'Duration', langKey: 'JBS_CMN_DURATION' },
                    { id: 'studyintro', label: 'Study Intro', langKey: 'JBS_CMN_STUDYINTRO' },
                    { id: 'series', label: 'Series', langKey: 'JBS_CMN_SERIES' },
                    { id: 'seriesthumbnail', label: 'Series Thumbnail', langKey: 'JBS_CMN_SERIES_THUMBNAIL' },
                    { id: 'seriesdescription', label: 'Series Description', langKey: 'JBS_CMN_SERIES_DESCRIPTION' },
                    { id: 'jbsmedia', label: 'Media', langKey: 'JBS_CMN_MEDIA' },
                    { id: 'topic', label: 'Topics', langKey: 'JBS_CMN_TOPICS' },
                    { id: 'locations', label: 'Locations', langKey: 'JBS_CMN_LOCATIONS' },
                    { id: 'hits', label: 'Hits', langKey: 'JBS_CMN_HITS' },
                    { id: 'downloads', label: 'Downloads', langKey: 'JBS_CMN_DOWNLOADS' },
                    { id: 'studynumber', label: 'Study Number', langKey: 'JBS_CMN_STUDYNUMBER' },
                    { id: 'messagetype', label: 'Message Type', langKey: 'JBS_CMN_MESSAGETYPE' },
                    { id: 'thumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                    { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: 'd'
            },
            // Teachers List (ts prefix)
            teachers: {
                label: 'Teachers List',
                elements: [
                    { id: 'teacher', label: 'Teacher Name', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'teacherimage', label: 'Teacher Image', langKey: 'JBS_CMN_TEACHER_IMAGE' },
                    { id: 'teacher-title', label: 'Teacher Title', langKey: 'JBS_CMN_TEACHER_TITLE' },
                    { id: 'teacheremail', label: 'Email', langKey: 'JBS_CMN_EMAIL' },
                    { id: 'teacherweb', label: 'Website', langKey: 'JBS_CMN_WEBSITE' },
                    { id: 'teacherphone', label: 'Phone', langKey: 'JBS_CMN_PHONE' },
                    { id: 'teacherfb', label: 'Facebook', langKey: 'JBS_CMN_FACEBOOK' },
                    { id: 'teachertw', label: 'Twitter', langKey: 'JBS_CMN_TWITTER' },
                    { id: 'teacherblog', label: 'Blog', langKey: 'JBS_CMN_BLOG' },
                    { id: 'teachershort', label: 'Short Bio', langKey: 'JBS_CMN_SHORTBIO' },
                    { id: 'teacherallinone', label: 'All in One', langKey: 'JBS_CMN_ALLINONE' },
                    { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: 'ts'
            },
            // Teacher Details (td prefix)
            teacherDetails: {
                label: 'Teacher Details',
                elements: [
                    { id: 'teacher', label: 'Teacher Name', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'teacherimage', label: 'Teacher Image', langKey: 'JBS_CMN_TEACHER_IMAGE' },
                    { id: 'teacher-title', label: 'Teacher Title', langKey: 'JBS_CMN_TEACHER_TITLE' },
                    { id: 'teacheremail', label: 'Email', langKey: 'JBS_CMN_EMAIL' },
                    { id: 'teacherweb', label: 'Website', langKey: 'JBS_CMN_WEBSITE' },
                    { id: 'teacherphone', label: 'Phone', langKey: 'JBS_CMN_PHONE' },
                    { id: 'teacherfb', label: 'Facebook', langKey: 'JBS_CMN_FACEBOOK' },
                    { id: 'teachertw', label: 'Twitter', langKey: 'JBS_CMN_TWITTER' },
                    { id: 'teacherblog', label: 'Blog', langKey: 'JBS_CMN_BLOG' },
                    { id: 'teachershort', label: 'Short Bio', langKey: 'JBS_CMN_SHORTBIO' },
                    { id: 'teacherlong', label: 'Full Bio', langKey: 'JBS_CMN_LONGBIO' },
                    { id: 'teacherlargeimage', label: 'Large Image', langKey: 'JBS_CMN_LARGEIMAGE' },
                    { id: 'teacherallinone', label: 'All in One', langKey: 'JBS_CMN_ALLINONE' },
                    { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: 'td'
            },
            // Series List (s prefix)
            series: {
                label: 'Series List',
                elements: [
                    { id: 'series', label: 'Series Title', langKey: 'JBS_CMN_TITLE' },
                    { id: 'description', label: 'Description', langKey: 'JBS_CMN_DESCRIPTION' },
                    { id: 'seriesthumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                    { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'dcustom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: 's'
            },
            // Series Details (sd prefix)
            seriesDetails: {
                label: 'Series Details',
                elements: [
                    { id: 'series', label: 'Series Title', langKey: 'JBS_CMN_TITLE' },
                    { id: 'description', label: 'Description', langKey: 'JBS_CMN_DESCRIPTION' },
                    { id: 'seriesthumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                    { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                    { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
                ],
                prefix: 'sd'
            }
        };

        /**
         * Link type options (matches LinkOptionsField.php)
         */
        const LINK_TYPES = [
            { value: '0', labelKey: 'JBS_TPL_NO_LINK', label: 'No Link' },
            { value: '1', labelKey: 'JBS_TPL_LINK_TO_DETAILS', label: 'Link to Details' },
            { value: '4', labelKey: 'JBS_TPL_LINK_TO_DETAILS_TOOLTIP', label: 'Link to Details (Tooltip)' },
            { value: '2', labelKey: 'JBS_TPL_LINK_TO_MEDIA', label: 'Link to Media' },
            { value: '9', labelKey: 'JBS_TPL_LINK_TO_DOWNLOAD', label: 'Link to Download' },
            { value: '5', labelKey: 'JBS_TPL_LINK_TO_MEDIA_TOOLTIP', label: 'Link to Media (Tooltip)' },
            { value: '3', labelKey: 'JBS_TPL_LINK_TO_TEACHERS_PROFILE', label: 'Link to Teacher\'s Profile' },
            { value: '6', labelKey: 'JBS_TPL_LINK_TO_FIRST_ARTICLE', label: 'Link to First Article' },
            { value: '7', labelKey: 'JBS_TPL_LINK_TO_VIRTUEMART', label: 'Link to VirtueMart' },
            { value: '8', labelKey: 'JBS_TPL_LINK_TO_DOCMAN', label: 'Link to DocMan' }
        ];

        /**
         * Element type options (matches ElementOptionsField.php)
         */
        const ELEMENT_TYPES = [
            { value: '0', labelKey: 'JBS_CMN_NONE', label: 'None' },
            { value: '1', labelKey: 'JBS_TPL_PARAGRAPH', label: 'Paragraph' },
            { value: '2', labelKey: 'JBS_TPL_HEADER1', label: 'Header 1' },
            { value: '3', labelKey: 'JBS_TPL_HEADER2', label: 'Header 2' },
            { value: '4', labelKey: 'JBS_TPL_HEADER3', label: 'Header 3' },
            { value: '5', labelKey: 'JBS_TPL_HEADER4', label: 'Header 4' },
            { value: '6', labelKey: 'JBS_TPL_HEADER5', label: 'Header 5' },
            { value: '7', labelKey: 'JBS_TPL_BLOCKQUOTE', label: 'Blockquote' }
        ];

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
                    numRows: 6,
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

                // Language strings helper
                this.trans = (key) => {
                    if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
                        return Joomla.Text._(key);
                    }
                    return key;
                };

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
                        <button type="button" class="btn btn-primary btn-view-visual" title="Visual Editor">
                            <span class="icon-image" aria-hidden="true"></span>
                            <span class="btn-text">Visual</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-view-classic" title="Classic View">
                            <span class="icon-list" aria-hidden="true"></span>
                            <span class="btn-text">Classic</span>
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
                <div class="layout-classic" style="display: none;"></div>
            `;

                this.sidebar = this.container.querySelector('.layout-sidebar');
                this.palette = this.container.querySelector('.element-palette');
                this.canvas = this.container.querySelector('.layout-canvas');
                this.contextTabs = this.container.querySelector('.layout-context-tabs');
                this.toolbar = this.container.querySelector('.layout-toolbar');
                this.editor = this.container.querySelector('.layout-editor');
                this.classicView = this.container.querySelector('.layout-classic');

                // Current view mode
                this.viewMode = 'visual';

                // Create context tabs
                this.createContextTabs();

                // Create settings modal
                this.createSettingsModal();
            }

            /**
             * Create context selector tabs
             */
            createContextTabs() {
                const contexts = [
                    { id: 'messages', label: this.trans('JBS_TPL_MESSAGES_LIST') || 'Messages List' },
                    { id: 'details', label: this.trans('JBS_TPL_STUDY_DETAILS') || 'Study Details' },
                    { id: 'teachers', label: this.trans('JBS_TPL_TEACHERS_LIST') || 'Teachers List' },
                    { id: 'teacherDetails', label: this.trans('JBS_TPL_TEACHER_DETAILS') || 'Teacher Details' },
                    { id: 'series', label: this.trans('JBS_TPL_SERIES_LIST') || 'Series List' },
                    { id: 'seriesDetails', label: this.trans('JBS_TPL_SERIES_DETAILS') || 'Series Details' }
                ];

                contexts.forEach(ctx => {
                    const tab = document.createElement('button');
                    tab.type = 'button';
                    const isActive = ctx.id === this.currentContext;
                    tab.className = 'btn layout-context-tab ' + (isActive ? 'btn-primary' : 'btn-secondary');
                    tab.dataset.context = ctx.id;
                    tab.textContent = ctx.label;
                    tab.addEventListener('click', () => this.switchContext(ctx.id));
                    this.contextTabs.appendChild(tab);
                });
            }

            /**
             * Switch to a different context
             * @param {string} context - The context to switch to
             */
            switchContext(context) {
                if (context === this.currentContext) return;

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

                // Reinitialize for new context
                this.initSidebar();
                this.initCanvas();
                this.loadFromParams();
                this.initSortable();
            }

            /**
             * Initialize the sidebar with available elements
             */
            initSidebar() {
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
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
             * Initialize the canvas with rows
             */
            initCanvas() {
                this.canvas.innerHTML = '';

                for (let row = 1; row <= this.options.numRows; row++) {
                    const rowEl = document.createElement('div');
                    rowEl.className = 'layout-row';
                    rowEl.dataset.row = row;

                    rowEl.innerHTML = `
                    <div class="row-label">${this.trans('JBS_TPL_ROW') || 'Row'} ${row}</div>
                    <div class="row-elements" data-row="${row}" data-empty-text="${this.trans('JBS_TPL_DROP_ELEMENTS_HERE') || 'Drop elements here'}"></div>
                `;

                    this.canvas.appendChild(rowEl);
                }
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
                                    ${ELEMENT_TYPES.map(opt => `<option value="${opt.value}">${this.trans(opt.labelKey) || opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_ELEMENT_DESC') || 'HTML element type to wrap this content'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-link-type">${this.trans('JBS_TPL_TYPE_OF_LINK') || 'Link Type'}</label>
                                <select class="form-select" id="layout-link-type">
                                    ${LINK_TYPES.map(opt => `<option value="${opt.value}">${this.trans(opt.labelKey) || opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_TYPE_OF_LINK_DESC') || 'How this element should link to the detail view'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-custom-class">${this.trans('JBS_TPL_CUSTOMCLASS') || 'Custom CSS Class'}</label>
                                <input type="text" class="form-control" id="layout-custom-class" placeholder="e.g., my-custom-class">
                                <div class="form-text">${this.trans('JBS_TPL_CUSTOMCLASS_DESC') || 'Additional CSS class to apply to this element'}</div>
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
                    const sortable = Sortable.create(rowEl, {
                        group: 'elements',
                        animation: 80, // Faster animation for snappier feel
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.element-handle',
                        delay: 0,
                        delayOnTouchOnly: true,
                        touchStartThreshold: 3,
                        onStart: function () {
                            // Save state for undo when drag starts
                            self.saveStateForUndo();
                        },
                        onAdd: function (evt) {
                            self.onElementAdded(evt);
                            // Add drop animation
                            evt.item.classList.add('just-dropped');
                            setTimeout(() => evt.item.classList.remove('just-dropped'), 300);
                        },
                        onUpdate: function (evt) {
                            self.onElementMoved(evt);
                        },
                        onRemove: function (evt) {
                            self.onElementRemoved(evt);
                        }
                    });
                    this.sortableInstances.push(sortable);
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
                this.sortableInstances.forEach(instance => instance.destroy());
                this.sortableInstances = [];
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
                if (count === 0) return;

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

                if (!data) return;

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
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
                if (!contextDef) return null;
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
                        const triggerElement = this.currentSettingsElement
                            ? this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"] .btn-settings`)
                            : null;
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

                // Form submit - sync state to form fields
                const form = document.getElementById(this.options.formId);
                if (form) {
                    form.addEventListener('submit', () => this.syncToForm());
                }

                // Also sync before Joomla toolbar actions
                document.addEventListener('click', (e) => {
                    const toolbarBtn = e.target.closest('.button-apply, .button-save, .button-save-new, .button-save-copy');
                    if (toolbarBtn) {
                        this.syncToForm();
                    }
                });

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

                    // View toggle buttons
                    const visualBtn = this.toolbar.querySelector('.btn-view-visual');
                    const classicBtn = this.toolbar.querySelector('.btn-view-classic');
                    if (visualBtn) {
                        visualBtn.addEventListener('click', () => this.switchView('visual'));
                    }
                    if (classicBtn) {
                        classicBtn.addEventListener('click', () => this.switchView('classic'));
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
                        linktype: '0'
                    };
                    this.state.set(elementId, data);
                }

                // Update modal title to show element name
                const elementDef = this.getElementDefinition(elementId);
                const modalTitle = document.getElementById('layoutSettingsModalLabel');
                if (modalTitle && elementDef) {
                    const elementLabel = this.trans(elementDef.langKey) || elementDef.label;
                    modalTitle.textContent = `${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings'}: ${elementLabel}`;
                }

                // Populate modal fields
                const colspanEl = document.getElementById('layout-colspan');
                const elementTypeEl = document.getElementById('layout-element-type');
                const linkTypeEl = document.getElementById('layout-link-type');
                const customClassEl = document.getElementById('layout-custom-class');

                if (colspanEl) colspanEl.value = String(data.colspan) || '1';
                if (elementTypeEl) elementTypeEl.value = String(data.element) || '1';
                if (linkTypeEl) linkTypeEl.value = String(data.linktype) || '0';
                if (customClassEl) customClassEl.value = data.custom || '';

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
                if (!this.currentSettingsElement) return;

                const data = this.state.get(this.currentSettingsElement);
                if (!data) return;

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
                // Close modal - global event listeners in bindEvents() handle focus and inert
                const modalInstance = this.getModalInstance();
                if (modalInstance) {
                    modalInstance.hide();
                } else if (this.modal) {
                    // Fallback: manually hide modal
                    // Move focus out first
                    const triggerElement = this.currentSettingsElement
                        ? this.canvas.querySelector(`.element-card[data-element="${this.currentSettingsElement}"] .btn-settings`)
                        : null;
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
             * Load state from existing form params
             */
            loadFromParams() {
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
                if (!contextDef) return;

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

                    // Try form fields as fallback (in case they're loaded)
                    const rowField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}row]"]`);
                    if (rowField) {
                        row = parseInt(rowField.value, 10) || row;
                        const colField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}col]"]`);
                        const colspanField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}colspan]"]`);
                        const elementField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}element]"]`);
                        const customField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}custom]"]`);
                        const linktypeField = document.querySelector(`[name="${this.options.paramsPrefix}[${fieldPrefix}linktype]"]`);

                        if (colField) col = parseInt(colField.value, 10) || col;
                        if (colspanField) colspan = colspanField.value || colspan;
                        if (elementField) elementType = elementField.value || elementType;
                        if (customField) custom = customField.value || custom;
                        if (linktypeField) linktype = linktypeField.value || linktype;
                    }

                    // Only add to canvas if row > 0 (element is visible)
                    if (row > 0) {
                        const data = {
                            row: row,
                            col: col,
                            colspan: colspan,
                            element: elementType,
                            custom: custom,
                            linktype: linktype
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
                const rowEl = this.canvas.querySelector(`.row-elements[data-row="${data.row}"]`);
                if (!rowEl) return;

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
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
                if (!contextDef) return;

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
                        linktype: `${this.options.paramsPrefix}[${fieldPrefix}linktype]`
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
                if (this.undoStack.length === 0) return;

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
                if (this.redoStack.length === 0) return;

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
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
                if (!contextDef) return;

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
            // View Toggle (Visual/Classic)
            // =====================================================================

            /**
             * Switch between visual and classic view modes
             * @param {string} mode - 'visual' or 'classic'
             */
            switchView(mode) {
                if (this.viewMode === mode) return;

                this.viewMode = mode;

                // Update button states - swap primary/secondary classes
                const visualBtn = this.toolbar?.querySelector('.btn-view-visual');
                const classicBtn = this.toolbar?.querySelector('.btn-view-classic');
                if (visualBtn) {
                    visualBtn.classList.toggle('btn-primary', mode === 'visual');
                    visualBtn.classList.toggle('btn-secondary', mode !== 'visual');
                }
                if (classicBtn) {
                    classicBtn.classList.toggle('btn-primary', mode === 'classic');
                    classicBtn.classList.toggle('btn-secondary', mode !== 'classic');
                }

                // Toggle views
                if (mode === 'visual') {
                    if (this.editor) this.editor.style.display = '';
                    if (this.classicView) this.classicView.style.display = 'none';
                } else {
                    if (this.editor) this.editor.style.display = 'none';
                    if (this.classicView) {
                        this.classicView.style.display = '';
                        this.renderClassicView();
                    }
                }
            }

            /**
             * Render classic view with element list
             */
            renderClassicView() {
                const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
                if (!contextDef || !this.classicView) return;

                // Get elements in layout (row > 0) sorted by row then col
                const placedElements = [];
                this.state.forEach((data, elementId) => {
                    if (data.row > 0) {
                        const element = contextDef.elements.find(el => el.id === elementId);
                        if (element) {
                            placedElements.push({ element, data });
                        }
                    }
                });

                placedElements.sort((a, b) => {
                    if (a.data.row !== b.data.row) return a.data.row - b.data.row;
                    return a.data.col - b.data.col;
                });

                // Render as a simple table
                let html = `
                <div class="classic-view-container">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Element</th>
                                <th>Row</th>
                                <th>Col</th>
                                <th>Colspan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

                if (placedElements.length === 0) {
                    html += `
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No elements in layout. Switch to Visual view to add elements.
                        </td>
                    </tr>
                `;
                } else {
                    placedElements.forEach(({ element, data }) => {
                        html += `
                        <tr data-element="${element.id}">
                            <td><strong>${element.label}</strong></td>
                            <td>${data.row}</td>
                            <td>${data.col}</td>
                            <td>${data.colspan}</td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-classic-edit" data-element="${element.id}">
                                    <span class="icon-options" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-danger btn-classic-remove" data-element="${element.id}">
                                    <span class="icon-cancel" aria-hidden="true"></span>
                                </button>
                            </td>
                        </tr>
                    `;
                    });
                }

                html += `
                        </tbody>
                    </table>
                </div>
            `;

                this.classicView.innerHTML = html;

                // Bind classic view events
                this.classicView.querySelectorAll('.btn-classic-edit').forEach(btn => {
                    btn.addEventListener('click', () => this.openSettings(btn.dataset.element));
                });

                this.classicView.querySelectorAll('.btn-classic-remove').forEach(btn => {
                    btn.addEventListener('click', () => {
                        this.saveStateForUndo();
                        const elementId = btn.dataset.element;
                        this.state.delete(elementId);
                        this.rebuildCanvas();
                        this.renderClassicView();
                    });
                });
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
                if (card.dataset.paletteItem !== undefined) return;

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
                if (!rowEl) return;

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
                if (isOuterEdge && children.length < 2) return;

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
            handleResize = (e) => {
                if (!this.isResizing || !this.resizeCard) return;

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
                if (otherCount === 0) return;

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
            endResize = () => {
                if (!this.isResizing) return;

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
                if (e.target.matches('input, textarea, select')) return;

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
