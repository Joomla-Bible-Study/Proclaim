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
        qsa('.wizard-step').forEach((el) => el.classList.remove('active'));
        const target = qs(`.wizard-step[data-step="${step}"]`);
        if (target) {
            target.classList.add('active');
        }

        // Update progress bar
        const pct    = Math.round((step / TOTAL_STEPS) * 100);
        const bar    = el('wizard-progress-bar');
        const label  = el('wizard-step-label');

        if (bar) {
            bar.style.width         = pct + '%';
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
            lbl.classList.toggle('text-muted', s !== step);
        });

        // Lazy-load step content when entering certain steps
        if (step === 4) {
            loadTeachers();
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
    // Step 4 — teacher list
    // -------------------------------------------------------------------------

    function loadTeachers() {
        const container = el('wizard-teachers-container');
        if (!container) {
            return;
        }

        const config = window.ProcWizard || {};
        const url    = `${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.getStepData&step=4&${config.token}=1`;

        fetch(url)
            .then((r) => r.json())
            .then((response) => {
                if (!response.success) {
                    container.innerHTML = `<div class="alert alert-danger">${response.message}</div>`;
                    return;
                }

                const teachers = response.data.teachers || [];

                if (teachers.length === 0) {
                    container.innerHTML = `<div class="alert alert-info">${txt('JBS_WIZARD_NO_TEACHERS')}</div>`;
                    return;
                }

                const linked   = teachers.filter((t) => t.user_id > 0);
                const unlinked = teachers.filter((t) => !(t.user_id > 0));

                let html = `<p class="mb-2"><strong>${linked.length}</strong> ${txt('JBS_WIZARD_TEACHERS_LINKED')} &nbsp;|&nbsp;
                             <strong>${unlinked.length}</strong> ${txt('JBS_WIZARD_TEACHERS_UNLINKED')}</p>`;

                html += '<div class="table-responsive"><table class="table table-sm table-striped">';
                html += `<thead class="table-dark"><tr>
                            <th>${txt('JBS_WIZARD_TEACHER')}</th>
                            <th>${txt('JBS_WIZARD_USER_ACCOUNT')}</th>
                         </tr></thead><tbody>`;

                teachers.forEach((t) => {
                    const userCell = t.user_id > 0
                        ? `<span class="badge bg-success">${t.user_name || t.user_id}</span>`
                        : `<span class="badge bg-secondary">${txt('JBS_WIZARD_NOT_LINKED')}</span>`;
                    html += `<tr><td>${escHtml(t.teacher)}</td><td>${userCell}</td></tr>`;
                });

                html += '</tbody></table></div>';

                if (unlinked.length > 0) {
                    html += `<div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${txt('JBS_WIZARD_UNLINKED_HINT')}
                        <a href="${config.baseUrl}?option=com_proclaim&view=cwmteachers" class="alert-link">
                            ${txt('JBS_WIZARD_MANAGE_TEACHERS')}</a>
                    </div>`;
                }

                container.innerHTML = html;
            })
            .catch((err) => {
                container.innerHTML = `<div class="alert alert-danger">${txt('JERROR_AN_ERROR_HAS_OCCURRED')}: ${escHtml(err.message)}</div>`;
            });
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
        const config   = window.ProcWizard || {};
        const url      = `${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.getStepData&step=5&${config.token}=1&mapping=${encodeURIComponent(JSON.stringify(pendingMapping))}`;

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
                        <div class="card border-0 bg-light py-3">
                            <div class="display-6 fw-bold text-primary">${d.locations}</div>
                            <div class="small text-muted">${txt('JBS_WIZARD_LOCATIONS')}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-light py-3">
                            <div class="display-6 fw-bold text-primary">${d.groups}</div>
                            <div class="small text-muted">${txt('JBS_WIZARD_GROUP_ASSIGNMENTS')}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-${d.unmapped_locations > 0 ? 'warning' : 'light'} py-3">
                            <div class="display-6 fw-bold text-${d.unmapped_locations > 0 ? 'dark' : 'success'}">${d.unmapped_locations}</div>
                            <div class="small text-muted">${txt('JBS_WIZARD_UNMAPPED_LOCATIONS')}</div>
                        </div>
                    </div>
                </div>`;

                // Mapping detail table
                const mapping  = d.mapping || {};
                const locs     = (config.locations || []).reduce((m, l) => { m[l.id] = l.title; return m; }, {});
                const grps     = (config.groups || []).reduce((m, g) => { m[g.id] = g.title; return m; }, {});
                const locIds   = Object.keys(mapping);

                if (locIds.length > 0) {
                    html += `<h6>${txt('JBS_WIZARD_MAPPING_SUMMARY')}</h6>
                             <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>${txt('JBS_WIZARD_LOCATION')}</th>
                                        <th>${txt('JBS_WIZARD_MAPPED_GROUPS')}</th>
                                    </tr>
                                </thead><tbody>`;

                    locIds.forEach((locId) => {
                        const groupIds  = mapping[locId] || [];
                        const groupList = groupIds.map((gid) => grps[gid] || `#${gid}`).join(', ');
                        html += `<tr>
                            <td><strong>${escHtml(locs[locId] || locId)}</strong></td>
                            <td>${escHtml(groupList) || '<em class="text-muted">none</em>'}</td>
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
        const msgEl  = el('wizard-processing-msg');
        const config = window.ProcWizard || {};

        // Build form data
        const formData = new FormData();
        formData.append(config.token, '1');
        formData.append('mapping', JSON.stringify(pendingMapping));

        fetch(`${config.baseUrl}?option=com_proclaim&task=cwmlocationwizard.apply`, {
            method: 'POST',
            body:   formData,
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
                        container.insertAdjacentHTML('beforeend',
                            `<div class="alert alert-danger mt-3">${escHtml(response.message)}</div>
                             <button type="button" class="btn btn-secondary mt-2" id="wizard-back-from-error">
                                 <i class="fas fa-arrow-left me-1"></i>${txt('JPREV')}
                             </button>`
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
            body:   formData,
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
        const config  = window.ProcWizard || {};
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
