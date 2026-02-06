<?php

/**
 * Archive Messages Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      7.1.0
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmarchive\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useStyle('com_proclaim.general');
?>

<!-- Archive Progress Modal -->
<div class="modal fade" id="archive-progress-modal" tabindex="-1"
     aria-labelledby="archiveModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveModalLabel">
                    <i class="icon-archive me-2" aria-hidden="true"></i>
                    <span class="title-text"><?php echo Text::_('JBS_CMN_ARCHIVE'); ?></span>
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary operation-spinner" role="status">
                        <span class="visually-hidden"><?php echo Text::_('JBS_ADM_LOADING'); ?></span>
                    </div>
                </div>
                <p class="status-text text-center fw-bold mb-2" aria-live="polite"><?php echo Text::_('JBS_ADM_PROCESSING'); ?></p>
                <div class="progress mb-3" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                </div>
                <p class="detail-text text-muted small text-center mb-0" aria-live="polite"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-close-modal" style="display: none;">
                    <i class="icon-checkmark me-1" aria-hidden="true"></i><?php echo Text::_('JCLOSE'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="icon-archive me-2" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_CMN_ARCHIVE'); ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="icon-info-circle me-2" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_ARCHIVE_TIMEFRAME_DESC'); ?>
                </div>

                <form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmarchive'); ?>"
                      method="post" name="adminForm" id="adminForm" class="form-validate">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="jform_timeframe" class="form-label fw-bold">
                                <?php echo Text::_('JBS_ARCHIVE_TIMEFRAME_LABEL'); ?>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <?php echo $this->form->getInput('timeframe'); ?>
                                <span class="input-group-text" id="timeframe-addon">
                                    <span id="interval-label"><?php echo Text::_('JBS_CMN_YEARS'); ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <?php echo Text::_('JBS_ARCHIVE_SWICH_LABEL'); ?>
                            </label>
                            <div class="mt-2">
                                <?php echo $this->form->getInput('switch'); ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2 justify-content-between">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1'); ?>"
                           class="btn btn-secondary">
                            <i class="icon-arrow-left me-1" aria-hidden="true"></i>
                            <?php echo Text::_('JTOOLBAR_BACK'); ?>
                        </a>
                        <button type="button" class="btn btn-primary" id="btn-start-archive">
                            <i class="icon-archive me-1" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_CMN_ARCHIVE'); ?>
                        </button>
                    </div>

                    <input type="hidden" name="option" value="com_proclaim"/>
                    <input type="hidden" name="task" value=""/>
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = '<?php echo Session::getFormToken(); ?>';
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

    switchRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            const labels = {
                'year': '<?php echo Text::_('JBS_CMN_YEARS'); ?>',
                'month': '<?php echo Text::_('JBS_CMN_MONTHS'); ?>',
                'day': '<?php echo Text::_('JBS_CMN_DAYS'); ?>'
            };
            intervalLabel.textContent = labels[this.value] || labels['year'];
        });
    });

    // Archive button click
    document.getElementById('btn-start-archive').addEventListener('click', function() {
        const timeframe = document.getElementById('jform_timeframe').value;
        const switchVal = document.querySelector('input[name="jform[switch]"]:checked')?.value || 'year';

        if (!timeframe || timeframe < 1) {
            Joomla.renderMessages({error: ['<?php echo Text::_('JBS_ARCHIVE_TIMEFRAME_REQUIRED', true); ?>']});
            return;
        }

        // Confirm action
        const intervalText = {
            'year': '<?php echo Text::_('JBS_CMN_YEARS'); ?>',
            'month': '<?php echo Text::_('JBS_CMN_MONTHS'); ?>',
            'day': '<?php echo Text::_('JBS_CMN_DAYS'); ?>'
        };
        const confirmMsg = '<?php echo Text::_('JBS_ARCHIVE_CONFIRM', true); ?>'
            .replace('%s', timeframe + ' ' + intervalText[switchVal]);

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
        statusText.textContent = '<?php echo Text::_('JBS_ADM_PROCESSING'); ?>';
        detailText.textContent = '';
        spinner.style.display = 'inline-block';
        closeBtn.style.display = 'none';
        bsModal.show();

        // Start archive via AJAX
        const url = 'index.php?option=com_proclaim&task=cwmadmin.doArchiveXHR&' + token + '=1' +
            '&timeframe=' + encodeURIComponent(timeframe) +
            '&switch=' + encodeURIComponent(switchVal);

        progressBar.style.width = '50%';
        progressBar.textContent = '50%';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                progressBar.classList.remove('progress-bar-animated');
                spinner.style.display = 'none';

                if (data.success) {
                    progressBar.classList.add('bg-success');
                    statusText.textContent = '<?php echo Text::_('JBS_CMN_OPERATION_SUCCESSFUL'); ?>';
                    detailText.innerHTML = data.message || '';
                } else {
                    progressBar.classList.add('bg-danger');
                    statusText.textContent = '<?php echo Text::_('JBS_ADM_ERROR'); ?>';
                    detailText.textContent = data.message || '';
                }

                closeBtn.style.display = 'inline-block';
            })
            .catch(error => {
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');
                spinner.style.display = 'none';
                statusText.textContent = '<?php echo Text::_('JBS_ADM_ERROR'); ?>';
                detailText.textContent = error.message;
                closeBtn.style.display = 'inline-block';
            });
    });

    // Close button
    closeBtn.addEventListener('click', function() {
        if (bsModal) {
            bsModal.hide();
        }
    });

    // Joomla submitbutton for toolbar
    Joomla.submitbutton = function(task) {
        if (task === 'cwmadmin.back' || task === 'administration.back') {
            window.location.href = '<?php echo Route::_('index.php?option=com_proclaim&view=cwmadmin&layout=edit&id=1', false); ?>';
            return;
        }
        var form = document.getElementById('adminForm');
        if (document.formvalidator.isValid(form)) {
            Joomla.submitform(task, form);
        }
    };
});
</script>
