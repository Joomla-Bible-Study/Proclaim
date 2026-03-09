/**
 * Generic form validation submit button handler.
 *
 * Reads configuration from Joomla.getOptions('com_proclaim.formValidate'):
 *   - cancelTask: e.g. "cwmtopic.cancel"
 *   - formId:     e.g. "item-form"
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
    'use strict';

    const config = Joomla.getOptions('com_proclaim.formValidate');

    if (!config) {
        return;
    }

    const { cancelTask, formId } = config;

    Joomla.submitbutton = (task) => {
        const formEl = document.getElementById(formId);

        if (task === cancelTask || document.formvalidator.isValid(formEl)) {
            Joomla.submitform(task, formEl);
        } else {
            const msg = Joomla.Text._('JGLOBAL_VALIDATION_FORM_FAILED') || 'Some values are unacceptable.';
            Joomla.renderMessages({ error: [msg] });
        }
    };
})();