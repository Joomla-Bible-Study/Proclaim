<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// Protect from unauthorized access
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

if ($this->totalSteps != '0') {
    $pre = $this->doneSteps . ' of ' . $this->totalSteps;
} else {
    $pre = '';
}

if ($this->more) {
    ?>
    <h1><?php
        echo Text::_('JBS_MIG_WORKING'); ?></h1>
    <?php
} else {
    ?>
    <h1><?php
        echo Text::_('JBS_MIG_MIGRATION_DONE'); ?></h1>
    <?php
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->addInlineScript(
    "if (typeof jQuery == 'function') {
	                                    if (typeof jQuery.ui == 'object') {
		                                    jQuery('#nojquerywarning').css('display', 'none');
	                                        }
	                                   }"
);
?>
<div class="p-3">
    <div class="row">
        <div id="install-progress-pane">
            <div class="migration-status">
                <div class="status"><?php
                    echo $pre . ' ' . Text::_('JBS_MIG_PROCESSING') . ' ' . $this->running; ?></div>
            </div>
            <div class="progress" role="progressbar" aria-label="Install Progress" aria-valuenow="<?php echo $this->percentage ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: <?php
                echo $this->percentage ?>%;"><?php
                    echo $this->percentage; ?>%</div>
            </div>
        </div>
        <form action="<?php
        echo Route::_('index.php?option=com_proclaim&view=cwminstall'); ?>" name="adminForm" id="adminForm"
              method="get">
            <?php
            ?>
            <?php
            if ($this->state === 'start') {
                ?>
                <input type="hidden" name="task" value="cwminstall.browse"/>
                <?php
            } else {
                ?>
                <input type="hidden" name="task" value="cwminstall.run"/>
                <?php
            }
            ?>

            <div id="backup-complete">
                <?php
                if (!$this->more) : ?>
                    <div class="alert alert-info">
                        <p><?php
                            echo Text::_('JBS_LBL_REDIRECT_IN_3S');
                            $wa->useScript('form.validate')
                                ->addInlineScript(
                                    "setTimeout(function(){
                                    jQuery('#adminForm').submit()
								}, 3000);"
                                ); ?></p>
                    </div>
                    <?php
                endif; ?>
            </div>
            <?php
            echo HTMLHelper::_('form.token'); ?>
            <input type="hidden" name="option" value="com_proclaim"/>
            <input type="hidden" name="view" value="cwminstall"/>
        </form>
    </div>
</div>
