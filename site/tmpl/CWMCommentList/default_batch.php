<?php
/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;
$published = $this->state->get('filter.published');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
			<button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
		<h3><?php echo Text::_('JBS_CMN_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body">
		<p><?php echo Text::_('JBS_CMN_BATCH_TIP'); ?></p>

		<div class="control-group">
			<div class="controls">
				<?php echo HtmlHelper::_('batch.access'); ?>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-folders-id');document.id('batch-access').value=''"
		        data-dismiss="modal">
			<?php echo Text::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('comment.batch');">
			<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
