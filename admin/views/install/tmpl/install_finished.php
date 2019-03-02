<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Clear Session after finish
$session = JFactory::getSession();
$session->set('migration_stack', '', 'JBSM');
?>
<?php if (!empty($errors)): ?>
	<!--suppress HtmlUnknownTarget -->
	<div style="background-color: #900; color: #fff; font-size: large;">
		<h1>MySQL errors during installation</h1>

		<p>The installation script detected MySQL error which will
			prevent the component from working properly. We suggest uninstalling
			any previous version of Akeeba Backup and trying a clean installation.
		</p>

		<p>
			The MySQL errors were:
		</p>

		<p style="font-size: initial;">
			<?php echo implode("<br/>", $errors); ?>
		</p>
	</div>
<?php endif; ?>

<h1>
	<img src="../media/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" class="float: left"/>
	<?php echo JText::sprintf('JBS_INS_INSTALLATION_RESULTS', JText::_('JBS_MIG_MIGRATION_DONE')); ?>
</h1>

<?php $rows = 0; ?>
<div class="clearfix"></div>

<table class="table table-striped adminlist" id="install">
	<thead>
	<tr>
		<th class="title" colspan="2">Extension</th>
		<th width="30%">Status</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="3"></td>
	</tr>
	</tfoot>
	<tbody>
	<tr class="row0">
		<td class="key" colspan="2">Proclaim Component</td>
		<td><strong>Installed</strong></td>
	</tr>
	<?php if (count($this->status->modules)) : ?>
		<tr>
			<th>Module</th>
			<th>Client</th>
			<th></th>
		</tr>
		<?php foreach ($this->status->modules as $module) : ?>
			<tr class="row<?php echo(++$rows % 2); ?>">
				<td class="key"><?php echo $module['name']; ?></td>
				<td class="key"><?php echo ucfirst($module['client']); ?></td>
				<td><strong
						style="color: <?php echo ($module['result']) ? "green" : "red" ?>;"><?php echo ($module['result']) ? 'Installed' : 'Not installed'; ?></strong>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if (count($this->status->plugins)) : ?>
		<tr>
			<th>Plugin</th>
			<th>Group</th>
			<th></th>
		</tr>
		<?php foreach ($this->status->plugins as $plugin) : ?>
			<tr class="row<?php echo(++$rows % 2); ?>">
				<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
				<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
				<td><strong
						style="color: <?php echo ($plugin['result']) ? "green" : "red" ?>;"><?php echo ($plugin['result']) ? 'Installed' : 'Not installed'; ?></strong>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>
<table class="table table-striped adminlist" id="install">
	<tbody>
	<tr>
		<td>

			<a href="index.php?option=com_biblestudy">
				<img src="../media/com_biblestudy/images/done-icon.jpg" alt="Done"/>

				<h3 style="text-align: left;"><?php echo JText::_('JBS_INS_CLICK_TO_FINISH'); ?></h3>
			</a>

		</td>

	</tr>

	<tr>
		<td>
			<p><a href="https://www.christianwebministries.org/support/user-help-forum.html"
			      target="_blank"><?php echo JText::_('JBS_INS_VISIT_FORUM'); ?></a></p>

			<p><a href="https://www.christianwebministries.org"
			      target="_blank"><?php echo JText::_('JBS_INS_GET_MORE_HELP'); ?></a></p>

			<p><a href="https://www.christianwebministries.org/documentation.html"
			      target="_blank"><?php echo JText::_('JBS_INS_VISIT_DOCUMENTATION'); ?></a></p>

			<p><?php echo JText::_('JBS_INS_TITLE'); ?> &copy; by <a href="https://www.christianwebministries.org"
			                                                         target="_blank">www.ChristianWebMinistries.org</a>
				All rights reserved.</p>

		</td>
	</tr>
	</tbody>
</table>


