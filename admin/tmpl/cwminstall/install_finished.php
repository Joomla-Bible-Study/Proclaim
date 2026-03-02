<?php

/**
 * Migration Finished Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwminstall\HtmlView $this */

// Clear Session after finish
$session = Factory::getApplication()->getSession();
$session->set('migration_stack', '', 'CWM');

// Dynamic finished heading based on install type
$finishedKey = match ($this->installType) {
    'install'  => 'JBS_MIG_DONE_INSTALL',
    'upgrade'  => 'JBS_MIG_DONE_UPGRADE',
    default    => 'JBS_MIG_MIGRATION_DONE',
};
?>

<div class="row">
    <!-- Success Header -->
    <div class="col-12 mb-4">
        <?php if (!empty($errors)): ?>
        <!-- Error State -->
        <div class="alert alert-danger">
            <h4 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo Text::_('JBS_MIG_MYSQL_ERRORS'); ?>
            </h4>
            <p><?php echo Text::_('JBS_MIG_MYSQL_ERRORS_DESC'); ?></p>
            <hr>
            <div class="small">
                <?php echo implode('<br>', $errors); ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Success State -->
        <div class="card bg-success text-white">
            <div class="card-body text-center py-4">
                <i class="fas fa-check-circle mb-3" style="font-size: 4rem;"></i>
                <h2 class="card-title mb-2">
                    <?php echo Text::sprintf('JBS_INS_INSTALLATION_RESULTS', Text::_($finishedKey)); ?>
                </h2>
                <p class="card-text mb-0">
                    <?php echo Text::_('JBS_MIG_SUCCESS_DESC'); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Extension Status -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-puzzle-piece me-2"></i>
                    <?php echo Text::_('JBS_MIG_EXTENSION_STATUS'); ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo Text::_('JBS_MIG_EXTENSION'); ?></th>
                            <th><?php echo Text::_('JBS_MIG_TYPE'); ?></th>
                            <th class="text-center"><?php echo Text::_('JSTATUS'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Component -->
                        <tr>
                            <td>
                                <i class="fas fa-cube me-2 text-primary"></i>
                                Proclaim Component
                            </td>
                            <td><span class="badge bg-primary">Component</span></td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i><?php echo Text::_('JBS_MIG_INSTALLED'); ?>
                                </span>
                            </td>
                        </tr>

                        <!-- Modules -->
                        <?php if (\count($this->status->cwmmodules)): ?>
                            <?php foreach ($this->status->cwmmodules as $module): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-th-large me-2 text-info"></i>
                                    <?php echo Text::_(strtoupper($module['name'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">Module</span>
                                    <small class="text-muted">(<?php echo ucfirst($module['client']); ?>)</small>
                                </td>
                                <td class="text-center">
                                    <?php if ($module['result']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i><?php echo Text::_('JBS_MIG_INSTALLED'); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i><?php echo Text::_('JBS_MIG_NOT_INSTALLED'); ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Plugins -->
                        <?php if (\count($this->status->cwmplugins)): ?>
                            <?php foreach ($this->status->cwmplugins as $plugin): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-plug me-2 text-warning"></i>
                                    <?php echo Text::_(strtoupper($plugin['name'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">Plugin</span>
                                    <small class="text-muted">(<?php echo ucfirst($plugin['group']); ?>)</small>
                                </td>
                                <td class="text-center">
                                    <?php if ($plugin['result']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i><?php echo Text::_('JBS_MIG_INSTALLED'); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i><?php echo Text::_('JBS_MIG_NOT_INSTALLED'); ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Next Steps Card -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tasks me-2"></i>
                    <?php echo Text::_('JBS_MIG_NEXT_STEPS'); ?>
                </h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2"><?php echo Text::_('JBS_MIG_NEXT_STEP1'); ?></li>
                    <li class="mb-2"><?php echo Text::_('JBS_MIG_NEXT_STEP2'); ?></li>
                    <li class="mb-2"><?php echo Text::_('JBS_MIG_NEXT_STEP3'); ?></li>
                    <li><?php echo Text::_('JBS_MIG_NEXT_STEP4'); ?></li>
                </ol>
            </div>
        </div>

        <!-- Action Button -->
        <div class="d-grid gap-2 mb-4">
            <a href="<?php echo Route::_('index.php?option=com_proclaim'); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i>
                <?php echo Text::_('JBS_INS_CLICK_TO_FINISH'); ?>
            </a>
        </div>
    </div>

    <!-- Support & Resources Sidebar -->
    <div class="col-12 col-lg-4">
        <!-- Support Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-life-ring me-2"></i>
                    <?php echo Text::_('JBS_MIG_SUPPORT'); ?>
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <a href="https://www.christianwebministries.org/support/user-help-forum.html"
                           target="_blank"
                           class="text-decoration-none">
                            <i class="fas fa-comments me-2 text-primary"></i>
                            <?php echo Text::_('JBS_INS_VISIT_FORUM'); ?>
                            <i class="fas fa-external-link-alt ms-1 small"></i>
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="https://www.christianwebministries.org/documentation.html"
                           target="_blank"
                           class="text-decoration-none">
                            <i class="fas fa-book me-2 text-success"></i>
                            <?php echo Text::_('JBS_INS_VISIT_DOCUMENTATION'); ?>
                            <i class="fas fa-external-link-alt ms-1 small"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.christianwebministries.org"
                           target="_blank"
                           class="text-decoration-none">
                            <i class="fas fa-globe me-2 text-info"></i>
                            <?php echo Text::_('JBS_INS_GET_MORE_HELP'); ?>
                            <i class="fas fa-external-link-alt ms-1 small"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Post-Install Notice -->
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>
                    <?php echo Text::_('JBS_MIG_POST_INSTALL_NOTICE'); ?>
                </h5>
            </div>
            <div class="card-body">
                <p class="small mb-0">
                    <?php echo Text::_('JBS_MIG_POST_INSTALL_DESC'); ?>
                </p>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-4 text-muted small">
            <p class="mb-0">
                <?php echo Text::_('JBS_INS_TITLE'); ?> &copy;
                <a href="https://www.christianwebministries.org" target="_blank" class="text-decoration-none">
                    ChristianWebMinistries.org
                </a>
            </p>
        </div>
    </div>
</div>
