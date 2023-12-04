<?php

/**
 * Form sub Archive
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      7.1.0
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('formbehavior.chosen', 'select');

Factory::getDocument()->addScriptDeclaration(
    "
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'cwmadmin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
"
);
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmarchive'); ?>" enctype="multipart/form-data"
      method="post" name="adminForm" id="adminForm">
    <div class="row-fluid" style="margin-top: 50px;">
        <div class="col-12 form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('timeframe'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('timeframe'); ?>
                    <?php
                    echo $this->form->getInput('switch'); ?>
                </div>
            </div>
            <div class="control-group">
                <input class="btn btn-primary" type="submit" value="<?php
                echo Text::_('JBS_CMN_SUBMIT'); ?>"
                       name="submit"/>
                <button onclick="Joomla.submitbutton('cwmadmin.back')" class="btn btn-default">
                    <span class="icon-back"></span>
                    Back
                </button>
            </div>
        </div>
    </div>
    <input type="hidden" name="option" value="com_proclaim"/>
    <input type="hidden" name="task" value="cwmadmin.doArchive"/>
    <input type="hidden" name="controller" value="admin"/>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
