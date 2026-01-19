/**
 * Layout Editor for Proclaim Templates
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function () {
    'use strict';

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
                { id: 'title', label: 'Series Title', langKey: 'JBS_CMN_TITLE' },
                { id: 'description', label: 'Description', langKey: 'JBS_CMN_DESCRIPTION' },
                { id: 'thumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
            ],
            prefix: 's'
        },
        // Series Details (sd prefix)
        seriesDetails: {
            label: 'Series Details',
            elements: [
                { id: 'title', label: 'Series Title', langKey: 'JBS_CMN_TITLE' },
                { id: 'description', label: 'Description', langKey: 'JBS_CMN_DESCRIPTION' },
                { id: 'thumbnail', label: 'Thumbnail', langKey: 'JBS_CMN_THUMBNAIL' },
                { id: 'teacher', label: 'Teacher', langKey: 'JBS_CMN_TEACHER' },
                { id: 'custom', label: 'Custom', langKey: 'JBS_CMN_CUSTOM' }
            ],
            prefix: 'sd'
        }
    };

    /**
     * Link type options
     */
    const LINK_TYPES = [
        { value: '0', label: 'No Link' },
        { value: '1', label: 'Link to Details' },
        { value: '2', label: 'Link in Popup' },
        { value: '3', label: 'Link in Lightbox' }
    ];

    /**
     * Element type options
     */
    const ELEMENT_TYPES = [
        { value: '0', label: 'Hidden' },
        { value: '1', label: 'Paragraph' },
        { value: '2', label: 'Div' },
        { value: '3', label: 'Span' },
        { value: '4', label: 'H1' },
        { value: '5', label: 'H2' },
        { value: '6', label: 'H3' },
        { value: '7', label: 'H4' },
        { value: '8', label: 'H5' }
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
                <div class="layout-help">
                    <span class="icon-info-circle" aria-hidden="true"></span>
                    ${this.trans('JBS_TPL_LAYOUT_HELP') || 'Drag elements from the sidebar onto rows to arrange your layout. Click the gear icon to configure element settings.'}
                </div>
                <div class="layout-context-tabs"></div>
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
                tab.className = 'layout-context-tab' + (ctx.id === this.currentContext ? ' active' : '');
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

            // Update active tab
            this.contextTabs.querySelectorAll('.layout-context-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.context === context);
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
            modal.setAttribute('aria-hidden', 'true');

            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="layoutSettingsModalLabel">${this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings'}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${this.trans('JCLOSE') || 'Close'}"></button>
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
                                    ${ELEMENT_TYPES.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
                                </select>
                                <div class="form-text">${this.trans('JBS_TPL_ELEMENT_DESC') || 'HTML element type to wrap this content'}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="layout-link-type">${this.trans('JBS_TPL_TYPE_OF_LINK') || 'Link Type'}</label>
                                <select class="form-select" id="layout-link-type">
                                    ${LINK_TYPES.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('')}
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${this.trans('JCANCEL') || 'Cancel'}</button>
                            <button type="button" class="btn btn-primary" id="layout-settings-save">${this.trans('JAPPLY') || 'Apply'}</button>
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
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onClone: function (evt) {
                        // Transform clone into a full element card
                        const clone = evt.clone;
                        const elementId = clone.dataset.element;
                        const element = self.getElementDefinition(elementId);

                        if (element) {
                            clone.dataset.paletteItem = '';
                            clone.innerHTML = `
                                <span class="element-handle"><span class="icon-menu" aria-hidden="true"></span></span>
                                <span class="element-name">${element.label}</span>
                                <span class="element-info">Col 1</span>
                                <button type="button" class="btn-settings" title="${self.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings'}">
                                    <span class="icon-options" aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn-remove" title="${self.trans('JBS_TPL_REMOVE_ELEMENT') || 'Remove'}">
                                    <span class="icon-cancel" aria-hidden="true"></span>
                                </button>
                            `;
                        }
                    }
                });
            }

            // Initialize sortable for each row
            this.canvas.querySelectorAll('.row-elements').forEach(rowEl => {
                const sortable = Sortable.create(rowEl, {
                    group: 'elements',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    handle: '.element-handle',
                    onAdd: function (evt) {
                        self.onElementAdded(evt);
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
            }

            this.updateElementInfo(evt.item);
            this.recalculateColumns(evt.to);
        }

        /**
         * Handle element moved within or between rows
         * @param {Event} evt - Sortable event
         */
        onElementMoved(evt) {
            this.recalculateColumns(evt.to);
            if (evt.from !== evt.to) {
                this.recalculateColumns(evt.from);
            }
        }

        /**
         * Handle element removed from a row
         * @param {Event} evt - Sortable event
         */
        onElementRemoved(evt) {
            this.recalculateColumns(evt.from);
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
                    col += parseInt(data.colspan, 10) || 1;
                }

                this.updateElementInfo(card);
            });
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
                        this.removeElement(card);
                    }
                }
            });

            // Settings modal save
            const saveBtn = document.getElementById('layout-settings-save');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.saveSettings());
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

            // Recalculate columns
            if (rowEl) {
                this.recalculateColumns(rowEl);
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
                modalInstance.show();
            } else {
                // Fallback: manually show modal with CSS classes
                if (this.modal) {
                    this.modal.classList.add('show');
                    this.modal.style.display = 'block';
                    this.modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');

                    // Create backdrop
                    let backdrop = document.querySelector('.modal-backdrop');
                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
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
            data.colspan = document.getElementById('layout-colspan').value;
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

            // Close modal
            const modalInstance = this.getModalInstance();
            if (modalInstance) {
                modalInstance.hide();
            } else if (this.modal) {
                // Fallback: manually hide modal
                this.modal.classList.remove('show');
                this.modal.style.display = 'none';
                this.modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');

                // Remove backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }

            this.currentSettingsElement = null;
        }

        /**
         * Load state from existing form params
         */
        loadFromParams() {
            const contextDef = ELEMENT_DEFINITIONS[this.currentContext];
            if (!contextDef) return;

            const prefix = contextDef.prefix;

            // Get params from Joomla script options (passed from PHP)
            const templateParams = Joomla.getOptions('com_proclaim.templateParams') || {};

            contextDef.elements.forEach(element => {
                const fieldPrefix = prefix + element.id;

                // Read from script options first, fall back to form fields
                let row = parseInt(templateParams[fieldPrefix + 'row'], 10) || 0;
                let col = parseInt(templateParams[fieldPrefix + 'col'], 10) || 1;
                let colspan = templateParams[fieldPrefix + 'colspan'] || '1';
                let elementType = templateParams[fieldPrefix + 'element'] || '1';
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
            rowEl.appendChild(card);

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
