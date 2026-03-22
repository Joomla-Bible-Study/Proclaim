<?php

/**
 * Admin Form - Asset Check/Fix
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmassets\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('bootstrap.modal')
    ->registerAndUseScript(
        'proclaim.asset-fix',
        'com_proclaim/asset-fix.min.js',
        ['version' => 'auto'],
        ['defer'   => true],
        ['core']
    );

// Add language strings for JavaScript
Text::script('JBS_CMN_PROCESSING');
Text::script('JBS_ADM_CHECKING_ASSETS');
Text::script('JBS_ADM_FIXING_ASSETS');
Text::script('JBS_ADM_FIX_COMPLETE');
Text::script('JBS_ADM_REBUILDING_TREE');
Text::script('JBS_CMN_OPERATION_SUCCESSFUL');
Text::script('JBS_CMN_ERROR');
Text::script('JCANCEL');
Text::script('JCLOSE');
?>

<!-- Skip Navigation Link (WCAG 2.4.1 AAA) -->
<a href="#main-content" class="visually-hidden-focusable skip-link position-absolute p-3 bg-primary text-white" style="z-index: 1050; top: 0; left: 0;">
    <?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?>
</a>

<main id="main-content" role="main" aria-label="<?php echo Text::_('JBS_ADM_ASSET_TABLE_NAME'); ?>">

<!-- Welcome Section -->
<section aria-label="<?php echo Text::_('JBS_ADM_ASSET_TABLE_NAME'); ?>" class="card border-primary mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-auto d-none d-md-block" aria-hidden="true">
                <span class="fa-stack fa-2x text-primary">
                    <i class="fa-solid fa-circle fa-stack-2x" style="opacity: 0.2;"></i>
                    <i class="fa-solid fa-shield-halved fa-stack-1x"></i>
                </span>
            </div>
            <div class="col">
                <h1 class="h4 mb-1"><?php echo Text::_('JBS_ADM_ASSET_TABLE_NAME'); ?></h1>
                <p class="mb-0 small"><?php echo Text::_('JBS_ADM_ASSET_EXPLANATION'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Asset Status Table -->
<section aria-labelledby="status-heading" class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 id="status-heading" class="card-title h5 mb-0">
                <i class="fa-solid fa-table me-2" aria-hidden="true"></i><?php echo Text::_('JBS_ADM_ASSET_STATUS'); ?>
            </h2>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-proclaim-action="refresh">
                    <i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i><?php echo Text::_('JBS_CMN_REFRESH'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-proclaim-action="fix">
                    <i class="fa-solid fa-wrench me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ADM_FIX'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="asset-status-table">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 25%;"><?php echo Text::_('JBS_ADM_TABLENAMES'); ?></th>
                        <th class="text-center"><?php echo Text::_('JBS_ADM_ROWCOUNT'); ?></th>
                        <th class="text-center"><?php echo Text::_('JBS_ADM_NULLROWS'); ?></th>
                        <th class="text-center"><?php echo Text::_('JBS_ADM_MATCHROWS'); ?></th>
                        <th class="text-center"><?php echo Text::_('JBS_ADM_ARULESROWS'); ?></th>
                        <th class="text-center"><?php echo Text::_('JBS_ADM_NOMATCHROWS'); ?></th>
                    </tr>
                </thead>
                <tbody id="asset-status-body">
                    <?php if (!empty($this->assets)) : ?>
                        <?php foreach ($this->assets as $asset) : ?>
                            <tr>
                                <td><?php echo Text::_($asset['realname']); ?></td>
                                <td class="text-center"><?php echo $asset['numrows']; ?></td>
                                <td class="text-center">
                                    <span class="<?php echo $asset['nullrows'] > 0 ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo $asset['nullrows']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="<?php echo $asset['matchrows'] > 0 ? 'text-success' : ''; ?>">
                                        <?php echo $asset['matchrows']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="<?php echo $asset['arulesrows'] > 0 ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo $asset['arulesrows']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="<?php echo $asset['nomatchrows'] > 0 ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo $asset['nomatchrows']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden"><?php echo Text::_('JBS_CMN_LOADING'); ?></span>
                                </div>
                                <span class="ms-2"><?php echo Text::_('JBS_ADM_CHECKING_ASSETS'); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Legend -->
<section class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h2 class="card-title h6 mb-0">
            <i class="fa-solid fa-circle-info me-2" aria-hidden="true"></i><?php echo Text::_('JBS_CMN_LEGEND'); ?>
        </h2>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><strong><?php echo Text::_('JBS_ADM_ROWCOUNT'); ?>:</strong> <?php echo Text::_('JBS_ADM_ROWCOUNT_DESC'); ?></li>
                    <li><span class="text-danger"><?php echo Text::_('JBS_ADM_NULLROWS'); ?>:</span> <?php echo Text::_('JBS_ADM_NULLROWS_DESC'); ?></li>
                    <li><span class="text-success"><?php echo Text::_('JBS_ADM_MATCHROWS'); ?>:</span> <?php echo Text::_('JBS_ADM_MATCHROWS_DESC'); ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled mb-0">
                    <li><span class="text-danger"><?php echo Text::_('JBS_ADM_ARULESROWS'); ?>:</span> <?php echo Text::_('JBS_ADM_ARULESROWS_DESC'); ?></li>
                    <li><span class="text-danger"><?php echo Text::_('JBS_ADM_NOMATCHROWS'); ?>:</span> <?php echo Text::_('JBS_ADM_NOMATCHROWS_DESC'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</section>

</main>

<!-- Fix Assets Modal -->
<div class="modal fade" id="fixAssetsModal" tabindex="-1" aria-labelledby="fixAssetsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="fixAssetsModalLabel">
                    <i class="fa-solid fa-wrench me-2" aria-hidden="true"></i><?php echo Text::_('JBS_ADM_FIX'); ?>
                </h5>
            </div>
            <div class="modal-body">
                <div id="fix-status-message" class="mb-3 text-center">
                    <i class="fa-solid fa-spinner fa-spin me-2" aria-hidden="true"></i>
                    <span id="fix-status-text"><?php echo Text::_('JBS_CMN_PROCESSING'); ?></span>
                </div>
                <div class="progress mb-3" style="height: 25px;">
                    <div id="fix-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span id="fix-progress-text">0%</span>
                    </div>
                </div>
                <div id="fix-details" class="small text-muted text-center"></div>
            </div>
            <div class="modal-footer" id="fix-modal-footer" style="display: none;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-check me-2" aria-hidden="true"></i><?php echo Text::_('JCLOSE'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo HTMLHelper::_('form.token'); ?>
