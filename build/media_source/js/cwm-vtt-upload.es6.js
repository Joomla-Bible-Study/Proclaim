/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * VTT/SRT caption file upload handler.
 *
 * Works with the VttUploadField custom form field. Each field renders:
 *   <div class="cwm-vtt-field">
 *     <div class="input-group">
 *       <input type="url" class="cwm-vtt-url" ...>
 *       <button class="cwm-vtt-upload-btn" ...>Browse</button>
 *     </div>
 *     <input type="file" class="cwm-vtt-file-input" ...>
 *   </div>
 *
 * Uses delegated events so dynamically added subform rows work.
 */
(function () {
    'use strict';

    const cfg = Joomla.getOptions('com_proclaim.vttUpload') || {};
    const uploadUrl    = cfg.uploadUrl || '';
    const uploadingTxt = Joomla.Text._('JBS_MED_VTT_UPLOADING') || 'Uploading…';
    const failedTxt    = Joomla.Text._('JBS_MED_VTT_UPLOAD_FAILED') || 'Upload failed';

    // Click the Browse button → trigger the hidden file input
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.cwm-vtt-upload-btn');

        if (!btn) {
            return;
        }

        e.preventDefault();
        const field     = btn.closest('.cwm-vtt-field');
        const fileInput = field ? field.querySelector('.cwm-vtt-file-input') : null;

        if (fileInput) {
            fileInput.click();
        }
    });

    // File selected → upload via AJAX
    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('cwm-vtt-file-input')) {
            return;
        }

        const fileInput = e.target;
        const field     = fileInput.closest('.cwm-vtt-field');

        if (!field) {
            return;
        }

        const urlInput = field.querySelector('.cwm-vtt-url');
        const btn      = field.querySelector('.cwm-vtt-upload-btn');

        if (!fileInput.files.length || !urlInput || !btn || !uploadUrl) {
            return;
        }

        const formData = new FormData();
        formData.append('vttfile', fileInput.files[0]);

        const origHtml = btn.innerHTML;
        btn.innerHTML  = '<span class="spinner-border spinner-border-sm"></span> ' + uploadingTxt;
        btn.disabled   = true;

        fetch(uploadUrl, { method: 'POST', body: formData })
            .then((r) => r.json())
            .then((data) => {
                if (data.success && data.url) {
                    urlInput.value = data.url;
                    urlInput.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    Joomla.renderMessages({ error: [data.error || failedTxt] });
                }
            })
            .catch(() => {
                Joomla.renderMessages({ error: [failedTxt] });
            })
            .finally(() => {
                btn.innerHTML   = origHtml;
                btn.disabled    = false;
                fileInput.value = '';
            });
    });
})();
