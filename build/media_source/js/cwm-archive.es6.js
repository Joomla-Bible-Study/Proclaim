/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.2.0
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const config = Joomla.getOptions('com_proclaim.archive') || {};
    const token = config.token || '';
    const backUrl = config.backUrl || 'index.php?option=com_proclaim&view=cwmadmin';

    const modal = document.getElementById('archive-progress-modal');
    const progressBar = modal.querySelector('.progress-bar');
    const statusText = modal.querySelector('.status-text');
    const detailText = modal.querySelector('.detail-text');
    const spinner = modal.querySelector('.operation-spinner');
    const closeBtn = modal.querySelector('.btn-close-modal');
    let bsModal = null;

    // Update interval label when radio changes
    const switchRadios = document.querySelectorAll('input[name="jform[switch]"]');
    const intervalLabel = document.getElementById('interval-label');

    const intervalLabels = {
        year: Joomla.Text._('JBS_CMN_YEARS'),
        month: Joomla.Text._('JBS_CMN_MONTHS'),
        day: Joomla.Text._('JBS_CMN_DAYS'),
    };

    switchRadios.forEach((radio) => {
        radio.addEventListener('change', function () {
            intervalLabel.textContent = intervalLabels[this.value] || intervalLabels.year;
        });
    });

    // Archive button click
    document.getElementById('btn-start-archive').addEventListener('click', () => {
        const timeframe = document.getElementById('jform_timeframe').value;
        const switchEl = document.querySelector('input[name="jform[switch]"]:checked');
        const switchVal = switchEl ? switchEl.value : 'year';

        if (!timeframe || timeframe < 1) {
            Joomla.renderMessages({ error: [Joomla.Text._('JBS_ARCHIVE_TIMEFRAME_REQUIRED')] });
            return;
        }

        // Confirm action
        const confirmMsg = Joomla.Text._('JBS_ARCHIVE_CONFIRM')
            .replace('%s', `${timeframe} ${intervalLabels[switchVal]}`);

        if (!confirm(confirmMsg)) {
            return;
        }

        // Show modal
        if (!bsModal) {
            bsModal = new bootstrap.Modal(modal);
        }
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        progressBar.classList.remove('bg-success', 'bg-danger');
        progressBar.classList.add('progress-bar-animated');
        statusText.textContent = Joomla.Text._('JBS_ADM_PROCESSING');
        detailText.textContent = '';
        spinner.style.display = 'inline-block';
        closeBtn.style.display = 'none';
        bsModal.show();

        // Start archive via AJAX
        const url = `index.php?option=com_proclaim&task=cwmadmin.doArchiveXHR&${token}=1`
            + `&timeframe=${encodeURIComponent(timeframe)}`
            + `&switch=${encodeURIComponent(switchVal)}`;

        progressBar.style.width = '50%';
        progressBar.textContent = '50%';

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                progressBar.classList.remove('progress-bar-animated');
                spinner.style.display = 'none';

                if (data.success) {
                    progressBar.classList.add('bg-success');
                    statusText.textContent = Joomla.Text._('JBS_CMN_OPERATION_SUCCESSFUL');
                    detailText.innerHTML = data.message || '';
                } else {
                    progressBar.classList.add('bg-danger');
                    statusText.textContent = Joomla.Text._('JBS_ADM_ERROR');
                    detailText.textContent = data.message || '';
                }

                closeBtn.style.display = 'inline-block';
            })
            .catch((error) => {
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');
                spinner.style.display = 'none';
                statusText.textContent = Joomla.Text._('JBS_ADM_ERROR');
                detailText.textContent = error.message;
                closeBtn.style.display = 'inline-block';
            });
    });

    // Close button
    closeBtn.addEventListener('click', () => {
        if (bsModal) {
            bsModal.hide();
        }
    });

    // Joomla submitbutton for toolbar
    Joomla.submitbutton = (task) => {
        if (task === 'cwmadmin.back' || task === 'administration.back') {
            window.location.href = backUrl;
            return;
        }
        const form = document.getElementById('adminForm');
        if (document.formvalidator.isValid(form)) {
            Joomla.submitform(task, form);
        }
    };
});
