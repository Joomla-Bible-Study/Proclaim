<?php
/**
 * Form sub backup
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Start of Form
$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet)
{
	if ($name == 'jwplayer')
	{
		?>
		<?php if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		?>
		<p><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
	<?php
	}
		foreach ($this->form->getFieldset($name) as $field)
		{
			?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php } ?>
	<?php
	}
	else
	{
		?>
		<div id="jwplayer_pro_section" class="modal-body">
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
		</div><?php
	}
} ?>
