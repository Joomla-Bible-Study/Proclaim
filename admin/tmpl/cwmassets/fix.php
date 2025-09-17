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

$wa = $this->document->getWebAssetManager();
$wa->addInlineScript(
    "if (typeof jQuery == 'function')
	{
		if (typeof jQuery.ui == 'object')
		{
			jQuery('#nojquerywarning').css('display', 'none')
		}
	}"
)
?>
<div class="p-3">
    <div class="row">
        <?php
        if ($this->more) {
            ?>
            <h1><?php
                echo Text::_('JBS_FIXASSETS_WORKING'); ?></h1>
            <?php
        } else {
            ?>
            <h1><?php
                echo Text::_('JBS_FIXASSETS_DONE'); ?></h1>
            <?php
        }
?>
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $this->percentage; ?>%" aria-valuenow="<?php echo $this->percentage; ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: <?php
                echo $this->percentage ?>%;"><?php
                echo $this->percentage; ?>%</div>
        </div>
        <form action="<?php
Route::_('index.php?option=com_proclaim&view=cwmassets'); ?>" name="adminForm"
              id="adminForm" class="form-inline">
            <?php if ($this->state === 'start') { ?>
                <input type="hidden" name="task" value="cwmassets.browse"/>
                <?php
            } elseif ($this->more === true) { ?>
                <input type="hidden" name="task" value="cwmassets.run"/>
                <?php
            } ?>
            <?php
            if (!$this->more) : ?>
                <div class="alert alert-info">
                    <p><?php
                        echo Text::_('Will refresh go back to Assets check in 3 seconds. If not press back button.');
                $wa->useScript('form.validate')
                    ->addInlineScript(
                        "setTimeout(function(){
                                    jQuery('#adminForm').submit()
								}, 3000);"
                    ); ?></p>
                    <input type="hidden" name="task" value="cwmassets.checkassets"/>
                </div>
            <?php endif; ?>
            <?php echo HTMLHelper::_('form.token'); ?>
            <input type="hidden" name="tooltype" value=""/>
            <input type="hidden" name="option" value="com_proclaim"/>
        </form>
    </div>
</div>
