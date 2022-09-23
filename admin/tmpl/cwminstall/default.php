<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// Protect from unauthorized access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die();

HTMLHelper::_('behavior.framework');

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
	<h1><?php echo Text::_('JBS_MIG_WORKING'); ?></h1>
	<?php
}
else
{
	?>
	<h1><?php echo Text::_('JBS_MIG_MIGRATION_DONE'); ?></h1>
	<?php
}
?>
<script type="text/javascript">
	if (typeof jQuery == 'function') {
		if (typeof jQuery.ui == 'object') {
			jQuery('#nojquerywarning').css('display', 'none');
		}
	}
</script>

<div id="install-progress-pane">
	<div class="migration-status">
		<div class="status"><?php echo $pre . ' ' . Text::_('JBS_MIG_PROCESSING') . ' ' . $this->running; ?></div>
	</div>
	<div class="progress progress-striped active">
		<div class="bar" style="width: <?php echo $this->percentage ?>%;"></div> <?php echo $this->percentage; ?>%
	</div>
</div>
<form action="index.php" name="adminForm" id="adminForm" method="get">
	<input type="hidden" name="option" value="com_proclaim"/>
	<input type="hidden" name="view" value="install"/>
	<?php ?>
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
	<input type="hidden" name="<?php echo Factory::getApplication()->getSession()->getFormToken() ?>" value="1"/>
</form>

<div id="backup-complete">
	<?php
	if (!$this->more)
		:
		?>
		<div class="alert alert-info">
			<p><?php echo Text::_('JBS_LBL_REDIRECT_IN_3S'); ?></p>
		</div>
		<script type="text/javascript">
			window.setTimeout('redirect();', 3000);
			function redirect() {
				window.location.replace("index.php?option=com_proclaim&view=cwminstall&layout=install_finished&<?php echo Session::getFormToken() ?>=1");
			}
		</script>
	<?php
	endif;
	?>
</div>
