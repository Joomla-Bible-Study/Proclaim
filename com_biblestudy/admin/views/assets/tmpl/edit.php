<?php
/**
 * Admin Form
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');

JText::script('ERROR');

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'admin.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'admin.checkassets' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'admin.fixAssets' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=assets') ?>" method="post" name="adminForm" id="item-assets" class="form-horizontal">
	<div class="row-fluid">
		<div class="span6 form-horizontal">
			<h4><?php echo JText::_('JBS_ADM_ASSET_CHECK'); ?></h4>

			<div class="span2">
				<a onclick="Joomla.submitbutton('admin.checkassets')">
					<img
						src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
						alt="Check Assets" height="48" width="48" style="clear: right;"/>
					<div><?php echo JText::_('JBS_ADM_CHECK_ASSETS'); ?></div>
				</a>
			</div>
			<div class="span2">
				<a onclick="Joomla.submitbutton('admin.fixAssets')"><img
						src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/export.png'; ?>"
						alt="Fix Assets" height="48" width="48"/>
					<div><?php echo JText::_('JBS_ADM_FIX'); ?></div>
				</a>
			</div>
			<div class="clearfix"></div>
			<div class="table table-hover table-striped">
				<?php
				$input = new JInput;
				$checkassets2 = $input->get('checkassets', null, 'array');

				if ($checkassets2)
				{
					echo '<table style="border: 1px solid;">';
					echo '<caption><h2>' . JText::_('JBS_ADM_ASSET_TABLE_NAME') . '</h2></caption>';
					echo '<thead>';
					echo '<tr>';

					echo '<th style="width: 20%;" class="center">' . JText::_('JBS_ADM_TABLENAMES') . '</th>';
					echo '<th class="center">' . JText::_('JBS_ADM_ROWCOUNT') . '</th>';
					echo '<th class="center">' . JText::_('JBS_ADM_NULLROWS') . '</th>';
					echo '<th class="center">' . JText::_('JBS_ADM_MATCHROWS') . '</th>';
					echo '<th class="center">' . JText::_('JBS_ADM_ARULESROWS') . '</th>';
					echo '<th class="center">' . JText::_('JBS_ADM_NOMATCHROWS') . '</th>';
					echo '</tr>';
					echo '</thead>';
					foreach ($checkassets2 as $asset)
					{
						echo '<tr>';
						echo '<td><p>' . JText::_($asset['realname']) . '</p></td>';
						echo '<td class="center"><p>' . JText::_($asset['numrows']) . '</p></td>';
						echo '<td class="center">';
						if ($asset['nullrows'] > 0)
						{
							echo '<p style="color: red;">';
						}
						else
						{
							echo '<p>';
						}
						echo JText::_($asset['nullrows']) . '</p></td>';
						echo '<td class="center">';
						if ($asset['matchrows'] > 0)
						{
							echo '<p style="color: green;">';
						}
						else
						{
							echo '<p>';
						}
						echo JText::_($asset['matchrows']) . '</p></td>';
						echo '<td class="center">';
						if ($asset['arulesrows'] > 0)
						{
							echo '<p style="color: red;">';
						}
						else
						{
							echo '<p>';
						}
						echo JText::_($asset['arulesrows']) . '</p></td>';
						echo '<td class="center">';
						if ($asset['nomatchrows'] > 0)
						{
							echo '<p style="color: red;">';
						}
						else
						{
							echo '<p>';
						}
						echo JText::_($asset['nomatchrows']) . '</p></td>';
						echo '</tr>';
					}
					echo '<tr><td colspan="6">';
					echo '<p>' . JText::_('JBS_ADM_ASSET_EXPLANATION') . '</p>';
					echo '</td></tr>';
					echo '</table>';
				}
				?>
			</div>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="tooltype" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
