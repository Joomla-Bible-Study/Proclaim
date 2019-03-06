<?php
/**
 * Converter Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>
<a class="btn" type="button" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="button" onclick="transferFileSize()" data-dismiss="modal">
	<?php echo JText::_('JBS_MED_CONVERTER'); ?>
</button>
