<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
/** @var $this BiblestudyViewMediafilelist */
HtmlHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HtmlHelper::_('dropdown.init');
HtmlHelper::_('formbehavior.chosen', 'select');
HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('biblestudy.framework');
HtmlHelper::_('biblestudy.loadcss', $this->params);

$app = Factory::getApplication();
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'mediafile.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_proclaim&task=mediafiles.saveOrderAjax&tmpl=component';
	HtmlHelper::_('sortablelist.sortable', 'mediafileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable=function () {
		var table = document.getElementById("sortTable");
		var direction = document.getElementById("directionTable");
		var order = table.options[table.selectedIndex].value;
		var dirn;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<h2><?php echo Text::_('JBS_CMN_MEDIA'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=mediafilelist'); ?>" method="post"
      name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search"
						class="element-invisible"><?php echo Text::_('JBS_MED_FILENAME'); ?>
					: </label>
				<input type="text" name="filter_search" placeholder="<?php echo Text::_('JBS_MED_FILENAME') ?>"
						id="filter_search"
						value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
						title="<?php echo Text::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit"
						title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
						class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button"
						onclick="document.id('filter_filename').value='';this.form.submit();"
						title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="clearfix"></div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable"
						class="element-invisible"><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></option>
					<option
						value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo Text::_('JBS_CMN_ASCENDING'); ?></option>
					<option
						value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo Text::_('JBS_CMN_DESCENDING'); ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo Text::_('JBS_CMN_SELECT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JBS_CMN_SELECT_BY'); ?></option>
					<?php echo HtmlHelper::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
				<select name="filter_published" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					<?php echo HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
				</select>
			</div>
			<div class="btn-group pull-right">
				<?php echo $this->newlink; ?>
			</div>
		</div>
		<div class="clearfix"></div>

		<table class="table table-striped" id="articleList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" style="min-width:25px;" class="nowrap center">
					<?php echo HtmlHelper::_('grid.sort', 'JPUBLISHED', 'mediafile.published', $listDirn, $listOrder); ?>
				</th>
				<th width="20%">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_MED_FILENAME', 'mediafile.filename', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_ID', 'mediafile.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :
				$item->max_ordering = 0;
				$canCreate          = $user->authorise('core.create');
				$canEdit            = $user->authorise('core.edit', 'com_proclaim.mediafile.' . $item->id);
				$canEditOwn         = $user->authorise('core.edit.own', 'com_proclaim.mediafile.' . $item->id);
				$canChange          = $user->authorise('core.edit.state', 'com_proclaim.mediafile.' . $item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo HtmlHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo HtmlHelper::_('jgrid.published', $item->published, $i, 'mediafilelist.', $canChange, 'cb', '', ''); ?>
						</div>
					</td>

					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($canEdit || $canEditOwn) : ?>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&task=mediafileform.edit&a_id=' . (int) $item->id); ?>">
								<?php endif; ?>
								<span class="label">
										<?php
										$label = $this->escape($item->serverConfig->name->__toString()) . ' - ';
										$label .= $this->escape($item->params[$item->serverConfig->config->media_resource->__toString()])
											? $item->serverConfig->config->media_resource->__toString() : 'mediacode';
										?>
										<?php echo $label; ?>
								</span>
								<?php echo '...' . substr($this->escape($item->params[$item->serverConfig->config->media_resource->__toString()]), -25); ?>
								<?php if ($canEdit || $canEditOwn) : ?>
							</a>
						<?php endif; ?>
						</div>
						<div class="pull-left">
							<?php
								// Create dropdown items
								if ($item->published) :
									HtmlHelper::_('dropdown.unpublish', 'cb' . $i, 'mediafilelist.');
								else :
									HtmlHelper::_('dropdown.publish', 'cb' . $i, 'mediafilelist.');
								endif;

								HtmlHelper::_('dropdown.divider');

								if ($archived) :
									HtmlHelper::_('dropdown.unarchive', 'cb' . $i, 'mediafilelist.');
								else :
									HtmlHelper::_('dropdown.archive', 'cb' . $i, 'mediafilelsit.');
								endif;

								if ($trashed) :
									HtmlHelper::_('dropdown.untrash', 'cb' . $i, 'mediafilelist.');
								else :
									HtmlHelper::_('dropdown.trash', 'cb' . $i, 'mediafilelist.');
								endif;

								// Render dropdown list
								echo HtmlHelper::_('dropdown.render');
							?>
						</div>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
</form>
