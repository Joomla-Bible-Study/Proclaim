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
HtmlHelper::_('dropdown.init');
HtmlHelper::_('formbehavior.chosen', 'select');
HtmlHelper::_('biblestudy.framework');
HtmlHelper::_('biblestudy.loadcss', $this->params);
HtmlHelper::_('behavior.multiselect');

$app = Factory::getApplication();
$user = Factory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'ordering';
?>
<h2><?php echo Text::_('JBS_CMN_MESSAGES_LIST'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=messagelist'); ?>" method="post"
      name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search"
				       class="element-invisible"><?php echo Text::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>
					: </label>
				<input type="text" name="filter_search"
				       placeholder="<?php echo Text::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>" id="filter_search"
				       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				       title="<?php echo Text::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit"
				        title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
						class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button"
				        onclick="document.id('filter_search').value='';this.form.submit();"
				        title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="clearfix"></div>
			<div class="btn-group btn-small pull-right">
				<select name="filter_book" class="inputbox" onchange="Joomla.submitbutton()">
					<option value=""><?php echo Text::_('JBS_CMN_SELECT_BOOK'); ?></option>
					<?php echo HtmlHelper::_('select.options', $this->books, 'value', 'text', $this->state->get('filter.book')); ?>
				</select>
				<select name="filter_teacher" class="inputbox" onchange="Joomla.submitbutton()">
					<option value=""><?php echo Text::_('JBS_CMN_SELECT_TEACHER'); ?></option>
					<?php echo HtmlHelper::_('select.options', $this->teachers, 'value', 'text', $this->state->get('filter.teacher')); ?>
				</select>
				<select name="filter_series" class="inputbox" onchange="Joomla.submitbutton()">
					<option value=""><?php echo Text::_('JBS_CMN_SELECT_SERIES'); ?></option>
					<?php echo HtmlHelper::_('select.options', $this->series, 'value', 'text', $this->state->get('filter.series')); ?>
				</select>
			</div>
			<div class="btn-group btn-small pull-right">
				<select name="filter_message_type" class="inputbox" onchange="Joomla.submitbutton()">
					<option value=""><?php echo Text::_('JBS_CMN_MESSAGETYPE'); ?></option>
					<?php echo HtmlHelper::_('select.options', $this->messageTypes, 'value', 'text', $this->state->get('filter.messageType')); ?>
				</select>
				<select name="filter_year" class="inputbox" onchange="Joomla.submitbutton()">
					<option value=""><?php echo Text::_('JBS_CMN_SELECT_YEAR'); ?></option>
					<?php echo HtmlHelper::_('select.options', $this->years, 'value', 'text', $this->state->get('filter.year')); ?>
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
		<table class="table table-striped" id="articleList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_ID', 'study.id', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" style="min-width:25px;" class="nowrap center">
					<?php echo HtmlHelper::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_SCRIPTURE', 'book.bookname', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap hidden-phone">
					<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_MESSAGETYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :
				$item->max_ordering = 0;
				$canCreate          = $user->authorise('core.create');
				$canEdit            = $user->authorise('core.edit', 'com_proclaim.message.' . $item->id);
				$canEditOwn         = $user->authorise('core.edit.own', 'com_proclaim.message.' . $item->id);
				$canChange          = $user->authorise('core.edit.state', 'com_proclaim.message.' . $item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id; ?>">
					<td class="center hidden-phone">
						<?php echo HtmlHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo HtmlHelper::_('jgrid.published', $item->published, $i, 'messagelist.', $canChange, 'cb', '', ''); ?>
						</div>
					</td>
					<td class="nowrap small hidden-phone">
						<?php echo HtmlHelper::_('date', $item->studydate, Text::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td class=" has-context">
						<div class="pull-left">
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&task=messageform.edit&a_id=' . (int) $item->id); ?>">
									<?php echo $this->escape($item->studytitle); ?></a>
							<?php else: ?>
								<?php echo $this->escape($item->studytitle); ?>
							<?php endif; ?>
						</div>
						<div class="pull-left">
							<?php
								// Create dropdown items
								if ($item->published) :
									HtmlHelper::_('dropdown.unpublish', 'cb' . $i, 'messagelist.');
								else :
									HtmlHelper::_('dropdown.publish', 'cb' . $i, 'messagelist.');
								endif;

								HtmlHelper::_('dropdown.divider');

								if ($archived) :
									HtmlHelper::_('dropdown.unarchive', 'cb' . $i, 'messagelist.');
								else :
									HtmlHelper::_('dropdown.archive', 'cb' . $i, 'messagelist.');
								endif;

								if ($trashed) :
									HtmlHelper::_('dropdown.untrash', 'cb' . $i, 'messagelist.');
								else :
									HtmlHelper::_('dropdown.trash', 'cb' . $i, 'messagelist.');
								endif;

								// Render dropdown list
								echo HtmlHelper::_('dropdown.render');
							?>
						</div>
					</td>
					<td class="small hidden-phone">
						<?php echo Text::_($this->escape($item->bookname)) . ' ' . $this->escape($item->chapter_begin) . ':' . $this->escape($item->verse_begin); ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->teachername); ?>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->messageType); ?>
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
