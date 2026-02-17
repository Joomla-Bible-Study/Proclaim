/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * International phone input with country picker and auto-formatting.
 * Uses intl-tel-input library (MIT license).
 */
((document) => {
    'use strict';

    /**
     * Initialize intl-tel-input on a phone field.
     *
     * @param {HTMLInputElement} input  The phone input element
     * @param {Object}          opts   Override options
     */
    function initPhoneInput(input, opts = {}) {
        if (input.dataset.itiInit) {
            return;
        }

        const defaults = {
            initialCountry: 'us',
            nationalMode: true,
            formatAsYouType: true,
            autoPlaceholder: 'aggressive',
            showSelectedDialCode: true,
            containerClass: 'iti--proclaim',
            countrySearch: true,
            // Put common church-ministry countries first
            preferredCountries: ['us', 'ca', 'gb', 'au', 'nz', 'za', 'ph', 'in', 'br', 'mx', 'de', 'kr', 'ng'],
        };

        const options = Object.assign({}, defaults, opts);

        // eslint-disable-next-line no-undef
        const iti = window.intlTelInput(input, options);

        // Store the full international number before form submit
        const form = input.closest('form');

        if (form) {
            form.addEventListener('submit', () => {
                if (iti.isValidNumber()) {
                    input.value = iti.getNumber();
                } else if (input.value.trim()) {
                    // Keep whatever the user typed if not valid international
                    const num = iti.getNumber();

                    if (num) {
                        input.value = num;
                    }
                }
            });
        }

        input.dataset.itiInit = '1';
    }

    /**
     * Auto-discover and init all phone inputs with data-phone-input attribute,
     * or fallback to jform_phone field.
     */
    function initAll() {
        // Explicit data attribute targets
        document.querySelectorAll('[data-phone-input]').forEach((el) => {
            initPhoneInput(el);
        });

        // Fallback: find the Joomla phone field by ID
        const jformPhone = document.getElementById('jform_phone');

        if (jformPhone && !jformPhone.dataset.itiInit) {
            initPhoneInput(jformPhone);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})(document);
