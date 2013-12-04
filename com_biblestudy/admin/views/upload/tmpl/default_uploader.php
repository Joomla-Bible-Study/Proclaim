<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die();

?>
<form action="" method="post">

    <div id="uploader">

        <p><?php JText::printf('JBS_ERROR_RUNTIME_NOT_SUPORTED', $this->runtime) ?></p>

</div>
<?php echo JHtml::_('form.token'); ?>
<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />


</form>
<?php if($this->enableLog) : ?>
	<button id="log_btn"><?php echo JText::_('JBS_UPLOADER_LOG_BTN'); ?></button>
	<div id="log"></div>
<?php endif; ?>
