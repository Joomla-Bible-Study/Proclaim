<?php
/**
 * Types html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// Check to ensure this file is included in Joomla!
use CWM\Component\Proclaim\Site\Helper\CWMRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

defined('_JEXEC') or die;

$app = Factory::getApplication();

if ($app->isClient('site'))
{
	Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_proclaim.cwmadmin-types-modal');

$function  = $app->input->getCmd('function', 'jSelectTypes');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);
$multilang = Multilanguage::isEnabled();

if (!empty($editor))
{
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	$this->document->addScriptOptions('xtd-types', array('editor' => $editor));
	$onclick = "jSelectTypes";
}
?>
<div class="container-popup">
	<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmservers&layout=types&tmpl=component&function=' .
		$function . '&' . Session::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm"
	      id="adminForm" class="form-inline">

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->types)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span
						class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-sm">
				<caption class="visually-hidden">
					<?php echo Text::_('JBS_CMN_MESSAGE_TABLE_CAPTION'); ?>,
					<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
					<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
				<tr>
					<th scope="col" class="w-1 text-center">
						<?php echo Text::_('JBS_CMN_TITLE'); ?>
					</th>
					<th scope="col" class="title">
						<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_TITLE', 'type.title', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" class="w-25">
						<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_SERIES', 'type.description', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($this->types as $i => $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap small hidden-phone">
							<img class="pull-left" style="padding: 5px;" src="<?php echo $item->image_url; ?>"
							     alt="<?php echo $item->title; ?>"
						</td>
						<th scope="row">
							<?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
								. ' data-id="' . $item->type . '"'
								. ' data-title="' . $this->escape($item->title) . '"'
								. ' data-uri="' . $this->escape(CWMRouteHelper::getTypeRoute($item->type, '*')) . '"'
								. ' data-language="*"';
							?>
							<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>">
							<?php echo $this->escape($item->title); ?>
						</th>
						<td class="small hidden-phone">
							<?php echo $this->escape($item->description); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
