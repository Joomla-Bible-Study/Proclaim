/**
 * YouTube Status tab interactivity for Admin Center.
 *
 * - "Clear Log" button with confirmation → POST to task=cwmadmin.clearYoutubeLog
 * - Expandable context details (toggle d-none on click)
 * - Level filter dropdown (client-side show/hide rows)
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // --- Level filter ---
    const levelFilter = document.getElementById('yt-log-level-filter');

    if (levelFilter) {
        levelFilter.addEventListener('change', () => {
            const selected = levelFilter.value;
            const rows = document.querySelectorAll('#yt-log-table tbody .yt-log-row, #yt-log-table tbody .yt-log-ctx');

            rows.forEach((row) => {
                if (!selected || row.dataset.level === selected) {
                    // Show data rows; context rows stay hidden unless toggled
                    if (row.classList.contains('yt-log-row')) {
                        row.style.display = '';
                    } else if (!row.classList.contains('d-none')) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // --- Expand/collapse context details ---
    document.querySelectorAll('.yt-log-toggle-ctx').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dataRow = btn.closest('tr');

            if (!dataRow) {
                return;
            }

            const ctxRow = dataRow.nextElementSibling;

            if (ctxRow && ctxRow.classList.contains('yt-log-ctx')) {
                ctxRow.classList.toggle('d-none');
                ctxRow.style.display = ctxRow.classList.contains('d-none') ? 'none' : '';
            }
        });
    });

    // --- Reset quota counter buttons ---
    document.querySelectorAll('.btn-reset-yt-quota').forEach((btn) => {
        btn.addEventListener('click', () => {
            const msg = Joomla.Text._('JBS_ADM_YOUTUBE_QUOTA_RESET_CONFIRM')
                || 'Reset the quota counter for this server?';

            if (!window.confirm(msg)) {
                return;
            }

            const serverId = btn.dataset.serverId;
            const tokenName = document.querySelector('input[type="hidden"][value="1"][name]')?.name || '';

            const url = `index.php?option=com_proclaim&task=cwmadmin.resetYoutubeQuotaXHR&server_id=${serverId}&${tokenName}=1`;

            btn.disabled = true;

            fetch(url)
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        // Update the card's progress bar and text
                        const card = btn.closest('.card-body');

                        if (card) {
                            const progressBar = card.querySelector('.progress-bar');
                            const progressText = card.querySelector('.card-text');
                            const progressContainer = card.querySelector('.progress');

                            if (progressBar) {
                                progressBar.style.width = '0%';
                                progressBar.textContent = '0%';
                                progressBar.className = 'progress-bar bg-success';
                            }

                            if (progressContainer) {
                                progressContainer.setAttribute('aria-valuenow', '0');
                            }

                            if (progressText) {
                                const budget = progressText.textContent.match(/of\s+([\d,]+)/);
                                const budgetStr = budget ? budget[1] : '10,000';

                                progressText.textContent = `0 of ${budgetStr} units used today`;
                            }
                        }

                        Joomla.renderMessages({message: [
                            Joomla.Text._('JBS_ADM_YOUTUBE_QUOTA_RESET_SUCCESS') || 'Quota counter has been reset.'
                        ]});
                    } else {
                        Joomla.renderMessages({error: [data.error || 'Reset failed']});
                    }
                })
                .catch(() => {
                    Joomla.renderMessages({error: ['Network error — please try again.']});
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    });

    // --- Clear log button ---
    const clearBtn = document.getElementById('btn-clear-youtube-log');

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            const msg = Joomla.Text._('JBS_ADM_YOUTUBE_LOG_CLEAR_CONFIRM')
                || 'Are you sure you want to clear all YouTube log entries?';

            if (!window.confirm(msg)) {
                return;
            }

            // Submit via hidden form to the controller task
            const form = document.getElementById('adminForm');

            if (form) {
                const taskInput = form.querySelector('input[name="task"]');

                if (taskInput) {
                    taskInput.value = 'cwmadmin.clearYoutubeLog';
                    form.submit();
                }
            }
        });
    }
});
