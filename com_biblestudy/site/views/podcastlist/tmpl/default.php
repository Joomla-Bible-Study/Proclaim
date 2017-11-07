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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('biblestudy.framework');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'ordering';

$jbsmedia = new JBSMMedia;
?>
<h2><?php echo JText::_('JBS_CMN_PODCASTS_LIST'); ?></h2>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=podcastlist'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div id="effect-1" class="effects">
		<?php foreach ($this->items as $item)
		{
			$img_base      = pathinfo($item->series_thumbnail);
			$originalFile = $item->series_thumbnail;

			if (file_exists(JPATH_ROOT . '/' . $originalFile) && $img_base['basename'])
			{
				$array    = explode('.', $img_base['basename']);
				$NewfileName = $img_base['dirname'] . '/' . $array[0] . '-200.' . $array[1];
				$img = JBSMImageLib::getSeriesPodcast($originalFile, $NewfileName);
			}
			else
			{
				$img = null;
			}
			?>
            <div class="jbsmimg">
				<?php echo JHtml::image($img, $item->id . ' : ' . stripslashes($item->series_text), $this->attribs); ?>
                <div class="overlay">
                    <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=podcastdisplay&id=' .
	                    $item->id . ':' . $item->alias); ?>" class="expand">+</a>
                    <p class="expand"><?php echo stripslashes($item->series_text); ?></p>
                    <a class="jbsmclose-overlay hidden">x</a>
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
	<?php echo JHtml::_('form.token'); ?>
</form>
