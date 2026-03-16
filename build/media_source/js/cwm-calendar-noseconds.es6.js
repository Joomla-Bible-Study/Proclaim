/**
 * CwmcalendarField — strips seconds from Joomla CalendarField display.
 *
 * The DB stores DATETIME with seconds, and Joomla's CalendarField always
 * renders them. This script intercepts the input element's value setter via
 * Object.defineProperty so every write (PHP, JS init, date pick) has seconds
 * stripped.
 *
 * @package    Proclaim.Admin
 * @subpackage Field
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.2.0
 */
(function () {
    if (window._cwmCalNoSec) {
        return;
    }
    window._cwmCalNoSec = true;

    const desc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

    document.querySelectorAll('[data-cwm-no-seconds] input[type="text"]').forEach(function (input) {
        Object.defineProperty(input, 'value', {
            get() {
                return desc.get.call(this);
            },
            set(val) {
                let cleaned = val;
                if (typeof cleaned === 'string') {
                    cleaned = cleaned.replace(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}):\d{2}$/, '$1');
                }
                desc.set.call(this, cleaned);
            },
            configurable: true,
        });
    });
})();
