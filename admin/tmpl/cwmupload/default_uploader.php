<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

?>
<script type="javascript">
    function submitbutton(task) {
        if (task === '') {
            return false;
        } else if (task === 'upload') {
            if (document.adminForm.upload_folder.value === '') {
                alert("<?php echo Text::_('JBS_MED_SELECT_FOLDER'); ?>");
            } else if (document.adminForm.upload_server.value === '') {
                alert("<?php echo Text::_('JBS_MED_ENTER_SERVER'); ?>");
            } else {
                document.submitform(task);
                window.location.setTimeout('window.location.reload(true)', 1000);
                return true;
            }
        }

        $(function () {
            $("a").trigger(function (event) {
                alert("Thanks for visiting!");
            });
        });
</script>
<form action="
<?php
$input = new Joomla\Input\Input();
if ($input->get('layout', '', 'string') === 'modal') {
    $url = 'index.php?option=com_proclaim&view=cwmupload&tmpl=component&layout=modal';
} else {
    $url = 'index.php?option=com_proclaim&view=cwmupload&layout=default';
}
echo Route::_($url);
?>" method="post" name="adminForm" id="item-form" class=" form-horizontal">
    <div class="control-group">
        <div class="control-label">
            <?php
            echo $this->form->getLabel('server'); ?>
        </div>
        <div class="controls">
            <?php
            echo $this->form->getInput('server'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php
            echo $this->form->getLabel('path'); ?>
        </div>
        <div class="controls">
            <?php
            echo $this->form->getInput('path'); ?>
        </div>
    </div>


    <div id="uploader">

        <p><?php
            echo Text::_('JBS_UPLOADER_ERROR_RUNTIME_NOT_SUPORTED') . ' ' . $this->runtime; ?></p>

    </div>
    <?php
    echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="<?php
    echo JSession::getFormToken(); ?>" value="1"/>


</form>
<?php
if ($this->enableLog) : ?>
    <button id="log_btn"><?php
        echo Text::_('JBS_UPLOADER_LOG_BTN'); ?></button>
    <div id="log"></div>
    <?php
endif; ?>
