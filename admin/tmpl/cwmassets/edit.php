<?php
/**
 * Admin Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('JBS_ADM_ASSET_TABLE_NAME'), 'administration');

defined('_JEXEC') or die;

$app  = Factory::getApplication();
$user = Factory::getApplication()->getSession()->get('user');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript("
		Joomla.submitbutton = function(task)
		{
			var form = document.getElementById('item-assets');
			if (task == 'cwmassets.back' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'cwmassets.checkassets' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
			elseif (task == 'cwmassets.browse' || document.formvalidator.isValid(form))
			{
				Joomla.submitform(task, form);
			}
		};
");
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmassets') ?>" method="post" name="assetsForm"
      id="item-assets" class="form-horizontal">
	<div class="row-fluid">
		<?php

		if ($this->assets)
		{
			echo '<table class="table table-hover table-striped table-sm table-bordered">';
			echo '<thead>';
			echo '<tr class="table-primary">';

			echo '<th style="width: 20%;" class="center">' . Text::_('JBS_ADM_TABLENAMES') . '</th>';
			echo '<th class="center">' . Text::_('JBS_ADM_ROWCOUNT') . '</th>';
			echo '<th class="center">' . Text::_('JBS_ADM_NULLROWS') . '</th>';
			echo '<th class="center">' . Text::_('JBS_ADM_MATCHROWS') . '</th>';
			echo '<th class="center">' . Text::_('JBS_ADM_ARULESROWS') . '</th>';
			echo '<th class="center">' . Text::_('JBS_ADM_NOMATCHROWS') . '</th>';
			echo '</tr>';
			echo '</thead>';
			foreach ($this->assets as $asset)
			{
				echo '<tr>';
				echo '<td>' . Text::_($asset['realname']) . '</td>';
				echo '<td class="center">' . Text::_($asset['numrows']) . '</td>';
				echo '<td class="center">';
				if ($asset['nullrows'] > 0)
				{
					echo '<span style="color: red;">';
				}
				else
				{
					echo '<span>';
				}
				echo Text::_($asset['nullrows']) . '</span></td>';
				echo '<td class="center">';
				if ($asset['matchrows'] > 0)
				{
					echo '<span style="color: green;">';
				}
				else
				{
					echo '<span>';
				}
				echo Text::_($asset['matchrows']) . '</span></td>';
				echo '<td class="center">';
				if ($asset['arulesrows'] > 0)
				{
					echo '<span style="color: red;">';
				}
				else
				{
					echo '<sapn>';
				}
				echo Text::_($asset['arulesrows']) . '</span></td>';
				echo '<td class="center">';
				if ($asset['nomatchrows'] > 0)
				{
					echo '<span style="color: red;">';
				}
				else
				{
					echo '<span>';
				}
				echo Text::_($asset['nomatchrows']) . '</span></td>';
				echo '</tr>';
			}
			echo '<tr><td colspan="6">';
			echo Text::_('JBS_ADM_ASSET_EXPLANATION');
			echo '</td></tr>';
			echo '</table>';
		}
		?>
<!--		--><?php //if ($user->authorise('core.create', 'com_proclaim')
//			&& $user->authorise('core.edit', 'com_proclaim')
//			&& $user->authorise('core.edit.state', 'com_proclaim')
//		) : ?>
<!--			--><?php //echo HTMLHelper::_(
//				'bootstrap.renderModal',
//				'collapseModal',
//				array(
//					'title'  => Text::_('JBS_ADM_FIX'),
//					'footer' => $this->loadTemplate('fix'),
//				),
//			); ?>
<!--		--><?php //endif; ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="tooltype" value=""/>
		<input type="hidden" name="component" value="com_proclaim"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
