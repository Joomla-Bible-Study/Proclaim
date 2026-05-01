/**
 * Platform Stats — Description Sync / Copy helper
 *
 * Handles two interaction paths:
 * 1. Sync-capable platforms (Vimeo, Wistia): Preview modal → editable textarea → push to platform
 * 2. Read-only platforms (YouTube): Copy to clipboard → toast with paste instructions
 *
 * Buttons use data attributes:
 *   data-desc-action="true"   — activates this handler
 *   data-study-id="{int}"     — study ID for description generation
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
(() => {
    'use strict';

    const T = (key, fallback) => {
        const val = Joomla.Text._(key);

        // Joomla.Text._() returns the raw key when unregistered, not empty
        return (val && val !== key) ? val : (fallback || key);
    };

    /**
     * Get the CSRF token name from the page.
     *
     * Joomla's CSRF token is a hidden input whose name is a 32-char hex hash.
     * We match on that pattern to avoid false positives from other hidden
     * inputs with value="1" (e.g. delete_physical_files).
     *
     * @returns {string}
     */
    function getToken() {
        const inputs = document.querySelectorAll('input[type="hidden"][value="1"]');

        for (const input of inputs) {
            if (/^[0-9a-f]{32}$/.test(input.name)) {
                return input.name;
            }
        }

        return '';
    }

    /**
     * Fetch the generated video description for a study via AJAX.
     *
     * @param {number} studyId
     * @param {number} [mediaId=0]  Optional media file ID — includes chapters from that file
     * @returns {Promise<{success: boolean, description?: string, error?: string}>}
     */
    async function fetchDescription(studyId, mediaId = 0) {
        const token = getToken();
        let url = 'index.php?option=com_proclaim&task=cwmadmin.getVideoDescriptionXHR'
            + '&study_id=' + studyId
            + '&' + token + '=1';

        if (mediaId > 0) {
            url += '&media_id=' + mediaId;
        }

        return window.ProclaimFetch.fetchJson(
            url,
            { method: 'GET', headers: { 'X-CSRF-Token': '1' } },
            { timeout: 30000, retries: 1 },
        );
    }

    /**
     * Copy text to clipboard with fallback.
     *
     * @param {string} text
     * @returns {Promise<boolean>}
     */
    async function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            try {
                await navigator.clipboard.writeText(text);

                return true;
            } catch {
                // Fall through to fallback
            }
        }

        // Fallback: textarea + execCommand
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();

        let ok = false;

        try {
            ok = document.execCommand('copy');
        } catch {
            // ignore
        }

        document.body.removeChild(ta);

        return ok;
    }

    /**
     * Show a Bootstrap modal with the description preview and optional sync controls.
     *
     * @param {string} description  The generated description text
     * @param {number} studyId      Study ID
     * @param {Array}  syncMedia    Array of {mediaId, platform, canSync, serverName} for sync-capable media
     */
    function showDescriptionModal(description, studyId, syncMedia) {
        // Remove any existing modal
        const existing = document.getElementById('cwm-desc-modal');

        if (existing) {
            existing.remove();
        }

        const hasSyncable = syncMedia.some((m) => m.canSync);

        // Build sync target checkboxes for sync-capable media
        let syncHtml = '';

        if (hasSyncable) {
            syncHtml = '<div class="mt-3"><label class="form-label fw-semibold">'
                + T('JBS_MED_SYNC_DESC_PREVIEW', 'Push to platforms') + ':</label>';

            syncMedia.forEach((m) => {
                if (m.canSync) {
                    syncHtml += '<div class="form-check">'
                        + '<input class="form-check-input cwm-sync-target" type="checkbox" '
                        + 'value="' + m.mediaId + '" id="cwm-sync-' + m.mediaId + '" checked>'
                        + '<label class="form-check-label" for="cwm-sync-' + m.mediaId + '">'
                        + m.serverName + ' (' + m.platform + ')</label></div>';
                }
            });

            syncHtml += '</div>';
        }

        const modalHtml = '<div class="modal fade" id="cwm-desc-modal" tabindex="-1">'
            + '<div class="modal-dialog modal-lg">'
            + '<div class="modal-content">'
            + '<div class="modal-header">'
            + '<h5 class="modal-title">' + T('JBS_MED_SYNC_DESC', 'Sync Description') + '</h5>'
            + '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>'
            + '</div>'
            + '<div class="modal-body">'
            + '<textarea id="cwm-desc-textarea" class="form-control" rows="10">'
            + description.replace(/</g, '&lt;').replace(/>/g, '&gt;')
            + '</textarea>'
            + syncHtml
            + '</div>'
            + '<div class="modal-footer">'
            + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
            + T('JCANCEL', 'Cancel') + '</button>'
            + '<button type="button" class="btn btn-outline-secondary" id="cwm-desc-copy-btn">'
            + '<i class="icon-copy me-1"></i>' + T('JBS_MED_COPY_DESC', 'Copy') + '</button>'
            + (hasSyncable
                ? '<button type="button" class="btn btn-primary" id="cwm-desc-sync-btn">'
                + '<i class="icon-upload me-1"></i>' + T('JBS_MED_SYNC_DESC', 'Sync') + '</button>'
                : '')
            + '</div></div></div></div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modalEl = document.getElementById('cwm-desc-modal');
        const bsModal = new bootstrap.Modal(modalEl);

        // Copy button inside modal
        modalEl.querySelector('#cwm-desc-copy-btn').addEventListener('click', async () => {
            const text = document.getElementById('cwm-desc-textarea').value;
            const ok = await copyToClipboard(text);

            if (ok) {
                Joomla.renderMessages({ success: [T('JBS_MED_COPY_DESC_COPIED', 'Copied!')] });
            } else {
                Joomla.renderMessages({ warning: [T('JBS_MED_COPY_DESC_FAIL', 'Copy failed')] });
            }
        });

        // Sync button inside modal (if present)
        const syncBtn = modalEl.querySelector('#cwm-desc-sync-btn');

        if (syncBtn) {
            syncBtn.addEventListener('click', async () => {
                const text = document.getElementById('cwm-desc-textarea').value;
                const targets = modalEl.querySelectorAll('.cwm-sync-target:checked');

                if (!targets.length) {
                    Joomla.renderMessages({ warning: ['No platforms selected'] });

                    return;
                }

                syncBtn.disabled = true;
                syncBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>'
                    + T('JBS_ANA_SYNCING', 'Syncing') + '...';

                const token = getToken();
                let successes = 0;
                const errors = [];

                for (const checkbox of targets) {
                    const mediaId = parseInt(checkbox.value, 10);
                    const label = checkbox.nextElementSibling?.textContent || '';

                    try {
                        const url = 'index.php?option=com_proclaim&task=cwmadmin.syncVideoDescriptionXHR'
                            + '&study_id=' + studyId
                            + '&media_id=' + mediaId
                            + '&' + token + '=1';

                        const data = await window.ProclaimFetch.fetchJson(
                            url,
                            {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-CSRF-Token': '1',
                                },
                                body: 'description=' + encodeURIComponent(text),
                            },
                            { timeout: 30000, retries: 1 },
                        );

                        if (data.success) {
                            successes += 1;
                        } else {
                            errors.push(label + ': ' + (data.error || 'Unknown error'));
                        }
                    } catch (e) {
                        errors.push(label + ': ' + e.message);
                    }
                }

                syncBtn.disabled = false;
                syncBtn.innerHTML = '<i class="icon-upload me-1"></i>'
                    + T('JBS_MED_SYNC_DESC', 'Sync');

                if (successes > 0) {
                    Joomla.renderMessages({
                        success: [T('JBS_MED_SYNC_DESC_SUCCESS', 'Description synced successfully')],
                    });
                }

                if (errors.length > 0) {
                    Joomla.renderMessages({ error: errors });
                }

                if (successes > 0 && errors.length === 0) {
                    bsModal.hide();
                }
            });
        }

        // Cleanup on close
        modalEl.addEventListener('hidden.bs.modal', () => {
            bsModal.dispose();
            modalEl.remove();
        });

        bsModal.show();
    }

    /**
     * Handle a description action button click.
     * Determines whether to open the preview modal or just copy.
     *
     * @param {HTMLElement} btn  The button element with data attributes
     */
    async function handleDescriptionAction(btn) {
        const studyId = parseInt(btn.dataset.studyId, 10);

        if (!studyId) {
            return;
        }

        btn.disabled = true;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>'
            + T('JLIB_HTML_PLEASE_WAIT', 'Please wait') + '...';

        try {
            // Collect sync-capable media from the table rows
            const syncMedia = collectSyncableMedia();

            // Pass the first media ID so the description includes its chapters
            const firstMediaId = syncMedia.length > 0 ? syncMedia[0].mediaId : 0;
            const result = await fetchDescription(studyId, firstMediaId);

            if (!result.success || !result.description) {
                Joomla.renderMessages({
                    error: [result.error || 'Failed to generate description'],
                });

                return;
            }

            if (syncMedia.length > 0) {
                // Show modal with preview + sync options
                showDescriptionModal(result.description, studyId, syncMedia);
            } else {
                // Simple copy to clipboard
                const ok = await copyToClipboard(result.description);

                if (ok) {
                    // Show platform-specific paste instructions if we can detect the platform
                    const platform = detectPrimaryPlatform();
                    const key = platform
                        ? 'JBS_MED_PASTE_INSTRUCTIONS_' + platform.toUpperCase()
                        : 'JBS_MED_COPY_DESC_COPIED';

                    Joomla.renderMessages({ success: [T(key, 'Copied!')] });
                } else {
                    // Clipboard failed — show modal with textarea for manual copy
                    showDescriptionModal(result.description, studyId, []);
                }
            }
        } catch (e) {
            Joomla.renderMessages({ error: [e.message] });
        } finally {
            btn.innerHTML = origHtml;
            btn.disabled = false;
        }
    }

    /**
     * Collect media files from the page that support description sync.
     * Reads from hidden data embedded by PHP or from table rows.
     *
     * @returns {Array<{mediaId: number, platform: string, canSync: boolean, serverName: string}>}
     */
    function collectSyncableMedia() {
        const items = [];

        // Look for data attributes on the card or embedded JSON
        const dataEl = document.querySelector('[data-sync-media]');

        if (dataEl) {
            try {
                return JSON.parse(dataEl.dataset.syncMedia);
            } catch {
                // Fall through
            }
        }

        return items;
    }

    /**
     * Detect the primary video platform from platform badges in the stats table.
     *
     * @returns {string|null}  Platform name (youtube, vimeo, wistia) or null
     */
    function detectPrimaryPlatform() {
        const badges = document.querySelectorAll('.badge.bg-secondary');

        for (const badge of badges) {
            const text = (badge.textContent || '').trim().toLowerCase();

            if (['youtube', 'vimeo', 'wistia'].includes(text)) {
                return text;
            }
        }

        return null;
    }

    /** Initialise description action buttons */
    function init() {
        document.querySelectorAll('[data-desc-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                handleDescriptionAction(btn);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
