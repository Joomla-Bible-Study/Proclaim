/**
 * Generic batch footer handler for all Proclaim admin list views.
 *
 * Reads the task from the batch submit button's data-submit-task attribute
 * and submits the form. Replaces entity-specific batch JS files.
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('batch-submit-button-id');

        if (button) {
            button.addEventListener('click', (e) => {
                const task = e.target.getAttribute('data-submit-task');
                const form = document.getElementById('adminForm');

                if (form && task && task.endsWith('.batch')) {
                    submitForm(task, form);
                }
            });
        }
    });
})(document, Joomla.submitform);
