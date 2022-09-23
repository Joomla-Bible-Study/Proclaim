<?php
/**
 * Form sub backup
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// Check to ensure this file is included in Joomla!
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

// Start of Form
$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet)
{
	if ($name == 'jwplayer')
	{
		if (isset($fieldSet->description) && trim($fieldSet->description))
		{
			?>
			<h3><?php echo $this->escape(Text::_($fieldSet->description)); ?></h3>
			<?php
		}
		foreach ($this->form->getFieldset($name) as $field)
		{
			?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php
		}
	}
	else
	{
		?>
		<div id="jwplayer_pro_section">
			<?php foreach ($this->form->getFieldset($name) as $field)
			{
				?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}
