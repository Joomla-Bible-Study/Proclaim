<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

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
<?php if ($this->more): ?>
	<h1><?php echo JText::_('JBS_MIG_WORKING'); ?></h1>
<?php else: ?>
	<h1><?php echo JText::_('JBS_MIG_MIGRATION_DONE'); ?></h1>
<?php endif; ?>


<div class="progress progress-striped active">
	<div class="bar" style="width: <?php echo $this->percentage ?>%"></div>
</div>

<form action="index.php" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_biblestudy"/>
	<input type="hidden" name="view" value="migration"/>
	<input type="hidden" name="task" value="migration.run"/>
</form>

<?php if (!$this->more): ?>
	<div id="j-main-container" class="span10">
		<div id="cpanel" style="padding-left: 20px">
			<div class="pull-left">
				<div class="icon">
					<a href="index.php?option=com_biblestudy&view=cpanel">
						<img src="../media/com_biblestudy/images/icons/icon-48-administration.png"
						     border="0" alt="<?php echo JText::_('JBS_CMN_CONTROL_PANEL') ?>" width="32" height="32"/>
					<span>
						<?php echo JText::_('JBS_CMN_CONTROL_PANEL') ?>
					</span>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
