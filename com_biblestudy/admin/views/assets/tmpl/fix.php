<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
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

<script type="text/javascript" language="javascript">
	if (typeof jQuery == 'function') {
		if (typeof jQuery.ui == 'object') {
			jQuery('#nojquerywarning').css('display', 'none');
		}
	}
</script>

<div class="progress progress-striped active">
    <div class="bar" style="width: <?php echo $this->percentage ?>%;"></div> <?php echo $this->percentage; ?>%
</div>

<form action="<?php JRoute::_('index.php?option=com_biblestudy&view=assets'); ?>" name="adminForm" id="adminForm"
      method="get">
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
    <input type="hidden" name="tmpl" value="component"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php if (!$this->more): ?>
    <div class="alert alert-info">
        <p><?php echo JText::_('JBS_LBL_AUTOCLOSE_IN_3S'); ?></p>
    </div>
    <script type="text/javascript">
		window.setTimeout('closeme();', 3000);
		function closeme() {
			window.parent.document.location = 'index.php?option=com_biblestudy&view=assets&task=assets.checkassets&<?php echo JSession::getFormToken(); ?>=1';
			window.location.reload();
			parent.SqueezeBox.close();
		}
    </script>
<?php endif; ?>
