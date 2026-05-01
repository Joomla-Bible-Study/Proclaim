/**
 * Merge modal submit handler for the Locations list view.
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('merge-submit-button-id');

        if (button) {
            button.addEventListener('click', (e) => {
                const task = e.target.getAttribute('data-submit-task');
                const form = document.getElementById('adminForm');

                if (form && task === 'cwmlocations.merge') {
                    submitForm(task, form);
                }
            });
        }
    });
})(document, Joomla.submitform);
