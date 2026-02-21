/**
 * Location Setup Wizard — client-side step navigation and AJAX submission.
 *
 * Reads initial data from window.ProcWizard (set by the template):
 *   { token, baseUrl, scenario, locations, groups, savedMapping }
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */

((doc) => {
    'use strict';

    const TOTAL_STEPS = 7;

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    let currentStep = 1;
    /** @type {Object.<string, number[]>} locationId → groupId[] */
    let pendingMapping = {};
    /** @type {Object.<string, string>} groupId → preset ('full'|'editor'|'none') */
    let pendingPermissions = {};

    // -------------------------------------------------------------------------
    // DOM helpers
    // -------------------------------------------------------------------------

    /**
     * Return a localised string, falling back to the key if unregistered.
     *
     * Joomla.Text._() returns the raw key when unregistered (truthy), so we
     * compare the result against the key to detect missing registrations.
     *
     * @param {string} key
     * @param {string} [fallback]
     * @returns {string}
     */
    function txt(key, fallback) {
        if (typeof Joomla !== 'undefined' && Joomla.Text) {
            const result = Joomla.Text._(key);
            if (result !== key) {
                return result;
            }
        }
        return fallback || key;
    }

    function el(id) { return doc.getElementById(id); }
    function qs(sel, ctx) { return (ctx || doc).querySelector(sel); }
    function qsa(sel, ctx) { return [...(ctx || doc).querySelectorAll(sel)]; }

    // -------------------------------------------------------------------------
    // Step navigation
    // -------------------------------------------------------------------------

    /**
     * Move the wizard to the given step number (1-based).
     *
     * @param {number} step
     */
    function goToStep(step) {
        if (step < 1 || step > TOTAL_STEPS) {
            return;
        }

        // Hide all steps, show target
        qsa('.wizard-step').forEach((stepEl) => stepEl.classList.remove('active'));
        const target = qs(`.wizard-step[data-step="${step}"]`);
        if (target) {
            target.classList.add('active');
        }

        // Update progress bar
        const pct = Math.round((step / TOTAL_STEPS) * 100);
        const bar = el('wizard-progress-bar');
        const label = el('wizard-step-label');

        if (bar) {
            bar.style.width = `${pct}%`;
            bar.setAttribute('aria-valuenow', pct);
        }
        if (label) {
            label.textContent = txt('JBS_WIZARD_STEP_OF', `Step ${step} of ${TOTAL_STEPS}`);
        }

        // Update step indicator labels
        qsa('.wizard-step-label[data-step]').forEach((lbl) => {
            const s = parseInt(lbl.dataset.step, 10);
            lbl.classList.toggle('fw-bold', s === step);
            lbl.classList.toggle('text-primary', s === step);
            lbl.classList.toggle('text-body-secondary', s !== step);
        });

        // Lazy-load step content when entering certain steps
        if (step === 4) {
            loadPermissions();
        }
        if (step === 5) {
            loadPreview();
        }
        if (step === 6) {
            applyConfiguration();
        }

        currentStep = step;
    }

    // -------------------------------------------------------------------------
    // Step 3 — collect mapping from checkboxes
    // -------------------------------------------------------------------------

    function collectMapping() {
        const result = {};
        qsa('.wizard-group-check:checked').forEach((cb) => {
            const locId = cb.dataset.location;
            const grpId = parseInt(cb.dataset.group, 10);
            if (!result[locId]) {
                result[locId] = [];
            }
            result[locId].push(grpId);
        });
        return result;
    }

    // -------------------------------------------------------------------------
    // Step 4 — admin permissions
    // -------------------------------------------------------------------------

    /**
     * Preset definitions with their language keys.
     */
    const PRESETS = [
        { value: 'none', labelKey: 'JBS_WIZARD_PRESET_NOCHANGE', descKey: 'JBS_WIZARD_PRESET_NOCHANGE_DESC' },
        { value: 'full', labelKey: 'JBS_WIZARD_PRESET_FULL', descKey: 'JBS_WIZARD_PRESET_FULL_DESC' },
        { value: 'editor', labelKey: 'JBS_WIZARD_PRESET_EDITOR', descKey: 'JBS_WIZARD_PRESET_EDITOR_DESC' },
        { value: 'viewer', labelKey: 'JBS_WIZARD_PRESET_VIEWER', descKey: 'JBS_WIZARD_PRESET_VIEWER_DESC' },
    ];

    /**
     * Populate the permissions UI from the current mapping state.
     * No AJAX needed — group data is already in window.ProcWizard.groups.
     */
    function loadPermissions() {
        const container = el('wizard-permissions-container');
        if (!container) {
            return;
        }

        const config = window.ProcWizard || {};
        const mapping = collectMapping();

        // Collect the union of all mapped group IDs
        const mappedGroupIds = new Set();
        Object.values(mapping).forEach((gids) => {
            gids.forEach((gid) => mappedGroupIds.add(gid));
        });

        if (mappedGroupIds.size === 0) {
            container.innerHTML = `<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${txt('JBS_WIZARD_NO_MAPPED_GROUPS')}
            </div>`;
            return;
        }

        // Build group lookup
        const groupMap = (config.groups || []).reduce((m, g) => { m[g.id] = g.title; return m; }, {});

        let html = '<div class="table-responsive"><table class="table table-sm align-middle">';
        html += `<thead class="table-dark"><tr>
                    <th>${txt('JBS_WIZARD_GROUP_NAME')}</th>
                    <th>${txt('JBS_WIZARD_MAPPED_GROUPS_PERMISSIONS')}</th>
                 </tr></thead><tbody>`;

        mappedGroupIds.forEach((gid) => {
            const groupName = groupMap[gid] || `#${gid}`;
            const saved = pendingPermissions[String(gid)] || 'none';

            html += `<tr>
                <td><strong>${escHtml(groupName)}</strong></td>
                <td>
                    <div class="d-flex flex-wrap gap-2">`;

            PRESETS.forEach((preset) => {
                const radioId = `perm_${gid}_${preset.value}`;
                const checked = (saved === preset.value) ? 'checked' : '';
                const label = txt(preset.labelKey, preset.value);
                const desc = txt(preset.descKey, '');

                html += `<div class="form-check form-check-inline" title="${escHtml(desc)}">
                    <input class="form-check-input wizard-perm-radio"
                           type="radio" name="perm_${gid}"
                           id="${radioId}" value="${preset.value}"
                           data-group="${gid}" ${checked}>
                    <label class="form-check-label small" for="${radioId}">${escHtml(label)}</label>
                </div>`;
            });

            html += `</div></td></tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    /**
     * Read selected permission presets from the radio buttons.
     *
     * @returns {Object.<string, string>} groupId → preset
     */
    function collectPermissions() {
        const result = {};
        qsa('.wizard-perm-radio:checked').forEach((radio) => {
            result[String(radio.dataset.group)] = radio.value;
        });
        return result;
    }

    // -------------------------------------------------------------------------
    // Step 5 — preview
    // -------------------------------------------------------------------------

    function loadPreview() {
        const container = el('wizard-preview-container');
        if (!container) {
            return;
        }

        pendingMapping = collectMapping();
        pendingPermissions = collectPermissions();
        const config = window.ProcWizard || {};
        const url = `${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.getStepData&step=5&${config.token}=1&mapping=${encodeURIComponent(JSON.stringify(pendingMapping))}`;

        fetch(url)
            .then((r) => r.json())
            .then((response) => {
                if (!response.success) {
                    container.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
                    return;
                }

                const d = response.data;
                let html = `<div class="row g-3 text-center mb-3">
                    <div class="col-sm-4">
                        <div class="card border-0 bg-body-tertiary py-3">
                            <div class="display-6 fw-bold text-primary">${d.locations}</div>
                            <div class="small text-body-secondary">${txt('JBS_WIZARD_LOCATIONS')}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-body-tertiary py-3">
                            <div class="display-6 fw-bold text-primary">${d.groups}</div>
                            <div class="small text-body-secondary">${txt('JBS_WIZARD_GROUP_ASSIGNMENTS')}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-${d.unmapped_locations > 0 ? 'warning-subtle' : 'body-tertiary'} py-3">
                            <div class="display-6 fw-bold text-${d.unmapped_locations > 0 ? 'warning-emphasis' : 'success'}">${d.unmapped_locations}</div>
                            <div class="small text-body-secondary">${txt('JBS_WIZARD_UNMAPPED_LOCATIONS')}</div>
                        </div>
                    </div>
                </div>`;

                // Mapping detail table
                const mapping = d.mapping || {};
                const locs = (config.locations || []).reduce((m, l) => { m[l.id] = l.title; return m; }, {});
                const grps = (config.groups || []).reduce((m, g) => { m[g.id] = g.title; return m; }, {});
                const locIds = Object.keys(mapping);

                if (locIds.length > 0) {
                    html += `<h6>${txt('JBS_WIZARD_MAPPING_SUMMARY')}</h6>
                             <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>${txt('JBS_WIZARD_LOCATION')}</th>
                                        <th>${txt('JBS_WIZARD_MAPPED_GROUPS')}</th>
                                    </tr>
                                </thead><tbody>`;

                    locIds.forEach((locId) => {
                        const groupIds = mapping[locId] || [];
                        const groupList = groupIds.map((gid) => grps[gid] || `#${gid}`).join(', ');
                        html += `<tr>
                            <td><strong>${escHtml(locs[locId] || locId)}</strong></td>
                            <td>${escHtml(groupList) || '<em class="text-body-secondary">none</em>'}</td>
                        </tr>`;
                    });

                    html += '</tbody></table>';
                }

                // Permissions summary
                const permEntries = Object.entries(pendingPermissions).filter(([, v]) => v !== 'none');
                if (permEntries.length > 0) {
                    const presetLabels = {
                        full: txt('JBS_WIZARD_PRESET_FULL', 'Full Access'),
                        editor: txt('JBS_WIZARD_PRESET_EDITOR', 'Content Editor'),
                        viewer: txt('JBS_WIZARD_PRESET_VIEWER', 'Viewer (Edit Own)'),
                    };

                    html += `<h6 class="mt-3">${txt('JBS_WIZARD_MAPPED_GROUPS_PERMISSIONS', 'Permissions')}</h6>
                             <table class="table table-sm table-bordered">
                                <thead><tr>
                                    <th>${txt('JBS_WIZARD_GROUP_NAME')}</th>
                                    <th>${txt('JBS_WIZARD_MAPPED_GROUPS_PERMISSIONS', 'Permissions')}</th>
                                </tr></thead><tbody>`;

                    permEntries.forEach(([gid, preset]) => {
                        html += `<tr>
                            <td><strong>${escHtml(grps[gid] || '#' + gid)}</strong></td>
                            <td><span class="badge bg-${preset === 'full' ? 'success' : preset === 'viewer' ? 'secondary' : 'info'}">${escHtml(presetLabels[preset] || preset)}</span></td>
                        </tr>`;
                    });

                    html += '</tbody></table>';
                }

                container.innerHTML = html;
            })
            .catch((err) => {
                container.innerHTML = `<div class="alert alert-danger">${txt('JERROR_AN_ERROR_HAS_OCCURRED')}: ${escHtml(err.message)}</div>`;
            });
    }

    // -------------------------------------------------------------------------
    // Step 6 — apply
    // -------------------------------------------------------------------------

    function applyConfiguration() {
        const msgEl = el('wizard-processing-msg');
        const config = window.ProcWizard || {};

        // Build form data
        const formData = new FormData();
        formData.append(config.token, '1');
        formData.append('mapping', JSON.stringify(pendingMapping));
        formData.append('permissions', JSON.stringify(pendingPermissions));

        fetch(`${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.apply`, {
            method: 'POST',
            body: formData,
        })
            .then((r) => r.json())
            .then((response) => {
                if (response.success) {
                    if (msgEl) {
                        msgEl.textContent = txt('JBS_WIZARD_APPLY_SUCCESS');
                    }
                    // Proceed to step 7
                    setTimeout(() => goToStep(7), 1000);
                } else {
                    if (msgEl) {
                        msgEl.textContent = response.message || txt('JBS_WIZARD_APPLY_ERROR');
                    }

                    const container = qs('.wizard-step[data-step="6"] .card-body');
                    if (container) {
                        container.insertAdjacentHTML(
                            'beforeend',
                            `<div class="alert alert-danger mt-3">${escHtml(response.message)}</div>
                             <button type="button" class="btn btn-secondary mt-2" id="wizard-back-from-error">
                                 <i class="fas fa-arrow-left me-1"></i>${txt('JPREV')}
                             </button>`,
                        );
                        const backBtn = el('wizard-back-from-error');
                        if (backBtn) {
                            backBtn.addEventListener('click', () => goToStep(5));
                        }
                    }
                }
            })
            .catch((err) => {
                if (msgEl) {
                    msgEl.textContent = `${txt('JERROR_AN_ERROR_HAS_OCCURRED')}: ${err.message}`;
                }
            });
    }

    // -------------------------------------------------------------------------
    // Dismiss
    // -------------------------------------------------------------------------

    function dismissWizard() {
        const config = window.ProcWizard || {};

        const formData = new FormData();
        formData.append(config.token, '1');

        fetch(`${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.dismiss`, {
            method: 'POST',
            body: formData,
        })
            .then((r) => r.json())
            .then((response) => {
                if (response.success) {
                    window.location.href = `${config.baseUrl}?option=com_proclaim`;
                }
            })
            .catch(() => {
                // Navigate away even on error
                window.location.href = `${config.baseUrl}?option=com_proclaim`;
            });
    }

    // -------------------------------------------------------------------------
    // Utility
    // -------------------------------------------------------------------------

    function escHtml(str) {
        const div = doc.createElement('div');
        div.appendChild(doc.createTextNode(String(str)));
        return div.innerHTML;
    }

    // -------------------------------------------------------------------------
    // Bootstrap seed from saved mapping
    // -------------------------------------------------------------------------

    function seedCheckboxes() {
        const config = window.ProcWizard || {};
        const mapping = config.savedMapping || {};

        Object.keys(mapping).forEach((locId) => {
            const groupIds = mapping[locId] || [];
            groupIds.forEach((grpId) => {
                const cb = doc.getElementById(`map_${locId}_${grpId}`);
                if (cb) {
                    cb.checked = true;
                }
            });
        });
    }

    // -------------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------------

    function init() {
        seedCheckboxes();

        // Next buttons
        qsa('.wizard-next-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                // On step 3, collect mapping before moving forward
                if (currentStep === 3) {
                    pendingMapping = collectMapping();
                }
                // On step 4, collect permissions before moving forward
                if (currentStep === 4) {
                    pendingPermissions = collectPermissions();
                }
                goToStep(currentStep + 1);
            });
        });

        // Prev buttons
        qsa('.wizard-prev-btn').forEach((btn) => {
            btn.addEventListener('click', () => goToStep(currentStep - 1));
        });

        // Dismiss button
        const dismissBtn = el('wizard-dismiss-btn');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                if (window.confirm(txt('JBS_WIZARD_CONFIRM_DISMISS', 'Skip the location setup wizard?'))) {
                    dismissWizard();
                }
            });
        }
    }

    // Wait for DOM ready
    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(document);
