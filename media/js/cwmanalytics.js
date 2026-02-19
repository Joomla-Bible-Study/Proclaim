(function () {
    'use strict';

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

        /** Detect Joomla Atum dark mode */
        const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
            document.documentElement.classList.contains('theme-dark') ||
            document.body.classList.contains('dark');

        const PALETTE = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#6610f2', '#fd7e14', '#20c9a6', '#e83e8c',
        ];

        const gridColor = () => isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.07)';
        const textColor = () => isDark() ? '#c2c7d0' : '#5a5c69';

        /** Build a line chart for the time-series panel */
        function buildLineChart(canvas, raw) {
            const colors = ['#4e73df', '#1cc88a', '#f6c23e'];
            const datasets = (raw.datasets || []).map((ds, i) => ({
                label:           ds.label,
                data:            ds.data,
                borderColor:     colors[i % colors.length],
                backgroundColor: colors[i % colors.length] + '22',
                fill:            true,
                tension:         0.3,
                pointRadius:     raw.labels.length > 60 ? 0 : 3,
            }));

            return new window.Chart(canvas, {
                type: 'line',
                data: { labels: raw.labels, datasets },
                options: {
                    responsive:          true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { labels: { color: textColor() } },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: {
                        x: {
                            ticks:   { color: textColor(), maxTicksLimit: 12 },
                            grid:    { color: gridColor() },
                        },
                        y: {
                            ticks:   { color: textColor() },
                            grid:    { color: gridColor() },
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
                    labels:   raw.labels,
                    datasets: [{
                        label:           canvas.closest('.card')?.querySelector('.card-header')?.textContent?.trim() ?? '',
                        data:            raw.data,
                        backgroundColor: PALETTE.slice(0, raw.data.length),
                    }],
                },
                options: {
                    indexAxis:           'y',
                    responsive:          true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: { color: textColor() },
                            grid:  { color: gridColor() },
                            beginAtZero: true,
                        },
                        y: {
                            ticks: { color: textColor() },
                            grid:  { display: false },
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
                    labels:   raw.labels,
                    datasets: [{
                        data:            raw.data,
                        backgroundColor: PALETTE.slice(0, raw.data.length),
                        borderWidth:     2,
                        borderColor:     isDark() ? '#1a1a2e' : '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels:   { color: textColor(), boxWidth: 12 },
                        },
                    },
                },
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
                } catch (e) {
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
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    })();

})();
