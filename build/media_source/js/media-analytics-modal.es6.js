/**
 * Quick-stats analytics modal for Messages and Media Files list views.
 *
 * Handles click events on [data-cwm-analytics-study] elements, fetches
 * analytics data via AJAX, and renders a KPI strip + media table inside
 * a Bootstrap 5 modal.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */
(() => {
    'use strict';

    const T = (key) => {
        if (window.Joomla && Joomla.Text && Joomla.Text._) {
            return Joomla.Text._(key) || key;
        }
        return key;
    };

    const fmt = (n) => Number(n || 0).toLocaleString();

    /**
     * Build the KPI strip HTML (4 colored stat boxes).
     */
    function renderKpi(kpi) {
        const cards = [
            { icon: 'icon-eye',      label: 'JBS_ANA_VIEWS',     value: kpi.views,     cls: 'text-primary' },
            { icon: 'icon-play',     label: 'JBS_ANA_PLAYS',     value: kpi.plays,     cls: 'text-success' },
            { icon: 'icon-download', label: 'JBS_ANA_DOWNLOADS', value: kpi.downloads, cls: 'text-warning' },
            { icon: 'icon-user',     label: 'JBS_ANA_UNIQUE_SESSIONS', value: kpi.sessions, cls: 'text-info' },
        ];

        return '<div class="d-flex flex-wrap gap-2 mb-3">' + cards.map((c) =>
            '<div class="flex-fill" style="min-width:100px">'
            + '<div class="card text-center h-100">'
            + '<div class="card-body py-2">'
            + '<i class="' + c.icon + ' ' + c.cls + ' mb-1" aria-hidden="true"></i>'
            + '<div class="fw-bold fs-5 ' + c.cls + '">' + fmt(c.value) + '</div>'
            + '<div class="text-muted small">' + T(c.label) + '</div>'
            + '<span class="text-muted" style="font-size:.65rem">' + T('JBS_ANA_MODAL_PERIOD_LABEL') + '</span>'
            + '</div></div></div>'
        ).join('') + '</div>';
    }

    /**
     * Build the media files table HTML.
     */
    function renderMediaTable(media) {
        if (!media || media.length === 0) {
            return '';
        }

        const hasPlatform = media.some((m) => Number(m.external_plays || 0) > 0);

        let thead = '<thead><tr>'
            + '<th>#</th>'
            + '<th>' + T('JBS_ANA_MODAL_MEDIA_TABLE_SERVER') + '</th>'
            + '<th>' + T('JBS_ANA_MODAL_MEDIA_TABLE_LABEL') + '</th>'
            + '<th class="text-end">' + T('JBS_ANA_ALL_TIME_PLAYS') + '</th>'
            + '<th class="text-end">' + T('JBS_ANA_PERIOD_PLAYS') + '</th>'
            + '<th class="text-end">' + T('JBS_ANA_DOWNLOADS') + '</th>';

        if (hasPlatform) {
            thead += '<th class="text-end">' + T('JBS_MED_EXTERNAL_PLAYS') + '</th>';
        }

        thead += '</tr></thead>';

        let tbody = '<tbody>';

        media.forEach((m, idx) => {
            const esc = (s) => {
                const el = document.createElement('span');
                el.textContent = s || '';
                return el.innerHTML;
            };

            tbody += '<tr>'
                + '<td>' + (idx + 1) + '</td>'
                + '<td>' + esc(m.server_name || '—') + '</td>'
                + '<td>' + esc(m.label || '—') + '</td>'
                + '<td class="text-end">' + fmt(m.all_time_plays) + '</td>'
                + '<td class="text-end">' + fmt(m.period_plays) + '</td>'
                + '<td class="text-end">' + fmt(m.period_downloads) + '</td>';

            if (hasPlatform) {
                tbody += '<td class="text-end">' + fmt(m.external_plays) + '</td>';
            }

            tbody += '</tr>';
        });

        tbody += '</tbody>';

        return '<h6 class="fw-semibold mb-2"><i class="icon-play me-1" aria-hidden="true"></i>'
            + T('JBS_ANA_MEDIA_FILES') + '</h6>'
            + '<table class="table table-sm table-hover mb-0">'
            + thead + tbody + '</table>';
    }

    /**
     * Render the full modal body content.
     */
    function renderBody(data) {
        const info = data.info;
        let html = '';

        // Title + date
        if (info) {
            html += '<p class="text-muted small mb-1">'
                + (info.study_date || '') + (info.series_title ? ' &middot; ' + escHtml(info.series_title) : '')
                + '</p>';
        }

        // All-time KPI row
        if (info && info.all_time_views > 0) {
            html += '<p class="small text-muted mb-2">'
                + '<i class="icon-eye me-1" aria-hidden="true"></i>' + T('JBS_ANA_MODAL_ALL_TIME') + ': '
                + fmt(info.all_time_views) + ' ' + T('JBS_ANA_VIEWS')
                + '</p>';
        }

        // Period KPI strip
        html += renderKpi(data.kpi);

        // Media table
        html += renderMediaTable(data.media);

        return html;
    }

    function escHtml(str) {
        const el = document.createElement('span');
        el.textContent = str || '';
        return el.innerHTML;
    }

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
     * Initialize delegated click handler.
     */
    function init() {
        const modalEl = document.getElementById('cwm-analytics-modal');

        if (!modalEl) {
            return;
        }

        const modal    = new bootstrap.Modal(modalEl);
        const bodyEl   = document.getElementById('cwm-analytics-modal-body');
        const titleEl  = document.getElementById('cwm-analytics-modal-label');
        const linkEl   = document.getElementById('cwm-analytics-modal-fulllink');

        const spinnerHtml = bodyEl.innerHTML;

        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-cwm-analytics-study]');

            if (!trigger) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const studyId = parseInt(trigger.dataset.cwmAnalyticsStudy, 10);

            if (!studyId || studyId <= 0) {
                return;
            }

            // Reset and show modal
            titleEl.textContent = T('JBS_ANA_MODAL_LOADING');
            bodyEl.innerHTML    = spinnerHtml;
            linkEl.href         = 'index.php?option=com_proclaim&view=cwmanalytics&drilldown=message&id='
                + studyId + '&preset=30d';
            modal.show();

            // Build AJAX URL with CSRF token
            const token = getToken();
            const url = 'index.php?option=com_proclaim&task=cwmanalytics.getStudyAnalyticsXHR'
                + '&study_id=' + studyId
                + '&' + token + '=1';

            window.ProclaimFetch.fetchJson(url, {}, {
                timeout: window.ProclaimFetch.ADMIN_TIMEOUT,
                retries: 1,
            }).then((result) => {
                if (!result.success) {
                    bodyEl.innerHTML = '<div class="alert alert-warning">'
                        + escHtml(result.message || T('JBS_ANA_NO_DATA'))
                        + '</div>';
                    return;
                }

                const data = result.data;

                // Update title
                if (data.info && data.info.title) {
                    titleEl.textContent = T('JBS_ANA_MODAL_TITLE').replace('%s', data.info.title);
                }

                // Check if there's any data at all
                const kpi = data.kpi || {};
                const hasData = (kpi.views || 0) + (kpi.plays || 0) + (kpi.downloads || 0) + (kpi.sessions || 0) > 0
                    || (data.media && data.media.length > 0);

                if (!hasData) {
                    bodyEl.innerHTML = '<div class="alert alert-info">'
                        + '<i class="icon-info-circle me-1" aria-hidden="true"></i>'
                        + T('JBS_ANA_MODAL_NO_DATA')
                        + '</div>';
                    return;
                }

                bodyEl.innerHTML = renderBody(data);
            }).catch((err) => {
                bodyEl.innerHTML = '<div class="alert alert-danger">'
                    + escHtml(err.message || 'Request failed')
                    + '</div>';
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
