(function () {
  'use strict';

  /**
   * @package    Proclaim.Admin
   * @copyright  (C) 2026 CWM Team All rights reserved
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   *
   * Keyboard shortcuts for Proclaim admin edit forms.
   * Only activates when an edit form with a task input is present.
   *
   * @since 10.1.0
   */
  ((Joomla) => {

    // Only bind on edit forms (not list views)
    const taskField = document.querySelector('input[name="task"]');
    if (!taskField) {
      return;
    }

    // Detect current controller from the form's hidden task field or URL
    const form = taskField.closest('form');
    let controller = '';

    if (form) {
      // Try to get controller from existing task value (e.g. "cwmmessage.edit")
      const taskVal = taskField.value;
      if (taskVal && taskVal.indexOf('.') > -1) {
        controller = taskVal.split('.')[0];
      }
    }

    // Fallback: parse from URL option + view
    if (!controller) {
      const params = new URLSearchParams(window.location.search);
      const view = params.get('view') || '';
      if (view) {
        controller = view;
      }
    }

    if (!controller) {
      return;
    }

    document.addEventListener('keydown', (e) => {
      const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
      const mod = isMac ? e.metaKey : e.ctrlKey;

      if (!mod) {
        return;
      }

      // Ctrl+Shift+S / Cmd+Shift+S = Save & Close
      if (e.shiftKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        Joomla.submitbutton(controller + '.save');
        return;
      }

      // Ctrl+Shift+N / Cmd+Shift+N = Save & New
      if (e.shiftKey && e.key.toLowerCase() === 'n') {
        e.preventDefault();
        Joomla.submitbutton(controller + '.save2new');
        return;
      }

      // Ctrl+S / Cmd+S = Save (apply)
      if (!e.shiftKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        Joomla.submitbutton(controller + '.apply');
        return;
      }
    });

    // Escape = Cancel/Close (no modifier needed)
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
        // Don't cancel if user is in a modal or dropdown
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.closest('.modal.show') || activeEl.closest('.choices__list--dropdown'))) {
          return;
        }

        e.preventDefault();
        Joomla.submitbutton(controller + '.cancel');
      }
    });
  })(Joomla);

})();
