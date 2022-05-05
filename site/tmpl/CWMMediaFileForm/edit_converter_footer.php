<?php
/**
 * Converter Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
$published = $this->state->get('filter.published');
?>
<a class="btn" type="button" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="button" onclick="transferFileSize()" data-dismiss="modal">
	<?php echo Text::_('JBS_MED_CONVERTER'); ?>
</button>
