<?php
/**
 * Default view for Squeezebox
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHTML::_('behavior.framework');
	JHtml::_('behavior.modal');
}
else
{
	JHTML::_('behavior.mootools');
}
?>

<form action="index.php" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="view" value="squeezebox"/>
	<input type="hidden" name="tmpl" value="component"/>
</form>

<div class="alert alert-info">
	<p><?php echo JText::_('JBS_CMN_AUTOCLOSE_IN_3S'); ?></p>
</div>
<script type="text/javascript">
	window.setTimeout('closeme();', 3000);
	function closeme() {
		parent.SqueezeBox.close();
	}
</script>
