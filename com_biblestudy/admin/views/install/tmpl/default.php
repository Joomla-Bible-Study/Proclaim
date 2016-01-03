<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Apply error container chrome if there are errors detected
// $quirks_style = $this->haserrors ? 'alert-error' : "";
$formstyle    = '';

JHtml::_('behavior.framework');

if ($this->totalSteps != '0')
{
	$pre = $this->doneSteps . ' of ' . $this->totalSteps;
}
else
{
	$pre = '';
}
?>
<?php
if ($this->more)
{
	?>
	<h1><?php echo JText::_('JBS_MIG_WORKING'); ?></h1>
	<?php
}
else
{
	?>
	<h1><?php echo JText::_('JBS_MIG_MIGRATION_DONE'); ?></h1>
	<?php
}
?>
<script type="text/javascript" language="javascript">
	if (typeof jQuery == 'function') {
		if (typeof jQuery.ui == 'object') {
			jQuery('#nojquerywarning'). css('display', 'none');
		}
	}
</script>

<div id="install-progress-pane">
	<div class="migration-status">
		<div class="status"><?php echo $pre . ' ' . JText::_('JBS_MIG_PROCESSING') . ' ' . $this->running; ?></div>
	</div>

	<fieldset>
		<div id="install-progress-content">
			<div id="install-steps">
			</div>
			<div id="install-status" class="well">
				<div id="install-step"></div>
				<div id="install-substep"></div>
			</div>
			<div id="install-percentage" class="progress">
				<div class="progress progress-striped active">
					<div class="bar" style="width: <?php echo $this->percentage ?>%"></div> <?php echo $this->percentage; ?>%
				</div>
				<div class="bar" style="width: 0%"></div>
			</div>
			<div id="response-timer">
				<div class="color-overlay"></div>
				<div class="text"></div>
			</div>
		</div>
	</fieldset>

	<form action="index.php" name="adminForm" id="adminForm" method="get">
		<input type="hidden" name="option" value="com_biblestudy"/>
		<input type="hidden" name="view" value="install"/>
		<input type="hidden" name="task" value="run"/>
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken() ?>" value="1"/>
	</form>
</div>

<div id="backup-complete">
	<?php if (!$this->more)
	{
		?>
		<div id="j-main-container" class="span10">
			<div id="cpanel" class="btn-group">
				<div class="pull-left">
					<a href="index.php?option=com_biblestudy" class="btn cpanl-img">
						<img src="../media/com_biblestudy/images/icons/icon-48-administration.png"
						     border="0" alt="<?php echo JText::_('JBS_CMN_CONTROL_PANEL') ?>" width="32"
						     height="32"/>
				<span>
					<?php echo JText::_('JBS_CMN_CONTROL_PANEL') ?>
				</span>
					</a>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>

<div id="error-panel" style="display: none">

</div>
