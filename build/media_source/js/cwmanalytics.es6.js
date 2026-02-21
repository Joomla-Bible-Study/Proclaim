/**
 * Proclaim Analytics Dashboard — Chart.js initialisation
 *
 * Reads chart data from data-cwm-chart and data-cwm-chart-data attributes on <canvas> elements.
 * Supports: line, bar, doughnut chart types.
 * Respects Joomla Atum dark mode.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */

(() => {
    'use strict';

    /** Detect Joomla Atum dark mode */
    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark'
        || document.documentElement.classList.contains('theme-dark')
        || document.body.classList.contains('dark');

    const PALETTE = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
        '#858796', '#6610f2', '#fd7e14', '#20c9a6', '#e83e8c',
    ];

    const gridColor = () => (isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.07)');
    const textColor = () => (isDark() ? '#c2c7d0' : '#5a5c69');

    /** Build a line chart for the time-series panel */
    function buildLineChart(canvas, raw) {
        const colors = ['#4e73df', '#1cc88a', '#f6c23e'];
        const datasets = (raw.datasets || []).map((ds, i) => ({
            label: ds.label,
            data: ds.data,
            borderColor: colors[i % colors.length],
            backgroundColor: `${colors[i % colors.length]}22`,
            fill: true,
            tension: 0.3,
            pointRadius: raw.labels.length > 60 ? 0 : 3,
        }));

        return new window.Chart(canvas, {
            type: 'line',
            data: { labels: raw.labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { labels: { color: textColor() } },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: {
                        ticks: { color: textColor(), maxTicksLimit: 12 },
                        grid: { color: gridColor() },
                    },
                    y: {
                        ticks: { color: textColor() },
                        grid: { color: gridColor() },
                        beginAtZero: true,
                    },
                },
            },
        });
    }

    /** Build a bar chart for top sermons */
    function buildBarChart(canvas, raw) {
        return new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels: raw.labels,
                datasets: [{
                    label: canvas.closest('.card')?.querySelector('.card-header')?.textContent?.trim() ?? '',
                    data: raw.data,
                    backgroundColor: PALETTE.slice(0, raw.data.length),
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { color: textColor() },
                        grid: { color: gridColor() },
                        beginAtZero: true,
                    },
                    y: {
                        ticks: { color: textColor() },
                        grid: { display: false },
                    },
                },
            },
        });
    }

    /** Build a doughnut chart for referrer / device breakdown */
    function buildDoughnutChart(canvas, raw) {
        return new window.Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: raw.labels,
                datasets: [{
                    data: raw.data,
                    backgroundColor: PALETTE.slice(0, raw.data.length),
                    borderWidth: 2,
                    borderColor: isDark() ? '#1a1a2e' : '#ffffff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: textColor(), boxWidth: 12 },
                    },
                },
            },
        });
    }

    /** Initialise the Platform Stats Sync button */
    function initSyncButton() {
        const btn = document.getElementById('cwm-sync-platform-stats');

        if (!btn) {
            return;
        }

        let servers;

        try {
            servers = JSON.parse(btn.dataset.servers || '[]');
        } catch {
            return;
        }

        if (!servers.length) {
            return;
        }

        const token = btn.dataset.token || '';
        const batchLimit = parseInt(btn.dataset.batchLimit, 10) || 50;

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            const origHtml = btn.innerHTML;
            let totalSynced = 0;
            let totalRemaining = 0;
            let totalErrors = 0;

            for (let i = 0; i < servers.length; i += 1) {
                const srv = servers[i];
                const name = srv.server_name || srv.type;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>'
                    + Joomla.Text._('JBS_ANA_SYNCING') + ' ' + name + ' (' + (i + 1) + '/' + servers.length + ')...';

                try {
                    const url = 'index.php?option=com_proclaim&task=cwmadmin.addonAjax'
                        + '&addon=' + encodeURIComponent(srv.type)
                        + '&action=fetchStats'
                        + '&server_id=' + srv.id
                        + '&batch_limit=' + batchLimit
                        + '&' + token + '=1';

                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-Token': '1' },
                    });

                    if (resp.ok) {
                        const data = await resp.json();

                        if (data && data.data) {
                            totalSynced += (data.data.synced || 0);
                            totalRemaining += (data.data.remaining || 0);
                        }
                    } else {
                        totalErrors += 1;
                    }
                } catch {
                    totalErrors += 1;
                }
            }

            btn.innerHTML = origHtml;
            btn.disabled = false;

            let msg = Joomla.Text._('JBS_ANA_SYNC_COMPLETE') + ' (' + totalSynced + ' videos)';

            if (totalRemaining > 0) {
                msg += ' — ' + totalRemaining + ' ' + Joomla.Text._('JBS_ANA_SYNC_REMAINING');
            }

            if (totalErrors === 0) {
                Joomla.renderMessages({ success: [msg] });
            } else {
                Joomla.renderMessages({
                    warning: [msg + ' (' + totalErrors + ' errors)'],
                });
            }

            // Scroll to the message so the user sees it before reload
            const msgEl = document.getElementById('system-message-container');

            if (msgEl) {
                msgEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // Reload after brief delay so user sees the message
            setTimeout(() => { window.location.reload(); }, 2000);
        });
    }

    /** Initialise all charts on the page */
    function initCharts() {
        if (!window.Chart) {
            return;
        }

        document.querySelectorAll('canvas[data-cwm-chart]').forEach((canvas) => {
            const type = canvas.dataset.cwmChart;
            let raw;

            try {
                raw = JSON.parse(canvas.dataset.cwmChartData || '{}');
            } catch {
                return;
            }

            if (!raw || (!raw.labels?.length && !raw.data?.length)) {
                return;
            }

            switch (type) {
                case 'line':
                    buildLineChart(canvas, raw);
                    break;
                case 'bar':
                    buildBarChart(canvas, raw);
                    break;
                case 'doughnut':
                    buildDoughnutChart(canvas, raw);
                    break;
                default:
                    break;
            }
        });
    }

    /** Initialise the Local / Combined engagement toggle */
    function initEngagementToggle() {
        const localBtn    = document.getElementById('cwm-engage-local');
        const combinedBtn = document.getElementById('cwm-engage-combined');

        if (!localBtn || !combinedBtn) {
            return;
        }

        const canvas    = document.getElementById('cwm-chart-topstudies');
        const localTbl  = document.getElementById('cwm-topstudies-local-tbl');
        const combTbl   = document.getElementById('cwm-topstudies-combined-tbl');
        let chartInst   = null;

        // Find the existing Chart.js instance on this canvas
        if (canvas && window.Chart) {
            chartInst = window.Chart.getChart(canvas);
        }

        function switchView(mode) {
            const isLocal = mode === 'local';
            const btn     = isLocal ? localBtn : combinedBtn;
            let raw;

            try {
                raw = JSON.parse(btn.dataset.chartData || '{}');
            } catch {
                return;
            }

            // Swap button styles
            localBtn.classList.toggle('btn-primary', isLocal);
            localBtn.classList.toggle('btn-outline-secondary', !isLocal);
            combinedBtn.classList.toggle('btn-primary', !isLocal);
            combinedBtn.classList.toggle('btn-outline-secondary', isLocal);

            // Swap tables
            if (localTbl) {
                localTbl.style.display  = isLocal ? '' : 'none';
            }

            if (combTbl) {
                combTbl.style.display   = isLocal ? 'none' : '';
            }

            // Rebuild chart with new data
            if (chartInst && raw.labels) {
                chartInst.data.labels                = raw.labels;
                chartInst.data.datasets[0].data      = raw.data;
                chartInst.data.datasets[0].backgroundColor = PALETTE.slice(0, raw.data.length);
                chartInst.update();
            }
        }

        localBtn.addEventListener('click', () => switchView('local'));
        combinedBtn.addEventListener('click', () => switchView('combined'));
    }

    function init() {
        initCharts();
        initSyncButton();
        initEngagementToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
