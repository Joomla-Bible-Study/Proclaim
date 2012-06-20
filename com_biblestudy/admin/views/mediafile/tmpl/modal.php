
<?php
/**
 * @package		JoomlaBibleStudy
 * @since 7.1.0
 * @desc form for modal layout
 * @copyright	Copyright (C) 2007 - 2012 Joomla Bible Study
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<div class="fltrt">
	<button type="button" onclick="Joomla.submitbutton('mediafile.save'); window.parent.SqueezeBox.close();">
		<?php echo JText::_('JSAVE');?></button>
	<button type="button" onclick="window.parent.SqueezeBox.close();">
		<?php echo JText::_('JCANCEL');?></button>
</div>
<div class="clr"></div>

<?php
$this->setLayout('edit');
echo $this->loadTemplate();

?>
