(function () {
    'use strict';

    /**
     * @package     Proclaim
     * @subpackage  com_proclaim
     * @copyright   (C) 2026 CWM Team All rights reserved
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     *
     * Template form lazy-loading functionality
     * Loads fieldsets on-demand when accordion sections are expanded
     */
    document.addEventListener('DOMContentLoaded', function () {

      var loadedFieldsets = new Set();
      var loadingFieldsets = new Set();
      function loadFieldset(fieldsetName, container, templateId) {
        var _document$querySelect;
        if (loadedFieldsets.has(fieldsetName) || loadingFieldsets.has(fieldsetName)) {
          return;
        }
        loadingFieldsets.add(fieldsetName);
        container.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';
        Joomla.getOptions('csrf.token') || ((_document$querySelect = document.querySelector('input[name^="' + Joomla.getOptions('csrf.token', '') + '"]')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.value);
        var url = 'index.php?option=com_proclaim&task=cwmtemplate.loadFieldset&format=json' + '&fieldset=' + encodeURIComponent(fieldsetName) + '&id=' + templateId + '&' + Joomla.getOptions('csrf.token', '') + '=1';
        fetch(url, {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          loadingFieldsets.delete(fieldsetName);
          if (data.success) {
            loadedFieldsets.add(fieldsetName);
            container.innerHTML = data.html;
            if (typeof Joomla !== 'undefined' && Joomla.initCustomSelect) {
              Joomla.initCustomSelect(container);
            }
            container.dispatchEvent(new CustomEvent('fieldsetLoaded', {
              bubbles: true,
              detail: {
                fieldset: fieldsetName
              }
            }));
          } else {
            container.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Failed to load content') + '</div>';
          }
        }).catch(function (error) {
          loadingFieldsets.delete(fieldsetName);
          container.innerHTML = '<div class="alert alert-danger">Error loading content: ' + error.message + '</div>';
          console.error('Fieldset load error:', error);
        });
      }
      function initAccordionLazyLoad() {
        document.querySelectorAll('[data-lazy-fieldset]').forEach(function (accordion) {
          var _document$querySelect2;
          var fieldsetName = accordion.dataset.lazyFieldset;
          var collapseTarget = accordion.querySelector('.accordion-collapse');
          var contentContainer = accordion.querySelector('.accordion-body');
          var templateId = parseInt(((_document$querySelect2 = document.querySelector('input[name="jform[id]"]')) === null || _document$querySelect2 === void 0 ? void 0 : _document$querySelect2.value) || '0', 10);
          if (!collapseTarget || !contentContainer) {
            return;
          }
          collapseTarget.addEventListener('show.bs.collapse', function () {
            loadFieldset(fieldsetName, contentContainer, templateId);
          });
          if (collapseTarget.classList.contains('show')) {
            loadFieldset(fieldsetName, contentContainer, templateId);
          }
        });
      }
      function initTabLazyLoad() {
        document.querySelectorAll('[data-lazy-tab]').forEach(function (tabPane) {
          var _document$querySelect3;
          var fieldsets = tabPane.dataset.lazyTab.split(',');
          var templateId = parseInt(((_document$querySelect3 = document.querySelector('input[name="jform[id]"]')) === null || _document$querySelect3 === void 0 ? void 0 : _document$querySelect3.value) || '0', 10);
          var tabId = tabPane.id;
          var tabButton = document.querySelector('[data-bs-target="#' + tabId + '"], [href="#' + tabId + '"]');
          if (!tabButton) {
            return;
          }
          tabButton.addEventListener('shown.bs.tab', function () {
            fieldsets.forEach(function (fieldsetName) {
              var container = tabPane.querySelector('[data-fieldset-container="' + fieldsetName.trim() + '"]');
              if (container) {
                loadFieldset(fieldsetName.trim(), container, templateId);
              }
            });
          });
          if (tabPane.classList.contains('active') || tabPane.classList.contains('show')) {
            fieldsets.forEach(function (fieldsetName) {
              var container = tabPane.querySelector('[data-fieldset-container="' + fieldsetName.trim() + '"]');
              if (container) {
                loadFieldset(fieldsetName.trim(), container, templateId);
              }
            });
          }
        });
      }
      initAccordionLazyLoad();
      initTabLazyLoad();
    });

})();
