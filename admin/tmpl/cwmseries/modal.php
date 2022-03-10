<?php
/**
 * Modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Site\Helper\CWMRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

HTMLHelper::_('behavior.multiselect');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_proclaim.cwmadmin-series-modal');

$function  = $app->input->getCmd('function', 'jSelectSeries');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
$multilang = Multilanguage::isEnabled();

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	$this->document->addScriptOptions('xtd-series', array('editor' => $editor));
	$onclick = "jSelectSeries";
}
?>
<form
		action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseries&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
		method="post" name="adminForm" id="adminForm" class="form-inline">
	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-info">
			<span class="icon-info-circle" aria-hidden="true"></span><span
					class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-sm">
			<caption class="visually-hidden">
				<?php echo Text::_('COM_CONTENT_ARTICLES_TABLE_CAPTION'); ?>,
				<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
				<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
			</caption>
			<thead>
			<tr>
				<th scope="col" class="w-1 text-center">
					<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'series.published', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="title">
					<?php echo HTMLHelper::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'series.access', $listDirn, $listOrder); ?>
				</th>
				<?php if ($multilang) : ?>
				<th scope="col" class="w-15">
					<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<th scope="col" class="w-1 d-none d-md-table-cell">
					<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'series.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$iconStates = array(
				-2 => 'icon-trash',
				0  => 'icon-times',
				1  => 'icon-check',
			);
			?>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php if ($item->language && $multilang)
				{
					$tag = strlen($item->language);
					if ($tag == 5)
					{
						$lang = substr($item->language, 0, 2);
					}
					elseif ($tag == 6)
					{
						$lang = substr($item->language, 0, 3);
					}
					else
					{
						$lang = '';
					}
				}
				elseif (!$multilang)
				{
					$lang = '';
				}
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="text-center">
							<span class="tbody-icon">
								<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
							</span>
					</td>

					<th scope="row">
						<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
							. ' data-id="' . $item->id . '"'
							. ' data-title="' . $this->escape($item->series_text) . '"'
							. ' data-uri="' . $this->escape(CWMRouteHelper::getSeriesRoute($item->id, (int) $item->language)) . '"'
							. ' data-language="' . $this->escape($lang) . '"';
						?>
						<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
							<?php echo $this->escape($item->series_text); ?>
						</a>
						<div class="small break-word">
							<?php if (empty($item->note)) : ?>
								<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
							<?php else : ?>
								<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
							<?php endif; ?>
						</div>
					</th>
					<td class="small d-none d-md-table-cell">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<?php if ($multilang) : ?>
						<td class="small">
							<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
						</td>
					<?php endif; ?>
					<td class="small d-none d-md-table-cell">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php // load the pagination. ?>
		<?php echo $this->pagination->getListFooter(); ?>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
