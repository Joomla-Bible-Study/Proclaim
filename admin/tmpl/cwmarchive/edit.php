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
    ->useScript('com_proclaim.cwm-archive')
    ->useStyle('com_proclaim.general');

$this->getDocument()->addScriptOptions('com_proclaim.archive', [
    'token'   => Session::getFormToken(),
    'backUrl' => Route::_('index.php?option=com_proclaim&view=cwmadmin', false),
]);
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
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmadmin'); ?>"
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
