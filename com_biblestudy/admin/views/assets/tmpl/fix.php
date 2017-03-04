<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.modal');

if ($this->more)
{
	?>
	<h1><?php echo JText::_('JBS_FIXASSETS_WORKING'); ?></h1>
	<?php
}
else
{
	?>
	<h1><?php echo JText::_('JBS_FIXASSETS_DONE'); ?></h1>
	<?php
}
?>

<div class="progress progress-striped active">
	<div class="bar" style="width: <?php echo $this->percentage ?>%;"></div> <?php echo $this->percentage; ?>%
</div>

<form action="index.php" name="adminForm" id="adminForm" method="get">
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="view" value="assets"/>
	<?php if ($this->state === 'start')
	{ ?>
		<input type="hidden" name="task" value="assets.browse"/>
	<?php }
	else
	{ ?>
		<input type="hidden" name="task" value="assets.run"/>
	<?php } ?>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken() ?>" value="1"/>
</form>

<div id="backup-complete">
	<?php if (!$this->more): ?>
		<div class="alert alert-info">
			<p><?php echo JText::_('JBS_LBL_AUTOCLOSE_IN_3S'); ?></p>
		</div>
		<script type="text/javascript">
			window.setTimeout('closeme();', 3000);
			function closeme() {
				parent.SqueezeBox.close();
			}
		</script>
	<?php endif; ?>
</div>
