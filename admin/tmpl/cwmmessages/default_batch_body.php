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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;

$params = ComponentHelper::getParams('com_proclaim');

$published = (int) $this->state->get('filter.published');

$user = Factory::getUser();
?>
<div class="p-3">
	<div class="row">
		<?php if (Multilanguage::isEnabled()) : ?>
			<div class="form-group col-md-6">
				<div class="controls">
					<?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="form-group col-md-6">
				<div class="controls">
					<?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_proclaim']); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="form-group col-md-4">
		<div class="controls">
			<?php echo HTMLHelper::_('Proclaim.Teacher'); ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="form-group col-md-6">
		<div class="controls">
			<?php echo HTMLHelper::_('Proclaim.Series'); ?>
		</div>
	</div>
	<div class="form-group col-md-4">
		<div class="controls">
			<?php echo HTMLHelper::_('Proclaim.MessageType'); ?>
		</div>
	</div>
</div>
