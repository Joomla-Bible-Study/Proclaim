<?php
/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;

$published = $this->state->get('filter.published');
HTMLHelper::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');
?>

<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('proclaim.players'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('proclaim.popup'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('proclaim.Mediatype'); ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('proclaim.link_type'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('batch.access'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('batch.language'); ?>
		</div>
	</div>
</div>
