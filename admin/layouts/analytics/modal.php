<?php

/**
 * Quick-stats analytics modal shell.
 *
 * Included in Messages and Media Files list templates. The modal body is
 * populated via JavaScript after an AJAX call to the analytics controller.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

?>
<div class="modal fade" id="cwm-analytics-modal" tabindex="-1"
     aria-labelledby="cwm-analytics-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:960px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cwm-analytics-modal-label">
                    <?php echo Text::_('JBS_ANA_ANALYTICS'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="<?php echo Text::_('JBS_ANA_MODAL_CLOSE'); ?>"></button>
            </div>
            <div class="modal-body" id="cwm-analytics-modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php echo Text::_('JBS_ANA_MODAL_LOADING'); ?></span>
                    </div>
                    <p class="mt-2 text-muted"><?php echo Text::_('JBS_ANA_MODAL_LOADING'); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="cwm-analytics-modal-fulllink" class="btn btn-primary btn-sm">
                    <i class="icon-chart-line me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_MODAL_VIEW_FULL'); ?>
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <?php echo Text::_('JBS_ANA_MODAL_CLOSE'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
