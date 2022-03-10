<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.framework', true);
HTMLHelper::_('formbehavior.chosen', 'select');

$input     = Factory::getApplication()->input;
$function  = $input->getCmd('function', 'jSelectServer');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
	jSelectServer_jform_server_id = function(server_id) {
		window.parent.Joomla.submitbutton('mediafile.setServer', server_id);
		window.parent.SqueezeBox.close();
	}
</script>
<form
	action="<?php echo JRoute::_('index.php?option=com_proclaim&view=servers&layout=modal&tmpl=component&function=' . $function); ?>"
	method="post" name="adminForm" id="adminForm" class="form-inline">
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'JBS_SVR_SERVER_NAME', 'mediafile.name', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="1">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<a href="javascript:void(0)"
					   onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $item->server_name; ?>', '', null, '<?php echo "index.php?option=com_proclaim&view=server&id=" . $item->id; ?>', '', null);">
						<?php echo $this->escape($item->server_name); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
