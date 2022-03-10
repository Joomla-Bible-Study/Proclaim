<?php
/**
 * Converter Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
HTMLHelper::_('proclaim.framework');
?>
<div class="row-fluid">
	<div class="control-group">
		<div class="controls">
			<input name="size_converter" type=text ID="Text1" onChange="decOnly(this);" size=5>
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<select name="sel" ID="Select1">
				<option value="KB">KB</option>
				<option value="MB" selected>MB</option>
				<option value="GB">GB</option>
			</select>
		</div>
	</div>
</div>
