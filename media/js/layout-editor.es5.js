(function () {
  'use strict';

  function _classCallCheck(a, n) {
    if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
  }
  function _defineProperties(e, r) {
    for (var t = 0; t < r.length; t++) {
      var o = r[t];
      o.enumerable = o.enumerable || false, o.configurable = true, "value" in o && (o.writable = true), Object.defineProperty(e, _toPropertyKey(o.key), o);
    }
  }
  function _createClass(e, r, t) {
    return r && _defineProperties(e.prototype, r), Object.defineProperty(e, "prototype", {
      writable: false
    }), e;
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function ownKeys(e, r) {
    var t = Object.keys(e);
    if (Object.getOwnPropertySymbols) {
      var o = Object.getOwnPropertySymbols(e);
      r && (o = o.filter(function (r) {
        return Object.getOwnPropertyDescriptor(e, r).enumerable;
      })), t.push.apply(t, o);
    }
    return t;
  }
  function _objectSpread2(e) {
    for (var r = 1; r < arguments.length; r++) {
      var t = null != arguments[r] ? arguments[r] : {};
      r % 2 ? ownKeys(Object(t), true).forEach(function (r) {
        _defineProperty(e, r, t[r]);
      }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
        Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
      });
    }
    return e;
  }
  function _toPrimitive(t, r) {
    if ("object" != typeof t || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != typeof i) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }
  function _toPropertyKey(t) {
    var i = _toPrimitive(t, "string");
    return "symbol" == typeof i ? i : i + "";
  }

  /**
   * Layout Editor for Proclaim Templates
   *
   * @package    Proclaim.Admin
   * @copyright  (C) 2026 CWM Team All rights reserved
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */

  (function () {
    var ELEMENT_DEFINITIONS = {
      messages: {
        label: 'Messages List',
        elements: [{
          id: 'scripture1',
          label: 'Scripture 1',
          langKey: 'JBS_CMN_SCRIPTURE'
        }, {
          id: 'scripture2',
          label: 'Scripture 2',
          langKey: 'JBS_CMN_SCRIPTURE2'
        }, {
          id: 'title',
          label: 'Title',
          langKey: 'JBS_CMN_TITLE'
        }, {
          id: 'date',
          label: 'Date',
          langKey: 'JBS_CMN_DATE'
        }, {
          id: 'teacher',
          label: 'Teacher',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'teacherimage',
          label: 'Teacher Image',
          langKey: 'JBS_CMN_TEACHER_IMAGE'
        }, {
          id: 'teacher-title',
          label: 'Teacher Title',
          langKey: 'JBS_CMN_TEACHER_TITLE'
        }, {
          id: 'duration',
          label: 'Duration',
          langKey: 'JBS_CMN_DURATION'
        }, {
          id: 'studyintro',
          label: 'Study Intro',
          langKey: 'JBS_CMN_STUDYINTRO'
        }, {
          id: 'series',
          label: 'Series',
          langKey: 'JBS_CMN_SERIES'
        }, {
          id: 'seriesthumbnail',
          label: 'Series Thumbnail',
          langKey: 'JBS_CMN_SERIES_THUMBNAIL'
        }, {
          id: 'seriesdescription',
          label: 'Series Description',
          langKey: 'JBS_CMN_SERIES_DESCRIPTION'
        }, {
          id: 'jbsmedia',
          label: 'Media',
          langKey: 'JBS_CMN_MEDIA'
        }, {
          id: 'topic',
          label: 'Topics',
          langKey: 'JBS_CMN_TOPICS'
        }, {
          id: 'locations',
          label: 'Locations',
          langKey: 'JBS_CMN_LOCATIONS'
        }, {
          id: 'hits',
          label: 'Hits',
          langKey: 'JBS_CMN_HITS'
        }, {
          id: 'downloads',
          label: 'Downloads',
          langKey: 'JBS_CMN_DOWNLOADS'
        }, {
          id: 'studynumber',
          label: 'Study Number',
          langKey: 'JBS_CMN_STUDYNUMBER'
        }, {
          id: 'messagetype',
          label: 'Message Type',
          langKey: 'JBS_CMN_MESSAGETYPE'
        }, {
          id: 'thumbnail',
          label: 'Thumbnail',
          langKey: 'JBS_CMN_THUMBNAIL'
        }, {
          id: 'custom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: ''
      },
      details: {
        label: 'Study Details',
        elements: [{
          id: 'scripture1',
          label: 'Scripture 1',
          langKey: 'JBS_CMN_SCRIPTURE'
        }, {
          id: 'scripture2',
          label: 'Scripture 2',
          langKey: 'JBS_CMN_SCRIPTURE2'
        }, {
          id: 'title',
          label: 'Title',
          langKey: 'JBS_CMN_TITLE'
        }, {
          id: 'date',
          label: 'Date',
          langKey: 'JBS_CMN_DATE'
        }, {
          id: 'teacher',
          label: 'Teacher',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'teacherimage',
          label: 'Teacher Image',
          langKey: 'JBS_CMN_TEACHER_IMAGE'
        }, {
          id: 'teacher-title',
          label: 'Teacher Title',
          langKey: 'JBS_CMN_TEACHER_TITLE'
        }, {
          id: 'duration',
          label: 'Duration',
          langKey: 'JBS_CMN_DURATION'
        }, {
          id: 'studyintro',
          label: 'Study Intro',
          langKey: 'JBS_CMN_STUDYINTRO'
        }, {
          id: 'series',
          label: 'Series',
          langKey: 'JBS_CMN_SERIES'
        }, {
          id: 'seriesthumbnail',
          label: 'Series Thumbnail',
          langKey: 'JBS_CMN_SERIES_THUMBNAIL'
        }, {
          id: 'seriesdescription',
          label: 'Series Description',
          langKey: 'JBS_CMN_SERIES_DESCRIPTION'
        }, {
          id: 'jbsmedia',
          label: 'Media',
          langKey: 'JBS_CMN_MEDIA'
        }, {
          id: 'topic',
          label: 'Topics',
          langKey: 'JBS_CMN_TOPICS'
        }, {
          id: 'locations',
          label: 'Locations',
          langKey: 'JBS_CMN_LOCATIONS'
        }, {
          id: 'hits',
          label: 'Hits',
          langKey: 'JBS_CMN_HITS'
        }, {
          id: 'downloads',
          label: 'Downloads',
          langKey: 'JBS_CMN_DOWNLOADS'
        }, {
          id: 'studynumber',
          label: 'Study Number',
          langKey: 'JBS_CMN_STUDYNUMBER'
        }, {
          id: 'messagetype',
          label: 'Message Type',
          langKey: 'JBS_CMN_MESSAGETYPE'
        }, {
          id: 'thumbnail',
          label: 'Thumbnail',
          langKey: 'JBS_CMN_THUMBNAIL'
        }, {
          id: 'custom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: 'd'
      },
      teachers: {
        label: 'Teachers List',
        elements: [{
          id: 'teacher',
          label: 'Teacher Name',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'teacherimage',
          label: 'Teacher Image',
          langKey: 'JBS_CMN_TEACHER_IMAGE'
        }, {
          id: 'teacher-title',
          label: 'Teacher Title',
          langKey: 'JBS_CMN_TEACHER_TITLE'
        }, {
          id: 'teacheremail',
          label: 'Email',
          langKey: 'JBS_CMN_EMAIL'
        }, {
          id: 'teacherweb',
          label: 'Website',
          langKey: 'JBS_CMN_WEBSITE'
        }, {
          id: 'teacherphone',
          label: 'Phone',
          langKey: 'JBS_CMN_PHONE'
        }, {
          id: 'teacherfb',
          label: 'Facebook',
          langKey: 'JBS_CMN_FACEBOOK'
        }, {
          id: 'teachertw',
          label: 'Twitter',
          langKey: 'JBS_CMN_TWITTER'
        }, {
          id: 'teacherblog',
          label: 'Blog',
          langKey: 'JBS_CMN_BLOG'
        }, {
          id: 'teachershort',
          label: 'Short Bio',
          langKey: 'JBS_CMN_SHORTBIO'
        }, {
          id: 'teacherallinone',
          label: 'All in One',
          langKey: 'JBS_CMN_ALLINONE'
        }, {
          id: 'custom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: 'ts'
      },
      teacherDetails: {
        label: 'Teacher Details',
        elements: [{
          id: 'teacher',
          label: 'Teacher Name',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'teacherimage',
          label: 'Teacher Image',
          langKey: 'JBS_CMN_TEACHER_IMAGE'
        }, {
          id: 'teacher-title',
          label: 'Teacher Title',
          langKey: 'JBS_CMN_TEACHER_TITLE'
        }, {
          id: 'teacheremail',
          label: 'Email',
          langKey: 'JBS_CMN_EMAIL'
        }, {
          id: 'teacherweb',
          label: 'Website',
          langKey: 'JBS_CMN_WEBSITE'
        }, {
          id: 'teacherphone',
          label: 'Phone',
          langKey: 'JBS_CMN_PHONE'
        }, {
          id: 'teacherfb',
          label: 'Facebook',
          langKey: 'JBS_CMN_FACEBOOK'
        }, {
          id: 'teachertw',
          label: 'Twitter',
          langKey: 'JBS_CMN_TWITTER'
        }, {
          id: 'teacherblog',
          label: 'Blog',
          langKey: 'JBS_CMN_BLOG'
        }, {
          id: 'teachershort',
          label: 'Short Bio',
          langKey: 'JBS_CMN_SHORTBIO'
        }, {
          id: 'teacherlong',
          label: 'Full Bio',
          langKey: 'JBS_CMN_LONGBIO'
        }, {
          id: 'teacherlargeimage',
          label: 'Large Image',
          langKey: 'JBS_CMN_LARGEIMAGE'
        }, {
          id: 'teacherallinone',
          label: 'All in One',
          langKey: 'JBS_CMN_ALLINONE'
        }, {
          id: 'custom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: 'td'
      },
      series: {
        label: 'Series List',
        elements: [{
          id: 'series',
          label: 'Series Title',
          langKey: 'JBS_CMN_TITLE'
        }, {
          id: 'description',
          label: 'Description',
          langKey: 'JBS_CMN_DESCRIPTION'
        }, {
          id: 'seriesthumbnail',
          label: 'Thumbnail',
          langKey: 'JBS_CMN_THUMBNAIL'
        }, {
          id: 'teacher',
          label: 'Teacher',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'dcustom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: 's'
      },
      seriesDetails: {
        label: 'Series Details',
        elements: [{
          id: 'series',
          label: 'Series Title',
          langKey: 'JBS_CMN_TITLE'
        }, {
          id: 'description',
          label: 'Description',
          langKey: 'JBS_CMN_DESCRIPTION'
        }, {
          id: 'seriesthumbnail',
          label: 'Thumbnail',
          langKey: 'JBS_CMN_THUMBNAIL'
        }, {
          id: 'teacher',
          label: 'Teacher',
          langKey: 'JBS_CMN_TEACHER'
        }, {
          id: 'custom',
          label: 'Custom',
          langKey: 'JBS_CMN_CUSTOM'
        }],
        prefix: 'sd'
      }
    };
    var LINK_TYPES = [{
      value: '0',
      labelKey: 'JBS_TPL_NO_LINK',
      label: 'No Link'
    }, {
      value: '1',
      labelKey: 'JBS_TPL_LINK_TO_DETAILS',
      label: 'Link to Details'
    }, {
      value: '4',
      labelKey: 'JBS_TPL_LINK_TO_DETAILS_TOOLTIP',
      label: 'Link to Details (Tooltip)'
    }, {
      value: '2',
      labelKey: 'JBS_TPL_LINK_TO_MEDIA',
      label: 'Link to Media'
    }, {
      value: '9',
      labelKey: 'JBS_TPL_LINK_TO_DOWNLOAD',
      label: 'Link to Download'
    }, {
      value: '5',
      labelKey: 'JBS_TPL_LINK_TO_MEDIA_TOOLTIP',
      label: 'Link to Media (Tooltip)'
    }, {
      value: '3',
      labelKey: 'JBS_TPL_LINK_TO_TEACHERS_PROFILE',
      label: 'Link to Teacher\'s Profile'
    }, {
      value: '6',
      labelKey: 'JBS_TPL_LINK_TO_FIRST_ARTICLE',
      label: 'Link to First Article'
    }, {
      value: '7',
      labelKey: 'JBS_TPL_LINK_TO_VIRTUEMART',
      label: 'Link to VirtueMart'
    }, {
      value: '8',
      labelKey: 'JBS_TPL_LINK_TO_DOCMAN',
      label: 'Link to DocMan'
    }];
    var ELEMENT_TYPES = [{
      value: '0',
      labelKey: 'JBS_CMN_NONE',
      label: 'None'
    }, {
      value: '1',
      labelKey: 'JBS_TPL_PARAGRAPH',
      label: 'Paragraph'
    }, {
      value: '2',
      labelKey: 'JBS_TPL_HEADER1',
      label: 'Header 1'
    }, {
      value: '3',
      labelKey: 'JBS_TPL_HEADER2',
      label: 'Header 2'
    }, {
      value: '4',
      labelKey: 'JBS_TPL_HEADER3',
      label: 'Header 3'
    }, {
      value: '5',
      labelKey: 'JBS_TPL_HEADER4',
      label: 'Header 4'
    }, {
      value: '6',
      labelKey: 'JBS_TPL_HEADER5',
      label: 'Header 5'
    }, {
      value: '7',
      labelKey: 'JBS_TPL_BLOCKQUOTE',
      label: 'Blockquote'
    }];
    var LayoutEditor = function () {
      function LayoutEditor(container) {
        var _this = this;
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        _classCallCheck(this, LayoutEditor);
        _defineProperty(this, "handleResize", function (e) {
          if (!_this.isResizing || !_this.resizeCard) return;
          var deltaX = e.clientX - _this.resizeStartX;
          var colsDelta = Math.round(deltaX / _this.columnWidth);
          if (_this.resizeDirection === 'left') {
            colsDelta = -colsDelta;
          }
          if (_this.resizeIsOuterEdge) {
            _this.handleOuterEdgeResize(colsDelta);
          } else if (_this.resizeNeighborCard) {
            _this.handleInnerEdgeResize(colsDelta);
          }
        });
        _defineProperty(this, "endResize", function () {
          if (!_this.isResizing) return;
          _this.isResizing = false;
          if (_this.resizeCard) {
            _this.resizeCard.classList.remove('resizing');
          }
          if (_this.resizeNeighborCard) {
            _this.resizeNeighborCard.classList.remove('resizing');
          }
          if (_this.resizeOtherElements) {
            _this.resizeOtherElements.forEach(function (card) {
              return card.classList.remove('resizing');
            });
          }
          if (_this.resizeRowEl) {
            _this.recalculateColumns(_this.resizeRowEl);
          }
          _this.resizeCard = null;
          _this.resizeNeighborCard = null;
          _this.resizeRowEl = null;
          _this.resizeIsOuterEdge = false;
          _this.resizeOtherElements = null;
          _this.resizeOtherStartColspans = null;
          document.removeEventListener('mousemove', _this.handleResize);
          document.removeEventListener('mouseup', _this.endResize);
        });
        this.container = container;
        this.options = _objectSpread2({
          numRows: 6,
          numCols: 12,
          context: 'messages',
          formId: 'item-form',
          paramsPrefix: 'jform[params]'
        }, options);
        this.state = new Map();
        this.currentContext = this.options.context;
        this.sortableInstances = [];
        this.paletteSortable = null;
        this.undoStack = [];
        this.redoStack = [];
        this.maxHistory = 50;
        this.showGrid = false;
        this.isResizing = false;
        this.trans = function (key) {
          if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return Joomla.Text._(key);
          }
          return key;
        };
        this.init();
      }
      return _createClass(LayoutEditor, [{
        key: "init",
        value: function init() {
          this.createStructure();
          this.initSidebar();
          this.initCanvas();
          this.loadFromParams();
          this.initSortable();
          this.bindEvents();
        }
      }, {
        key: "createStructure",
        value: function createStructure() {
          this.container.innerHTML = "\n                <div class=\"layout-help alert alert-info\">\n                    <span class=\"icon-info-circle\" aria-hidden=\"true\"></span>\n                    ".concat(this.trans('JBS_TPL_LAYOUT_HELP') || 'Drag elements from the sidebar onto rows to arrange your layout. Click the gear icon to configure element settings.', "\n                </div>\n                <div class=\"layout-context-tabs\"></div>\n                <div class=\"layout-toolbar\">\n                    <div class=\"layout-toolbar-group\">\n                        <button type=\"button\" class=\"btn btn-secondary btn-undo\" title=\"Undo (Ctrl+Z)\" disabled>\n                            <span class=\"icon-undo\" aria-hidden=\"true\"></span>\n                        </button>\n                        <button type=\"button\" class=\"btn btn-secondary btn-redo\" title=\"Redo (Ctrl+Y)\" disabled>\n                            <span class=\"icon-redo\" aria-hidden=\"true\"></span>\n                        </button>\n                    </div>\n                    <div class=\"layout-toolbar-group\">\n                        <button type=\"button\" class=\"btn btn-secondary btn-grid\" title=\"Toggle Grid\">\n                            <span class=\"icon-grid-view\" aria-hidden=\"true\"></span>\n                            <span class=\"btn-text\">Grid</span>\n                        </button>\n                    </div>\n                    <div class=\"layout-toolbar-group layout-toolbar-spacer\"></div>\n                    <div class=\"layout-toolbar-group\">\n                        <button type=\"button\" class=\"btn btn-primary btn-view-visual\" title=\"Visual Editor\">\n                            <span class=\"icon-image\" aria-hidden=\"true\"></span>\n                            <span class=\"btn-text\">Visual</span>\n                        </button>\n                        <button type=\"button\" class=\"btn btn-secondary btn-view-classic\" title=\"Classic View\">\n                            <span class=\"icon-list\" aria-hidden=\"true\"></span>\n                            <span class=\"btn-text\">Classic</span>\n                        </button>\n                    </div>\n                </div>\n                <div class=\"layout-editor\">\n                    <aside class=\"layout-sidebar\">\n                        <h4>").concat(this.trans('JBS_TPL_AVAILABLE_ELEMENTS') || 'Available Elements', "</h4>\n                        <div class=\"element-palette\"></div>\n                    </aside>\n                    <main class=\"layout-canvas\"></main>\n                </div>\n                <div class=\"layout-classic\" style=\"display: none;\"></div>\n            ");
          this.sidebar = this.container.querySelector('.layout-sidebar');
          this.palette = this.container.querySelector('.element-palette');
          this.canvas = this.container.querySelector('.layout-canvas');
          this.contextTabs = this.container.querySelector('.layout-context-tabs');
          this.toolbar = this.container.querySelector('.layout-toolbar');
          this.editor = this.container.querySelector('.layout-editor');
          this.classicView = this.container.querySelector('.layout-classic');
          this.viewMode = 'visual';
          this.createContextTabs();
          this.createSettingsModal();
        }
      }, {
        key: "createContextTabs",
        value: function createContextTabs() {
          var _this2 = this;
          var contexts = [{
            id: 'messages',
            label: this.trans('JBS_TPL_MESSAGES_LIST') || 'Messages List'
          }, {
            id: 'details',
            label: this.trans('JBS_TPL_STUDY_DETAILS') || 'Study Details'
          }, {
            id: 'teachers',
            label: this.trans('JBS_TPL_TEACHERS_LIST') || 'Teachers List'
          }, {
            id: 'teacherDetails',
            label: this.trans('JBS_TPL_TEACHER_DETAILS') || 'Teacher Details'
          }, {
            id: 'series',
            label: this.trans('JBS_TPL_SERIES_LIST') || 'Series List'
          }, {
            id: 'seriesDetails',
            label: this.trans('JBS_TPL_SERIES_DETAILS') || 'Series Details'
          }];
          contexts.forEach(function (ctx) {
            var tab = document.createElement('button');
            tab.type = 'button';
            var isActive = ctx.id === _this2.currentContext;
            tab.className = 'btn layout-context-tab ' + (isActive ? 'btn-primary' : 'btn-secondary');
            tab.dataset.context = ctx.id;
            tab.textContent = ctx.label;
            tab.addEventListener('click', function () {
              return _this2.switchContext(ctx.id);
            });
            _this2.contextTabs.appendChild(tab);
          });
        }
      }, {
        key: "switchContext",
        value: function switchContext(context) {
          if (context === this.currentContext) return;
          this.syncToForm();
          this.contextTabs.querySelectorAll('.layout-context-tab').forEach(function (tab) {
            var isActive = tab.dataset.context === context;
            tab.classList.toggle('btn-primary', isActive);
            tab.classList.toggle('btn-secondary', !isActive);
          });
          this.currentContext = context;
          this.state.clear();
          this.destroySortables();
          this.initSidebar();
          this.initCanvas();
          this.loadFromParams();
          this.initSortable();
        }
      }, {
        key: "initSidebar",
        value: function initSidebar() {
          var _this3 = this;
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef || !this.palette) {
            return;
          }
          this.palette.innerHTML = '';
          var group = document.createElement('div');
          group.className = 'element-group';
          var groupTitle = document.createElement('div');
          groupTitle.className = 'element-group-title';
          groupTitle.textContent = contextDef.label;
          group.appendChild(groupTitle);
          var paletteItems = document.createElement('div');
          paletteItems.className = 'element-palette-items';
          paletteItems.dataset.sortableGroup = 'elements';
          contextDef.elements.forEach(function (element) {
            var card = _this3.createElementCard(element, true);
            paletteItems.appendChild(card);
          });
          group.appendChild(paletteItems);
          this.palette.appendChild(group);
        }
      }, {
        key: "createElementCard",
        value: function createElementCard(element) {
          var isPalette = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
          var card = document.createElement('div');
          card.className = 'element-card';
          card.dataset.element = element.id;
          if (isPalette) {
            card.dataset.paletteItem = 'true';
          }
          card.innerHTML = "\n                <span class=\"element-handle\"><span class=\"icon-menu\" aria-hidden=\"true\"></span></span>\n                <span class=\"element-name\">".concat(element.label, "</span>\n                ").concat(!isPalette ? "\n                    <span class=\"element-info\">Col 1</span>\n                    <button type=\"button\" class=\"btn-settings\" title=\"".concat(this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings', "\">\n                        <span class=\"icon-options\" aria-hidden=\"true\"></span>\n                    </button>\n                    <button type=\"button\" class=\"btn-remove\" title=\"").concat(this.trans('JBS_TPL_REMOVE_ELEMENT') || 'Remove', "\">\n                        <span class=\"icon-cancel\" aria-hidden=\"true\"></span>\n                    </button>\n                ") : '', "\n            ");
          return card;
        }
      }, {
        key: "initCanvas",
        value: function initCanvas() {
          this.canvas.innerHTML = '';
          for (var row = 1; row <= this.options.numRows; row++) {
            var rowEl = document.createElement('div');
            rowEl.className = 'layout-row';
            rowEl.dataset.row = row;
            rowEl.innerHTML = "\n                    <div class=\"row-label\">".concat(this.trans('JBS_TPL_ROW') || 'Row', " ").concat(row, "</div>\n                    <div class=\"row-elements\" data-row=\"").concat(row, "\" data-empty-text=\"").concat(this.trans('JBS_TPL_DROP_ELEMENTS_HERE') || 'Drop elements here', "\"></div>\n                ");
            this.canvas.appendChild(rowEl);
          }
        }
      }, {
        key: "createSettingsModal",
        value: function createSettingsModal() {
          var _this4 = this;
          if (document.getElementById('layoutSettingsModal')) {
            this.modal = document.getElementById('layoutSettingsModal');
            return;
          }
          var modal = document.createElement('div');
          modal.className = 'modal fade layout-settings-modal';
          modal.id = 'layoutSettingsModal';
          modal.tabIndex = -1;
          modal.setAttribute('aria-labelledby', 'layoutSettingsModalLabel');
          modal.inert = true;
          modal.innerHTML = "\n                <div class=\"modal-dialog\">\n                    <div class=\"modal-content\">\n                        <div class=\"modal-header\">\n                            <h5 class=\"modal-title\" id=\"layoutSettingsModalLabel\">".concat(this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings', "</h5>\n                        </div>\n                        <div class=\"modal-body\">\n                            <div class=\"form-group\">\n                                <label class=\"form-label\" for=\"layout-colspan\">").concat(this.trans('JBS_TPL_COLSPAN') || 'Column Span', "</label>\n                                <select class=\"form-select\" id=\"layout-colspan\">\n                                    ").concat(Array.from({
            length: 12
          }, function (_, i) {
            return "<option value=\"".concat(i + 1, "\">").concat(i + 1, " ").concat(i === 0 ? 'column' : 'columns', "</option>");
          }).join(''), "\n                                </select>\n                                <div class=\"form-text\">").concat(this.trans('JBS_TPL_COLSPAN_DESC') || 'Number of columns this element should span (1-12)', "</div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label class=\"form-label\" for=\"layout-element-type\">").concat(this.trans('JBS_TPL_ELEMENT') || 'Element Type', "</label>\n                                <select class=\"form-select\" id=\"layout-element-type\">\n                                    ").concat(ELEMENT_TYPES.map(function (opt) {
            return "<option value=\"".concat(opt.value, "\">").concat(_this4.trans(opt.labelKey) || opt.label, "</option>");
          }).join(''), "\n                                </select>\n                                <div class=\"form-text\">").concat(this.trans('JBS_TPL_ELEMENT_DESC') || 'HTML element type to wrap this content', "</div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label class=\"form-label\" for=\"layout-link-type\">").concat(this.trans('JBS_TPL_TYPE_OF_LINK') || 'Link Type', "</label>\n                                <select class=\"form-select\" id=\"layout-link-type\">\n                                    ").concat(LINK_TYPES.map(function (opt) {
            return "<option value=\"".concat(opt.value, "\">").concat(_this4.trans(opt.labelKey) || opt.label, "</option>");
          }).join(''), "\n                                </select>\n                                <div class=\"form-text\">").concat(this.trans('JBS_TPL_TYPE_OF_LINK_DESC') || 'How this element should link to the detail view', "</div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label class=\"form-label\" for=\"layout-custom-class\">").concat(this.trans('JBS_TPL_CUSTOMCLASS') || 'Custom CSS Class', "</label>\n                                <input type=\"text\" class=\"form-control\" id=\"layout-custom-class\" placeholder=\"e.g., my-custom-class\">\n                                <div class=\"form-text\">").concat(this.trans('JBS_TPL_CUSTOMCLASS_DESC') || 'Additional CSS class to apply to this element', "</div>\n                            </div>\n                        </div>\n                        <div class=\"modal-footer\">\n                            <button type=\"button\" class=\"btn btn-danger\" data-bs-dismiss=\"modal\">").concat(this.trans('JCANCEL') || 'Cancel', "</button>\n                            <button type=\"button\" class=\"btn btn-success\" id=\"layout-settings-save\">").concat(this.trans('JAPPLY') || 'Apply', "</button>\n                        </div>\n                    </div>\n                </div>\n            ");
          document.body.appendChild(modal);
          this.modal = modal;
          this.bsModal = null;
        }
      }, {
        key: "getModalInstance",
        value: function getModalInstance() {
          if (this.bsModal) {
            return this.bsModal;
          }
          if (!this.modal) {
            return null;
          }
          if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            this.bsModal = new bootstrap.Modal(this.modal);
            return this.bsModal;
          }
          if (typeof bootstrap !== 'undefined' && bootstrap.Modal && bootstrap.Modal.getInstance) {
            this.bsModal = bootstrap.Modal.getInstance(this.modal) || new bootstrap.Modal(this.modal);
            return this.bsModal;
          }
          return null;
        }
      }, {
        key: "initSortable",
        value: function initSortable() {
          var _this5 = this;
          if (typeof Sortable === 'undefined') {
            console.error('Sortable.js is not loaded');
            var errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-danger';
            errorMsg.innerHTML = "\n                    <span class=\"icon-warning\" aria-hidden=\"true\"></span>\n                    ".concat(this.trans('JBS_TPL_SORTABLE_NOT_LOADED') || 'Drag and drop functionality is unavailable. Please refresh the page or contact support.', "\n                ");
            this.container.insertBefore(errorMsg, this.container.firstChild);
            return;
          }
          var self = this;
          var paletteItems = this.palette.querySelector('.element-palette-items');
          if (paletteItems) {
            this.paletteSortable = Sortable.create(paletteItems, {
              group: {
                name: 'elements',
                pull: 'clone',
                put: false
              },
              sort: false,
              animation: 80,
              ghostClass: 'sortable-ghost',
              chosenClass: 'sortable-chosen',
              dragClass: 'sortable-drag',
              delay: 0,
              delayOnTouchOnly: true,
              touchStartThreshold: 3,
              onStart: function onStart() {
                self.saveStateForUndo();
              },
              onClone: function onClone(evt) {
                var clone = evt.clone;
                clone.dataset.paletteItem = 'true';
              }
            });
          }
          this.canvas.querySelectorAll('.row-elements').forEach(function (rowEl) {
            var sortable = Sortable.create(rowEl, {
              group: 'elements',
              animation: 80,
              ghostClass: 'sortable-ghost',
              chosenClass: 'sortable-chosen',
              dragClass: 'sortable-drag',
              handle: '.element-handle',
              delay: 0,
              delayOnTouchOnly: true,
              touchStartThreshold: 3,
              onStart: function onStart() {
                self.saveStateForUndo();
              },
              onAdd: function onAdd(evt) {
                self.onElementAdded(evt);
                evt.item.classList.add('just-dropped');
                setTimeout(function () {
                  return evt.item.classList.remove('just-dropped');
                }, 300);
              },
              onUpdate: function onUpdate(evt) {
                self.onElementMoved(evt);
              },
              onRemove: function onRemove(evt) {
                self.onElementRemoved(evt);
              }
            });
            _this5.sortableInstances.push(sortable);
          });
        }
      }, {
        key: "destroySortables",
        value: function destroySortables() {
          if (this.paletteSortable) {
            this.paletteSortable.destroy();
            this.paletteSortable = null;
          }
          this.sortableInstances.forEach(function (instance) {
            return instance.destroy();
          });
          this.sortableInstances = [];
        }
      }, {
        key: "onElementAdded",
        value: function onElementAdded(evt) {
          var elementId = evt.item.dataset.element;
          var row = parseInt(evt.to.dataset.row, 10);
          var col = this.calculateColumn(evt.to, evt.newIndex);
          if (this.state.has(elementId)) {
            var data = this.state.get(elementId);
            data.row = row;
            data.col = col;
          } else {
            this.state.set(elementId, {
              row: row,
              col: col,
              colspan: '1',
              element: '1',
              custom: '',
              linktype: '0'
            });
          }
          if (evt.item.dataset.paletteItem === 'true' || evt.item.dataset.paletteItem === '') {
            delete evt.item.dataset.paletteItem;
            var element = this.getElementDefinition(elementId);
            if (element) {
              evt.item.innerHTML = "\n                        <span class=\"element-handle\"><span class=\"icon-menu\" aria-hidden=\"true\"></span></span>\n                        <span class=\"element-name\">".concat(element.label, "</span>\n                        <span class=\"element-info\">Col ").concat(col, "</span>\n                        <button type=\"button\" class=\"btn-settings\" title=\"").concat(this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Settings', "\">\n                            <span class=\"icon-options\" aria-hidden=\"true\"></span>\n                        </button>\n                        <button type=\"button\" class=\"btn-remove\" title=\"").concat(this.trans('JBS_TPL_REMOVE_ELEMENT') || 'Remove', "\">\n                            <span class=\"icon-cancel\" aria-hidden=\"true\"></span>\n                        </button>\n                    ");
            }
            evt.item.tabIndex = 0;
            this.addResizeHandles(evt.item);
          }
          this.updateElementInfo(evt.item);
          this.distributeColspans(evt.to);
        }
      }, {
        key: "onElementMoved",
        value: function onElementMoved(evt) {
          this.distributeColspans(evt.to);
          if (evt.from !== evt.to) {
            this.distributeColspans(evt.from);
          }
        }
      }, {
        key: "onElementRemoved",
        value: function onElementRemoved(evt) {
          this.distributeColspans(evt.from);
        }
      }, {
        key: "calculateColumn",
        value: function calculateColumn(rowEl, index) {
          var children = Array.from(rowEl.children).filter(function (el) {
            return !el.classList.contains('sortable-ghost');
          });
          var col = 1;
          for (var i = 0; i < index && i < children.length; i++) {
            var elementId = children[i].dataset.element;
            var data = this.state.get(elementId);
            col += data ? parseInt(data.colspan, 10) || 1 : 1;
          }
          return Math.min(col, this.options.numCols);
        }
      }, {
        key: "recalculateColumns",
        value: function recalculateColumns(rowEl) {
          var _this6 = this;
          var row = parseInt(rowEl.dataset.row, 10);
          var children = Array.from(rowEl.children).filter(function (el) {
            return !el.classList.contains('sortable-ghost');
          });
          var col = 1;
          children.forEach(function (card) {
            var elementId = card.dataset.element;
            var data = _this6.state.get(elementId);
            if (data) {
              data.row = row;
              data.col = col;
              var colspan = parseInt(data.colspan, 10) || 1;
              var remainingCols = _this6.options.numCols - col + 1;
              if (colspan > remainingCols) {
                colspan = remainingCols;
                data.colspan = String(colspan);
                card.dataset.colspan = String(colspan);
              }
              col += colspan;
            }
            _this6.updateElementInfo(card);
          });
        }
      }, {
        key: "distributeColspans",
        value: function distributeColspans(rowEl) {
          var _this7 = this;
          var preserveManual = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
          var children = Array.from(rowEl.children).filter(function (el) {
            return !el.classList.contains('sortable-ghost') && el.classList.contains('element-card');
          });
          var count = children.length;
          if (count === 0) return;
          var totalCols = this.options.numCols;
          if (count === 1) {
            var data = this.state.get(children[0].dataset.element);
            if (data && (!preserveManual || !data.manualColspan)) {
              data.colspan = String(totalCols);
              children[0].dataset.colspan = String(totalCols);
            }
          } else {
            var baseColspan = Math.floor(totalCols / count);
            var remainder = totalCols % count;
            children.forEach(function (card, index) {
              var data = _this7.state.get(card.dataset.element);
              if (data && (!preserveManual || !data.manualColspan)) {
                var colspan = baseColspan + (index < remainder ? 1 : 0);
                data.colspan = String(colspan);
                card.dataset.colspan = String(colspan);
              }
            });
          }
          this.recalculateColumns(rowEl);
        }
      }, {
        key: "updateElementInfo",
        value: function updateElementInfo(card) {
          var elementId = card.dataset.element;
          var data = this.state.get(elementId);
          if (!data) return;
          var infoEl = card.querySelector('.element-info');
          if (infoEl) {
            var colspan = parseInt(data.colspan, 10) || 1;
            var endCol = data.col + colspan - 1;
            infoEl.textContent = colspan > 1 ? "Col ".concat(data.col, "-").concat(endCol) : "Col ".concat(data.col);
          }
          card.dataset.colspan = data.colspan || '1';
        }
      }, {
        key: "getElementDefinition",
        value: function getElementDefinition(elementId) {
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef) return null;
          return contextDef.elements.find(function (el) {
            return el.id === elementId;
          }) || null;
        }
      }, {
        key: "bindEvents",
        value: function bindEvents() {
          var _this8 = this;
          this.container.addEventListener('click', function (e) {
            var settingsBtn = e.target.closest('.btn-settings');
            if (settingsBtn) {
              var card = settingsBtn.closest('.element-card');
              if (card) {
                _this8.openSettings(card.dataset.element);
              }
            }
            var removeBtn = e.target.closest('.btn-remove');
            if (removeBtn) {
              var _card = removeBtn.closest('.element-card');
              if (_card) {
                _this8.saveStateForUndo();
                _this8.removeElement(_card);
              }
            }
          });
          var saveBtn = document.getElementById('layout-settings-save');
          if (saveBtn) {
            saveBtn.addEventListener('click', function () {
              return _this8.saveSettings();
            });
          }
          if (this.modal) {
            var closeBtn = this.modal.querySelector('.btn-close');
            if (closeBtn) {
              closeBtn.addEventListener('click', function () {
                return _this8.closeSettingsModal();
              });
            }
            this.modal.addEventListener('hide.bs.modal', function () {
              var triggerElement = _this8.currentSettingsElement ? _this8.canvas.querySelector(".element-card[data-element=\"".concat(_this8.currentSettingsElement, "\"] .btn-settings")) : null;
              if (triggerElement) {
                triggerElement.focus();
              }
            });
            this.modal.addEventListener('hidden.bs.modal', function () {
              _this8.modal.inert = true;
              _this8.currentSettingsElement = null;
            });
            var observer = new MutationObserver(function (mutations) {
              mutations.forEach(function (mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                  if (_this8.modal.getAttribute('aria-hidden') === 'true') {
                    _this8.modal.removeAttribute('aria-hidden');
                  }
                }
              });
            });
            observer.observe(this.modal, {
              attributes: true,
              attributeFilter: ['aria-hidden']
            });
          }
          var form = document.getElementById(this.options.formId);
          if (form) {
            form.addEventListener('submit', function () {
              return _this8.syncToForm();
            });
          }
          document.addEventListener('click', function (e) {
            var toolbarBtn = e.target.closest('.button-apply, .button-save, .button-save-new, .button-save-copy');
            if (toolbarBtn) {
              _this8.syncToForm();
            }
          });
          if (this.toolbar) {
            var undoBtn = this.toolbar.querySelector('.btn-undo');
            if (undoBtn) {
              undoBtn.addEventListener('click', function () {
                return _this8.undo();
              });
            }
            var redoBtn = this.toolbar.querySelector('.btn-redo');
            if (redoBtn) {
              redoBtn.addEventListener('click', function () {
                return _this8.redo();
              });
            }
            var gridBtn = this.toolbar.querySelector('.btn-grid');
            if (gridBtn) {
              gridBtn.addEventListener('click', function () {
                return _this8.toggleGrid();
              });
            }
            var visualBtn = this.toolbar.querySelector('.btn-view-visual');
            var classicBtn = this.toolbar.querySelector('.btn-view-classic');
            if (visualBtn) {
              visualBtn.addEventListener('click', function () {
                return _this8.switchView('visual');
              });
            }
            if (classicBtn) {
              classicBtn.addEventListener('click', function () {
                return _this8.switchView('classic');
              });
            }
          }
          document.addEventListener('keydown', function (e) {
            return _this8.handleKeyboard(e);
          });
        }
      }, {
        key: "removeElement",
        value: function removeElement(card) {
          var elementId = card.dataset.element;
          var rowEl = card.closest('.row-elements');
          this.state.delete(elementId);
          card.remove();
          if (rowEl) {
            this.distributeColspans(rowEl);
          }
        }
      }, {
        key: "openSettings",
        value: function openSettings(elementId) {
          var _this9 = this;
          this.currentSettingsElement = elementId;
          var data = this.state.get(elementId);
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
          var elementDef = this.getElementDefinition(elementId);
          var modalTitle = document.getElementById('layoutSettingsModalLabel');
          if (modalTitle && elementDef) {
            var elementLabel = this.trans(elementDef.langKey) || elementDef.label;
            modalTitle.textContent = "".concat(this.trans('JBS_TPL_ELEMENT_SETTINGS') || 'Element Settings', ": ").concat(elementLabel);
          }
          var colspanEl = document.getElementById('layout-colspan');
          var elementTypeEl = document.getElementById('layout-element-type');
          var linkTypeEl = document.getElementById('layout-link-type');
          var customClassEl = document.getElementById('layout-custom-class');
          if (colspanEl) colspanEl.value = String(data.colspan) || '1';
          if (elementTypeEl) elementTypeEl.value = String(data.element) || '1';
          if (linkTypeEl) linkTypeEl.value = String(data.linktype) || '0';
          if (customClassEl) customClassEl.value = data.custom || '';
          var modalInstance = this.getModalInstance();
          if (modalInstance) {
            if (this.modal) {
              this.modal.inert = false;
            }
            modalInstance.show();
          } else {
            if (this.modal) {
              this.modal.inert = false;
              this.modal.classList.add('show');
              this.modal.style.display = 'block';
              this.modal.setAttribute('aria-modal', 'true');
              this.modal.setAttribute('role', 'dialog');
              document.body.classList.add('modal-open');
              var backdrop = document.querySelector('.modal-backdrop');
              if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
              }
              requestAnimationFrame(function () {
                var firstFocusable = _this9.modal.querySelector('select, input, button, [href], textarea, [tabindex]:not([tabindex="-1"])');
                if (firstFocusable) {
                  firstFocusable.focus();
                }
              });
            }
          }
        }
      }, {
        key: "saveSettings",
        value: function saveSettings() {
          if (!this.currentSettingsElement) return;
          var data = this.state.get(this.currentSettingsElement);
          if (!data) return;
          var newColspan = parseInt(document.getElementById('layout-colspan').value, 10) || 1;
          newColspan = Math.max(1, Math.min(this.options.numCols, newColspan));
          if (data.colspan !== String(newColspan)) {
            data.manualColspan = true;
          }
          data.colspan = String(newColspan);
          data.element = document.getElementById('layout-element-type').value;
          data.linktype = document.getElementById('layout-link-type').value;
          data.custom = document.getElementById('layout-custom-class').value;
          var card = this.canvas.querySelector(".element-card[data-element=\"".concat(this.currentSettingsElement, "\"]"));
          if (card) {
            this.updateElementInfo(card);
            var rowEl = card.closest('.row-elements');
            if (rowEl) {
              this.recalculateColumns(rowEl);
            }
          }
          var modalInstance = this.getModalInstance();
          if (modalInstance) {
            modalInstance.hide();
          } else if (this.modal) {
            var triggerElement = this.canvas.querySelector(".element-card[data-element=\"".concat(this.currentSettingsElement, "\"] .btn-settings"));
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
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
              backdrop.remove();
            }
            this.currentSettingsElement = null;
          }
        }
      }, {
        key: "closeSettingsModal",
        value: function closeSettingsModal() {
          var modalInstance = this.getModalInstance();
          if (modalInstance) {
            modalInstance.hide();
          } else if (this.modal) {
            var triggerElement = this.currentSettingsElement ? this.canvas.querySelector(".element-card[data-element=\"".concat(this.currentSettingsElement, "\"] .btn-settings")) : null;
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
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
              backdrop.remove();
            }
            this.currentSettingsElement = null;
          }
        }
      }, {
        key: "loadFromParams",
        value: function loadFromParams() {
          var _window$Joomla,
            _window$Joomla$getOpt,
            _this0 = this;
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef) return;
          var prefix = contextDef.prefix;
          var templateParams = ((_window$Joomla = window.Joomla) === null || _window$Joomla === void 0 || (_window$Joomla$getOpt = _window$Joomla.getOptions) === null || _window$Joomla$getOpt === void 0 ? void 0 : _window$Joomla$getOpt.call(_window$Joomla, 'com_proclaim.templateParams')) || {};
          contextDef.elements.forEach(function (element) {
            var fieldPrefix = prefix + element.id;
            var row = parseInt(templateParams[fieldPrefix + 'row'], 10) || 0;
            var col = parseInt(templateParams[fieldPrefix + 'col'], 10) || 1;
            var colspan = templateParams[fieldPrefix + 'colspan'] || '1';
            var elementType = templateParams[fieldPrefix + 'element'];
            if (elementType === undefined || elementType === null || elementType === '') {
              elementType = '1';
            }
            var custom = templateParams[fieldPrefix + 'custom'] || '';
            var linktype = templateParams[fieldPrefix + 'linktype'] || '0';
            var rowField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "row]\"]"));
            if (rowField) {
              row = parseInt(rowField.value, 10) || row;
              var colField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "col]\"]"));
              var colspanField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "colspan]\"]"));
              var elementField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "element]\"]"));
              var customField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "custom]\"]"));
              var linktypeField = document.querySelector("[name=\"".concat(_this0.options.paramsPrefix, "[").concat(fieldPrefix, "linktype]\"]"));
              if (colField) col = parseInt(colField.value, 10) || col;
              if (colspanField) colspan = colspanField.value || colspan;
              if (elementField) elementType = elementField.value || elementType;
              if (customField) custom = customField.value || custom;
              if (linktypeField) linktype = linktypeField.value || linktype;
            }
            if (row > 0) {
              var data = {
                row: row,
                col: col,
                colspan: colspan,
                element: elementType,
                custom: custom,
                linktype: linktype
              };
              _this0.state.set(element.id, data);
              _this0.addElementToCanvas(element, data);
            }
          });
          this.sortCanvasElements();
          this.migrateLegacyColspans();
        }
      }, {
        key: "addElementToCanvas",
        value: function addElementToCanvas(element, data) {
          var rowEl = this.canvas.querySelector(".row-elements[data-row=\"".concat(data.row, "\"]"));
          if (!rowEl) return;
          var card = this.createElementCard(element, false);
          card.dataset.colspan = data.colspan;
          card.tabIndex = 0;
          rowEl.appendChild(card);
          this.addResizeHandles(card);
          this.updateElementInfo(card);
        }
      }, {
        key: "sortCanvasElements",
        value: function sortCanvasElements() {
          var _this1 = this;
          this.canvas.querySelectorAll('.row-elements').forEach(function (rowEl) {
            var cards = Array.from(rowEl.querySelectorAll('.element-card'));
            cards.sort(function (a, b) {
              var dataA = _this1.state.get(a.dataset.element);
              var dataB = _this1.state.get(b.dataset.element);
              return ((dataA === null || dataA === void 0 ? void 0 : dataA.col) || 0) - ((dataB === null || dataB === void 0 ? void 0 : dataB.col) || 0);
            });
            cards.forEach(function (card) {
              return rowEl.appendChild(card);
            });
          });
        }
      }, {
        key: "migrateLegacyColspans",
        value: function migrateLegacyColspans() {
          var _this10 = this;
          var rowGroups = new Map();
          this.state.forEach(function (data, elementId) {
            if (data.row > 0) {
              if (!rowGroups.has(data.row)) {
                rowGroups.set(data.row, []);
              }
              rowGroups.get(data.row).push({
                elementId: elementId,
                data: data
              });
            }
          });
          rowGroups.forEach(function (elements, row) {
            var totalColspan = elements.reduce(function (sum, el) {
              return sum + (parseInt(el.data.colspan, 10) || 1);
            }, 0);
            var elementCount = elements.length;
            var needsMigration = elementCount === 1 && totalColspan < 12 || elementCount > 1 && totalColspan !== 12;
            if (needsMigration) {
              var rowEl = _this10.canvas.querySelector(".row-elements[data-row=\"".concat(row, "\"]"));
              if (rowEl) {
                _this10.distributeColspans(rowEl, false);
              }
            }
          });
        }
      }, {
        key: "syncToForm",
        value: function syncToForm() {
          var _this11 = this;
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef) return;
          var prefix = contextDef.prefix;
          var form = document.getElementById(this.options.formId);
          contextDef.elements.forEach(function (element) {
            var fieldPrefix = prefix + element.id;
            var data = _this11.state.get(element.id);
            var fieldNames = {
              row: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "row]"),
              col: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "col]"),
              colspan: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "colspan]"),
              element: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "element]"),
              custom: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "custom]"),
              linktype: "".concat(_this11.options.paramsPrefix, "[").concat(fieldPrefix, "linktype]")
            };
            var getOrCreateField = function getOrCreateField(name, value) {
              var field = document.querySelector("[name=\"".concat(name, "\"]"));
              if (!field && form) {
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
              getOrCreateField(fieldNames.row, data.row);
              getOrCreateField(fieldNames.col, data.col);
              getOrCreateField(fieldNames.colspan, data.colspan);
              getOrCreateField(fieldNames.element, data.element);
              getOrCreateField(fieldNames.custom, data.custom);
              getOrCreateField(fieldNames.linktype, data.linktype);
            } else {
              getOrCreateField(fieldNames.row, '0');
            }
          });
        }
      }, {
        key: "getState",
        value: function getState() {
          var state = {};
          this.state.forEach(function (value, key) {
            state[key] = _objectSpread2({}, value);
          });
          return state;
        }
      }, {
        key: "saveStateForUndo",
        value: function saveStateForUndo() {
          var stateCopy = new Map();
          this.state.forEach(function (value, key) {
            stateCopy.set(key, _objectSpread2({}, value));
          });
          this.undoStack.push(stateCopy);
          if (this.undoStack.length > this.maxHistory) {
            this.undoStack.shift();
          }
          this.redoStack = [];
          this.updateToolbarState();
        }
      }, {
        key: "undo",
        value: function undo() {
          if (this.undoStack.length === 0) return;
          var currentState = new Map();
          this.state.forEach(function (value, key) {
            currentState.set(key, _objectSpread2({}, value));
          });
          this.redoStack.push(currentState);
          this.state = this.undoStack.pop();
          this.rebuildCanvas();
          this.updateToolbarState();
        }
      }, {
        key: "redo",
        value: function redo() {
          if (this.redoStack.length === 0) return;
          var currentState = new Map();
          this.state.forEach(function (value, key) {
            currentState.set(key, _objectSpread2({}, value));
          });
          this.undoStack.push(currentState);
          this.state = this.redoStack.pop();
          this.rebuildCanvas();
          this.updateToolbarState();
        }
      }, {
        key: "rebuildCanvas",
        value: function rebuildCanvas() {
          var _this12 = this;
          this.canvas.querySelectorAll('.row-elements').forEach(function (rowEl) {
            rowEl.innerHTML = '';
          });
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef) return;
          this.state.forEach(function (data, elementId) {
            var element = contextDef.elements.find(function (el) {
              return el.id === elementId;
            });
            if (element && data.row > 0) {
              _this12.addElementToCanvas(element, data);
            }
          });
          this.sortCanvasElements();
        }
      }, {
        key: "updateToolbarState",
        value: function updateToolbarState() {
          var _this$toolbar, _this$toolbar2;
          var undoBtn = (_this$toolbar = this.toolbar) === null || _this$toolbar === void 0 ? void 0 : _this$toolbar.querySelector('.btn-undo');
          var redoBtn = (_this$toolbar2 = this.toolbar) === null || _this$toolbar2 === void 0 ? void 0 : _this$toolbar2.querySelector('.btn-redo');
          if (undoBtn) {
            undoBtn.disabled = this.undoStack.length === 0;
          }
          if (redoBtn) {
            redoBtn.disabled = this.redoStack.length === 0;
          }
        }
      }, {
        key: "toggleGrid",
        value: function toggleGrid() {
          var _this$toolbar3;
          this.showGrid = !this.showGrid;
          if (this.editor) {
            this.editor.classList.toggle('show-grid', this.showGrid);
          }
          var gridBtn = (_this$toolbar3 = this.toolbar) === null || _this$toolbar3 === void 0 ? void 0 : _this$toolbar3.querySelector('.btn-grid');
          if (gridBtn) {
            gridBtn.classList.toggle('btn-primary', this.showGrid);
            gridBtn.classList.toggle('btn-secondary', !this.showGrid);
          }
        }
      }, {
        key: "switchView",
        value: function switchView(mode) {
          var _this$toolbar4, _this$toolbar5;
          if (this.viewMode === mode) return;
          this.viewMode = mode;
          var visualBtn = (_this$toolbar4 = this.toolbar) === null || _this$toolbar4 === void 0 ? void 0 : _this$toolbar4.querySelector('.btn-view-visual');
          var classicBtn = (_this$toolbar5 = this.toolbar) === null || _this$toolbar5 === void 0 ? void 0 : _this$toolbar5.querySelector('.btn-view-classic');
          if (visualBtn) {
            visualBtn.classList.toggle('btn-primary', mode === 'visual');
            visualBtn.classList.toggle('btn-secondary', mode !== 'visual');
          }
          if (classicBtn) {
            classicBtn.classList.toggle('btn-primary', mode === 'classic');
            classicBtn.classList.toggle('btn-secondary', mode !== 'classic');
          }
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
      }, {
        key: "renderClassicView",
        value: function renderClassicView() {
          var _this13 = this;
          var contextDef = ELEMENT_DEFINITIONS[this.currentContext];
          if (!contextDef || !this.classicView) return;
          var placedElements = [];
          this.state.forEach(function (data, elementId) {
            if (data.row > 0) {
              var element = contextDef.elements.find(function (el) {
                return el.id === elementId;
              });
              if (element) {
                placedElements.push({
                  element: element,
                  data: data
                });
              }
            }
          });
          placedElements.sort(function (a, b) {
            if (a.data.row !== b.data.row) return a.data.row - b.data.row;
            return a.data.col - b.data.col;
          });
          var html = "\n                <div class=\"classic-view-container\">\n                    <table class=\"table table-striped table-sm\">\n                        <thead>\n                            <tr>\n                                <th>Element</th>\n                                <th>Row</th>\n                                <th>Col</th>\n                                <th>Colspan</th>\n                                <th>Actions</th>\n                            </tr>\n                        </thead>\n                        <tbody>\n            ";
          if (placedElements.length === 0) {
            html += "\n                    <tr>\n                        <td colspan=\"5\" class=\"text-center text-muted\">\n                            No elements in layout. Switch to Visual view to add elements.\n                        </td>\n                    </tr>\n                ";
          } else {
            placedElements.forEach(function (_ref) {
              var element = _ref.element,
                data = _ref.data;
              html += "\n                        <tr data-element=\"".concat(element.id, "\">\n                            <td><strong>").concat(element.label, "</strong></td>\n                            <td>").concat(data.row, "</td>\n                            <td>").concat(data.col, "</td>\n                            <td>").concat(data.colspan, "</td>\n                            <td>\n                                <button type=\"button\" class=\"btn btn-secondary btn-classic-edit\" data-element=\"").concat(element.id, "\">\n                                    <span class=\"icon-options\" aria-hidden=\"true\"></span>\n                                </button>\n                                <button type=\"button\" class=\"btn btn-danger btn-classic-remove\" data-element=\"").concat(element.id, "\">\n                                    <span class=\"icon-cancel\" aria-hidden=\"true\"></span>\n                                </button>\n                            </td>\n                        </tr>\n                    ");
            });
          }
          html += "\n                        </tbody>\n                    </table>\n                </div>\n            ";
          this.classicView.innerHTML = html;
          this.classicView.querySelectorAll('.btn-classic-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
              return _this13.openSettings(btn.dataset.element);
            });
          });
          this.classicView.querySelectorAll('.btn-classic-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
              _this13.saveStateForUndo();
              var elementId = btn.dataset.element;
              _this13.state.delete(elementId);
              _this13.rebuildCanvas();
              _this13.renderClassicView();
            });
          });
        }
      }, {
        key: "addResizeHandles",
        value: function addResizeHandles(card) {
          var _this14 = this;
          if (card.dataset.paletteItem !== undefined) return;
          var rightHandle = document.createElement('div');
          rightHandle.className = 'resize-handle resize-handle-right';
          rightHandle.addEventListener('mousedown', function (e) {
            return _this14.startResize(e, card, 'right');
          });
          card.appendChild(rightHandle);
          var leftHandle = document.createElement('div');
          leftHandle.className = 'resize-handle resize-handle-left';
          leftHandle.addEventListener('mousedown', function (e) {
            return _this14.startResize(e, card, 'left');
          });
          card.appendChild(leftHandle);
        }
      }, {
        key: "startResize",
        value: function startResize(e, card, direction) {
          var _this15 = this;
          e.preventDefault();
          e.stopPropagation();
          var rowEl = card.closest('.row-elements');
          if (!rowEl) return;
          var children = Array.from(rowEl.children).filter(function (el) {
            return !el.classList.contains('sortable-ghost') && el.classList.contains('element-card');
          });
          var cardIndex = children.indexOf(card);
          var neighborCard = null;
          var isOuterEdge = false;
          if (direction === 'right') {
            if (cardIndex < children.length - 1) {
              neighborCard = children[cardIndex + 1];
            } else {
              isOuterEdge = true;
            }
          } else if (direction === 'left') {
            if (cardIndex > 0) {
              neighborCard = children[cardIndex - 1];
            } else {
              isOuterEdge = true;
            }
          }
          if (isOuterEdge && children.length < 2) return;
          this.isResizing = true;
          this.resizeCard = card;
          this.resizeNeighborCard = neighborCard;
          this.resizeDirection = direction;
          this.resizeIsOuterEdge = isOuterEdge;
          this.resizeStartX = e.clientX;
          this.resizeRowEl = rowEl;
          var data = this.state.get(card.dataset.element);
          this.resizeStartColspan = parseInt((data === null || data === void 0 ? void 0 : data.colspan) || 1, 10);
          if (neighborCard) {
            var neighborData = this.state.get(neighborCard.dataset.element);
            this.resizeNeighborStartColspan = parseInt((neighborData === null || neighborData === void 0 ? void 0 : neighborData.colspan) || 1, 10);
          } else {
            this.resizeOtherElements = children.filter(function (el) {
              return el !== card;
            });
            this.resizeOtherStartColspans = this.resizeOtherElements.map(function (el) {
              var d = _this15.state.get(el.dataset.element);
              return parseInt((d === null || d === void 0 ? void 0 : d.colspan) || 1, 10);
            });
          }
          this.columnWidth = rowEl.offsetWidth / 12;
          this.saveStateForUndo();
          card.classList.add('resizing');
          if (neighborCard) {
            neighborCard.classList.add('resizing');
          }
          document.addEventListener('mousemove', this.handleResize);
          document.addEventListener('mouseup', this.endResize);
        }
      }, {
        key: "handleInnerEdgeResize",
        value: function handleInnerEdgeResize(colsDelta) {
          var combinedColspan = this.resizeStartColspan + this.resizeNeighborStartColspan;
          var newColspan = this.resizeStartColspan + colsDelta;
          var newNeighborColspan = combinedColspan - newColspan;
          if (newColspan < 1) {
            newColspan = 1;
            newNeighborColspan = combinedColspan - 1;
          }
          if (newNeighborColspan < 1) {
            newNeighborColspan = 1;
            newColspan = combinedColspan - 1;
          }
          var data = this.state.get(this.resizeCard.dataset.element);
          if (data) {
            data.colspan = String(newColspan);
            data.manualColspan = true;
            this.resizeCard.dataset.colspan = String(newColspan);
            this.updateElementInfo(this.resizeCard);
          }
          var neighborData = this.state.get(this.resizeNeighborCard.dataset.element);
          if (neighborData) {
            neighborData.colspan = String(newNeighborColspan);
            neighborData.manualColspan = true;
            this.resizeNeighborCard.dataset.colspan = String(newNeighborColspan);
            this.updateElementInfo(this.resizeNeighborCard);
          }
        }
      }, {
        key: "handleOuterEdgeResize",
        value: function handleOuterEdgeResize(colsDelta) {
          var _this16 = this;
          var otherCount = this.resizeOtherElements.length;
          if (otherCount === 0) return;
          var newColspan = this.resizeStartColspan + colsDelta;
          var maxColspan = 12 - otherCount;
          newColspan = Math.max(1, Math.min(maxColspan, newColspan));
          var remainingCols = 12 - newColspan;
          var originalOtherTotal = this.resizeOtherStartColspans.reduce(function (a, b) {
            return a + b;
          }, 0);
          var data = this.state.get(this.resizeCard.dataset.element);
          if (data) {
            data.colspan = String(newColspan);
            data.manualColspan = true;
            this.resizeCard.dataset.colspan = String(newColspan);
            this.updateElementInfo(this.resizeCard);
          }
          var distributed = 0;
          this.resizeOtherElements.forEach(function (card, index) {
            var otherData = _this16.state.get(card.dataset.element);
            if (otherData) {
              var newOtherColspan;
              if (index === otherCount - 1) {
                newOtherColspan = remainingCols - distributed;
              } else {
                var ratio = _this16.resizeOtherStartColspans[index] / originalOtherTotal;
                newOtherColspan = Math.max(1, Math.round(remainingCols * ratio));
                distributed += newOtherColspan;
              }
              newOtherColspan = Math.max(1, newOtherColspan);
              otherData.colspan = String(newOtherColspan);
              card.dataset.colspan = String(newOtherColspan);
              _this16.updateElementInfo(card);
            }
          });
        }
      }, {
        key: "handleKeyboard",
        value: function handleKeyboard(e) {
          var _document$activeEleme;
          if (e.target.matches('input, textarea, select')) return;
          if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
            e.preventDefault();
            this.undo();
            return;
          }
          if (e.ctrlKey && e.key === 'y' || e.ctrlKey && e.shiftKey && e.key === 'z') {
            e.preventDefault();
            this.redo();
            return;
          }
          if ((e.key === 'Delete' || e.key === 'Backspace') && (_document$activeEleme = document.activeElement) !== null && _document$activeEleme !== void 0 && _document$activeEleme.classList.contains('element-card')) {
            var card = document.activeElement;
            if (card.closest('.row-elements')) {
              e.preventDefault();
              this.saveStateForUndo();
              this.removeElement(card);
            }
            return;
          }
          if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            var focused = document.activeElement;
            if (focused !== null && focused !== void 0 && focused.classList.contains('element-card') && focused.closest('.row-elements')) {
              e.preventDefault();
              this.navigateElements(focused, e.key);
            }
          }
        }
      }, {
        key: "navigateElements",
        value: function navigateElements(current, direction) {
          var _targetCard;
          var rowEl = current.closest('.row-elements');
          var allRows = Array.from(this.canvas.querySelectorAll('.row-elements'));
          var rowIndex = allRows.indexOf(rowEl);
          var targetCard = null;
          switch (direction) {
            case 'ArrowLeft':
              {
                targetCard = current.previousElementSibling;
                break;
              }
            case 'ArrowRight':
              {
                targetCard = current.nextElementSibling;
                break;
              }
            case 'ArrowUp':
              {
                if (rowIndex > 0) {
                  var prevRow = allRows[rowIndex - 1];
                  var cards = prevRow.querySelectorAll('.element-card');
                  if (cards.length > 0) {
                    targetCard = cards[0];
                  }
                }
                break;
              }
            case 'ArrowDown':
              {
                if (rowIndex < allRows.length - 1) {
                  var nextRow = allRows[rowIndex + 1];
                  var _cards = nextRow.querySelectorAll('.element-card');
                  if (_cards.length > 0) {
                    targetCard = _cards[0];
                  }
                }
                break;
              }
          }
          if ((_targetCard = targetCard) !== null && _targetCard !== void 0 && _targetCard.classList.contains('element-card')) {
            targetCard.focus();
          }
        }
      }]);
    }();
    window.LayoutEditor = LayoutEditor;
    function initLayoutEditor() {
      var container = document.getElementById('layout-editor-container');
      if (container && !container.dataset.initialized) {
        container.dataset.initialized = 'true';
        var initialContext = container.dataset.context || 'messages';
        window.proclaimLayoutEditor = new LayoutEditor(container, {
          context: initialContext
        });
      }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initLayoutEditor);
    } else {
      initLayoutEditor();
    }
  })();

})();
