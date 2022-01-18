<?php
/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
HTMLHelper::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');
?>

<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('biblestudy.players'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('biblestudy.popup'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('biblestudy.Mediatype'); ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo HTMLHelper::_('biblestudy.link_type'); ?>
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
