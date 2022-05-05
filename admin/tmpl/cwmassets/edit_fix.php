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
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();
?>

<script type="text/javascript">
	if (typeof jQuery == 'function')
	{
		if (typeof jQuery.ui == 'object')
		{
			jQuery('#nojquerywarning').css('display', 'none')
		}
	}
</script>
<div class="p-3">
	<div class="row">
		<?php if ($this->more)
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

		<form action="<?php JRoute::_('index.php?option=com_proclaim&view=cwmassets'); ?>" name="adminForm"
		      id="adminForm"
		      method="get">
			<input type="hidden" name="option" value="com_proclaim"/>
			<input type="hidden" name="view" value="assets"/>
			<?php if ($this->state === 'start')
			{ ?>
				<input type="hidden" name="task" value="assets.browse"/>
			<?php }
			else
			{ ?>
				<input type="hidden" name="task" value="assets.run"/>
			<?php } ?>
			<input type="hidden" name="tmpl" value="component"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>

		<?php if ($this->more === true): ?>
			<div class="alert alert-info">
				<p><?php echo JText::_('JBS_LBL_AUTOCLOSE_IN_3S'); ?></p>
			</div>
			<script type="text/javascript">
							window.setTimeout('closeme();', 3000)

							function closeme ()
							{
								window.parent.document.location = 'index.php?option=com_proclaim&view=cwmassets&task=cwmassets.checkassets&<?php echo JSession::getFormToken(); ?>=1'
								window.location.reload()
								parent.SqueezeBox.close()
							}
			</script>
		<?php endif; ?>
	</div>
</div>
