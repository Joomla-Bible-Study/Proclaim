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
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die();

$wa = $this->document->getWebAssetManager();
$wa->addInlineScript("if (typeof jQuery == 'function')
	{
		if (typeof jQuery.ui == 'object')
		{
			jQuery('#nojquerywarning').css('display', 'none')
		}
	}")
?>
<div class="p-3">
	<div class="row">
		<?php if ($this->more)
		{
			?>
			<h1><?php echo Text::_('JBS_FIXASSETS_WORKING'); ?></h1>
			<?php
		}
		else
		{
			?>
			<h1><?php echo Text::_('JBS_FIXASSETS_DONE'); ?></h1>
			<?php
		}
		?>
		<div class="progress progress-striped active">
			<div class="bar" style="width: <?php echo $this->percentage ?>%;"></div> <?php echo $this->percentage; ?>%
		</div>

		<form action="<?php Route::_('index.php?option=com_proclaim&view=cwmassets'); ?>" name="adminForm"
		      id="adminForm" class="form-inline">
			<?php if ($this->state === 'start')
			{ ?>
				<input type="hidden" name="task" value="cwmassets.browse"/>
			<?php }
			elseif ($this->more)
			{ ?>
				<input type="hidden" name="task" value="cwmassets.run"/>
			<?php } ?>
			<?php echo HTMLHelper::_('form.token'); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="tooltype" value=""/>
			<input type="hidden" name="option" value="com_proclaim"/>
		</form>

		<?php if ($this->more === true): ?>
			<div class="alert alert-info">
				<p><?php echo Text::_('JBS_LBL_AUTOCLOSE_IN_3S'); ?></p>
			</div>
			<script type="text/javascript">
							window.setTimeout('closeme();', 3000)

							function closeme ()
							{
								window.parent.document.location = 'index.php?option=com_proclaim&view=cwmassets&task=cwmassets.checkassets&<?php echo Session::getFormToken(); ?>=1'
								window.location.reload()
								parent.SqueezeBox.close()
							}
			</script>
		<?php endif; ?>
	</div>
</div>