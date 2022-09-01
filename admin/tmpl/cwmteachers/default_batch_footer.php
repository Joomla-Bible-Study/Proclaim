<?php
/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$published = $this->state->get('filter.published');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_proclaim.administrator-teachers-batch');

?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" id='batch-submit-button-id' class="btn btn-success"  data-submit-task='techer.batch'>
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
