<?php
// @todo add header
defined('_JEXEC') or die; ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="col100">
	<div style="width: 100%; text-align: center;">
		<label for="series" >
			<?php echo JText::_('Directory to Scan'); ?>:
		</label>
		<input type="text" name="directoryname" size="50" value="<?php echo JPATH_SITE.DS.'media'.DS; ?>" />
		<span id="dirStatus" class="st"></span>
		<input type="submit" name="preview" value="Preview"/>
	<div class="clr"></div>

	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="view" value="mp3"/>
	<input type="hidden" name="controller" value="mp3" />
</form>