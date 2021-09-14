<?php
/**
 * Modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', ['event'], 'lt IE 9');
JHtml::_('script', 'com_content/administrator-articles-modal.min.js', ['version' => 'auto', 'relative' => true]);
JHtml::_('bootstrap.tooltip', '.hasTooltip', ['placement' => 'bottom']);
JHtml::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', ['title' => JText::_($searchFilterDesc), 'placement' => 'bottom']);

$function  = $app->input->getCmd('function', 'jSelectStudy');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	Factory::getDocument()->addScriptOptions('xtd-articles', ['editor' => $editor]);
	$onclick = "jSelectArticle";
}
?>
<div class="container-popup">
	<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messages&layout=modal&tmpl=component&function=' .
		$function . '&' . JSession::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm"
	      id="adminForm" class="form-inline">

		<?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

		<div class="clearfix"></div>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-condensed">
				<thead>
				<tr>
					<th width="8%">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'study.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_MESSAGETYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="12">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
				$iconStates = [
					-2 => 'icon-trash',
					0  => 'icon-unpublish',
					1  => 'icon-publish',
					2  => 'icon-archive',
				];
				?>
				<?php
				foreach ($this->items as $i => $item) :
					?>
					<?php if ($item->language && JLanguageMultilang::isEnabled())
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
				elseif (!JLanguageMultilang::isEnabled())
				{
					$lang = '';
				}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>"></span>
						</td>
						<td>
							<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
								. ' data-id="' . $item->id . '"'
								. ' data-title="' . $this->escape(addslashes($item->studytitle)) . '"'
								. ' data-uri=" "'
								. ' data-language="' . $this->escape($lang) . '"';
							?>
							<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>">
							<?php echo $this->escape($item->studytitle); ?>
							</a>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo JHtml::_('date', $item->studydate, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->teachername); ?>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->messageType); ?>
						</td>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->series_text); ?>
						</td>
						<td class="small">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="forcedLanguage"
		       value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
