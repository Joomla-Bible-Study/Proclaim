<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

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
			jQuery('#nojquerywarning').css('display', 'none');
		}
	}
</script>

<div id="install-progress-pane">
	<div class="migration-status">
		<div class="status"><?php echo $pre . ' ' . JText::_('JBS_MIG_PROCESSING') . ' ' . $this->running; ?></div>
	</div>
	<div class="progress progress-striped active">
		<div class="bar" style="width: <?php echo $this->percentage ?>%;"></div> <?php echo $this->percentage; ?>%
	</div>
</div>
<form action="index.php" name="adminForm" id="adminForm" method="get">
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="view" value="install"/>
	<?php if ($this->state === 'start')
	{
		?>
		<input type="hidden" name="task" value="install.browse"/>
	<?php
}
	else
	{
		?>
		<input type="hidden" name="task" value="install.run"/>
	<?php
	}
	?>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken() ?>" value="1"/>
</form>

<div id="backup-complete">
	<?php
	if (!$this->more)
		:
		?>
		<div class="alert alert-info">
			<p><?php echo JText::_('JBS_LBL_REDIRECT_IN_3S'); ?></p>
		</div>
		<script type="text/javascript">
			window.setTimeout('redirect();', 3000);
			function redirect() {
				window.location.replace("index.php?option=com_biblestudy&view=install&layout=install_finished&<?php echo JSession::getFormToken() ?>=1");
			}
		</script>
	<?php
	endif;
	?>
</div>
