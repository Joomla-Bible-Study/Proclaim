<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;
use CWM\Component\Proclaim\Administrator\Helper\CWMImageLib;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HtmlHelper::_('proclaim.framework');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder === 'ordering';

$CWMedia = new CWMMedia;
?>
<h2><?php echo Text::_('JBS_CMN_PODCASTS_LIST'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=podcastlist'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div id="effect-1" class="effects">
		<?php foreach ($this->items as $item)
		{
			$originalFile = $item->series_thumbnail;

			if (!empty($originalFile) && file_exists(JPATH_ROOT . '/' . $originalFile))
			{
				$img = CWMImageLib::getSeriesPodcast($originalFile);
			}
			else
			{
				$img = CWMImageLib::getSeriesPodcast($this->params->get('default_study_image'));
			}
			?>
            <div class="CWMimg">
				<?php echo HtmlHelper::image($img, $item->id . ' : ' . stripslashes($item->series_text), $this->attribs); ?>
                <div class="overlay">
                    <a href="<?php echo Route::_('index.php?option=com_proclaim&view=podcastdisplay&id=' .
	                    $item->id . ':' . $item->alias); ?>" class="expand">+</a>
                    <p class="expand"><?php echo stripslashes($item->series_text); ?></p>
                    <a class="CWMclose-overlay hidden">x</a>
                </div>
            </div>
		<?php } ?>
    </div>
    <div style="clear: both"></div>
	<?php if ($this->params->get('show_pagination', 2)) : ?>
        <div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter" style="padding-top: 10px">
					<?php echo $this->pagination->getPagesCounter(); ?>
                </p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
        </div>
	<?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HtmlHelper::_('form.token'); ?>
</form>
