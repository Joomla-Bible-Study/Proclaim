/**
 * Upload field — plupload initialisation.
 *
 * Reads configuration from Joomla.getOptions('com_proclaim.uploadField'):
 *   - url:     the XHR upload endpoint (includes CSRF token)
 *   - handler: the server handler identifier
 *
 * @package    Proclaim.Admin
 * @subpackage Field
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.2.0
 */
/* global uploader */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof uploader === 'undefined') {
        return;
    }

    const opts = Joomla.getOptions('com_proclaim.uploadField');
    if (!opts) {
        return;
    }

    uploader.setOption('url', opts.url);
    uploader.bind('BeforeUpload', function () {
        const pathEl = document.getElementById('jform_params_localFolder');
        const typeEl = document.getElementById('jform_serverType');
        uploader.setOption('multipart_params', {
            handler: opts.handler,
            path: pathEl ? pathEl.value : '',
            type: typeEl ? typeEl.value : '',
        });
    });
    uploader.init();
});
