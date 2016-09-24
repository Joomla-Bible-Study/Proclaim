<?php
/**
 * Batch Template
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>
<a class="btn" type="button"
        onclick="document.id('batch-mediafiles-id');document.id('batch-access').value=''" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('mediafile.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
