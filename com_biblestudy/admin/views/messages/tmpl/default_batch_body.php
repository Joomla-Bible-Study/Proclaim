<?php
/**
 * Batch Template
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');
?>

<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo JHtml::_('batch.access'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo JHtml::_('biblestudy.Teacher'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo JHtml::_('biblestudy.Messagetype'); ?>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="control-group span4">
		<div class="controls">
			<?php echo JHtml::_('biblestudy.Series'); ?>
		</div>
	</div>
	<div class="control-group span4">
		<div class="controls">
			<?php echo JHtml::_('batch.language'); ?>
		</div>
	</div>
</div>
