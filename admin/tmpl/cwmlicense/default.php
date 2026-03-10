<?php

/**
 * License acceptance screen
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Branding header -->
        <div class="text-center mb-4">
            <img src="../media/com_proclaim/images/proclaim.jpg"
                 alt="<?php echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"
                 style="max-width: 120px; height: auto;" />
            <h1 class="h3 mt-3"><?php echo Text::_('JBS_LICENSE_WELCOME'); ?></h1>
            <p class="text-body-secondary">
                <?php echo Text::_('JBS_LICENSE_SUBTITLE'); ?>
                <a href="https://www.christianwebministries.org/" target="_blank" rel="noopener">
                    christianwebministries.org
                </a>
            </p>
        </div>

        <!-- Proclaim License -->
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">
                    <span class="icon-file-alt me-1" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_LICENSE_PROCLAIM_HEADING'); ?>
                </h2>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo Text::_('JBS_LICENSE_PROCLAIM_DESC'); ?></p>
            </div>
        </div>

        <!-- Third-Party Libraries -->
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">
                    <span class="icon-cube me-1" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_LICENSE_THIRDPARTY_HEADING'); ?>
                </h2>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <h3 class="alert-heading h6 mb-1">
                        <span class="icon-exclamation-triangle me-1" aria-hidden="true"></span>
                        Fancybox
                    </h3>
                    <p class="mb-0"><?php echo Text::_('JBS_LICENSE_FANCYBOX_DESC'); ?></p>
                </div>

                <h3 class="h6"><?php echo Text::_('JBS_LICENSE_MIT_HEADING'); ?></h3>
                <p class="text-body-secondary mb-0"><?php echo Text::_('JBS_LICENSE_MIT_DESC'); ?></p>
            </div>
        </div>

        <!-- Accept form -->
        <form action="<?php echo Route::_('index.php?option=com_proclaim&task=cwmlicense.accept'); ?>" method="post">
            <?php echo HTMLHelper::_('form.token'); ?>
            <div class="text-center mt-4 mb-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <span class="icon-check" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_LICENSE_ACCEPT_BUTTON'); ?>
                </button>
            </div>
        </form>

    </div>
</div>
