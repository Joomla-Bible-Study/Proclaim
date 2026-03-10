<?php

/**
 * Migration Progress Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// Protect from unauthorized access
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwminstall\HtmlView $this */

\defined('_JEXEC') or die();

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive');

// Progress text
$progressText = '';
if ($this->totalSteps != '0') {
    $progressText = Text::sprintf('JBS_MIG_STEP_PROGRESS', $this->doneSteps, $this->totalSteps);
}

// Dynamic heading based on install type
$workingKey  = match ($this->installType) {
    'install'  => 'JBS_MIG_WORKING_INSTALL',
    'upgrade'  => 'JBS_MIG_WORKING_UPGRADE',
    default    => 'JBS_MIG_WORKING',
};
$finishedKey = match ($this->installType) {
    'install'  => 'JBS_MIG_DONE_INSTALL',
    'upgrade'  => 'JBS_MIG_DONE_UPGRADE',
    default    => 'JBS_MIG_MIGRATION_DONE',
};

// Determine progress bar color based on percentage
$progressClass = 'bg-primary';
if ($this->percentage >= 100) {
    $progressClass = 'bg-success';
} elseif ($this->percentage >= 75) {
    $progressClass = 'bg-info';
}

// Auto-submit form if more work to do
if ($this->more) {
    $wa->addInlineScript(
        "document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('adminForm').submit();
            }, 2000);
        });"
    );
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <!-- Migration Progress Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-database me-2"></i>
                    <?php echo $this->more ? Text::_($workingKey) : Text::_($finishedKey); ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if ($this->more): ?>
                <!-- Active Migration State -->
                <div class="text-center mb-4">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden"><?php echo Text::_('JBS_MIG_PROCESSING'); ?></span>
                    </div>
                </div>
                <?php else: ?>
                <!-- Completed State -->
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <?php endif; ?>

                <!-- Progress Information -->
                <div class="mb-3">
                    <?php if ($progressText): ?>
                    <p class="text-center fw-bold mb-2"><?php echo $progressText; ?></p>
                    <?php endif; ?>

                    <?php if ($this->running): ?>
                    <p class="text-center text-muted small mb-3">
                        <i class="fas fa-cog fa-spin me-1"></i>
                        <?php echo Text::_('JBS_MIG_PROCESSING'); ?> <?php echo htmlspecialchars($this->running); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 30px;">
                    <div class="progress-bar progress-bar-striped <?php echo $this->more ? 'progress-bar-animated' : ''; ?> <?php echo $progressClass; ?>"
                         role="progressbar"
                         style="width: <?php echo $this->percentage; ?>%;"
                         aria-valuenow="<?php echo $this->percentage; ?>"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        <?php echo $this->percentage; ?>%
                    </div>
                </div>

                <?php if ($this->more): ?>
                <!-- Auto-continue notice -->
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo Text::_('JBS_MIG_AUTO_CONTINUE'); ?>
                </div>
                <?php else: ?>
                <!-- Completion notice -->
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo Text::_('JBS_LBL_REDIRECT_IN_3S'); ?>
                </div>
                <?php
                $wa->addInlineScript(
                    "document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(function() {
                            document.getElementById('adminForm').submit();
                        }, 3000);
                    });"
                );
                    ?>
                <?php endif; ?>
            </div>

            <?php if ($this->more): ?>
            <div class="card-footer text-center">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    <?php echo Text::_('JBS_MIG_DO_NOT_CLOSE'); ?>
                </small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Migration Tips Card -->
        <?php if ($this->more): ?>
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lightbulb me-2"></i><?php echo Text::_('JBS_MIG_TIPS_TITLE'); ?>
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><?php echo Text::_('JBS_MIG_TIP1'); ?></li>
                    <li><?php echo Text::_('JBS_MIG_TIP2'); ?></li>
                    <li><?php echo Text::_('JBS_MIG_TIP3'); ?></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Hidden Form for Auto-Submit -->
        <form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwminstall'); ?>"
              name="adminForm"
              id="adminForm"
              method="get"
              style="display: none;">
            <?php if ($this->state === 'start'): ?>
            <input type="hidden" name="task" value="cwminstall.browse"/>
            <?php else: ?>
            <input type="hidden" name="task" value="cwminstall.run"/>
            <?php endif; ?>
            <?php echo HTMLHelper::_('form.token'); ?>
            <input type="hidden" name="option" value="com_proclaim"/>
            <input type="hidden" name="view" value="cwminstall"/>
        </form>
    </div>
</div>
