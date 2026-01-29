(function () {
    'use strict';

    /**
     * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     */
    (function () {
      window.jSelectSeries = function (id, title, object, link, lang) {
        var hreflang = '';
        if (!Joomla.getOptions('xtd-servers')) {
          if (window.parent.Joomla.Modal) {
            window.parent.Joomla.Modal.getCurrent().close();
          }
          return false;
        }
        var _Joomla$getOptions = Joomla.getOptions('xtd-servers'),
          editor = _Joomla$getOptions.editor;
        if (lang !== '') {
          hreflang = "hreflang = \"".concat(lang, "\"");
        }
        var tag = " < a ".concat(hreflang, " href = \"").concat(link, "\" > ").concat(title, " < / a > ");
        window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
        if (window.parent.Joomla.Modal) {
          window.parent.Joomla.Modal.getCurrent().close();
        }
        return true;
      };
      document.addEventListener('DOMContentLoaded', function () {
        var elements = document.querySelectorAll('.select-link');
        for (var i = 0, l = elements.length; l > i; i += 1) {
          elements[i].addEventListener('click', function (event) {
            event.preventDefault();
            var target = event.target;
            var functionName = target.getAttribute('data-function');
            if (functionName === 'jSelectServer') {
              window[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-uri'), target.getAttribute('data-language'));
            } else {
              window.parent[functionName](target.getAttribute('data-id'), target.getAttribute('data-title'), target.getAttribute('data-uri'), target.getAttribute('data-language'));
            }
            if (window.parent.Joomla.Modal) {
              window.parent.Joomla.Modal.getCurrent().close();
              var doc = window.parent.document,
                theForm = doc.getElementById("adminForm"),
                task = doc.getElementsByName('task');
              for (var _i = 0; _i < task.length; _i++) {
                task[_i].value = "cwmmediafile.setServer";
              }
              theForm.submit();
            }
          });
        }
      });
    })();

})();
