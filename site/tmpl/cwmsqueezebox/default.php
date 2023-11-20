<?php
/**
 * Default view for Squeezebox
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;

HtmlHelper::_('behavior.framework');
HtmlHelper::_('bootstrap.modal');
?>

<form action="index.php" name="adminForm" id="adminForm">
    <input type="hidden" name="option" value="com_proclaim"/>
    <input type="hidden" name="view" value="squeezebox"/>
    <input type="hidden" name="tmpl" value="component"/>
</form>

<div class="alert alert-info">
    <p><?php
        echo Text::_('JBS_CMN_AUTOCLOSE_IN_3S'); ?></p>
</div>
<script type="text/javascript">
    window.setTimeout('closeme();', 3000);

    function closeme() {
        parent.SqueezeBox.close();
    }
</script>
