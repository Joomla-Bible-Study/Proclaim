<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('biblestudy.framework');
JHtml::_('behavior.multiselect');

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
	<?php foreach ($this->items as $item)
	{ ?>
        <div >
            <h2><?php echo $item->id . ' : ' . stripslashes($item->series_text); ?></h2>
            <div class="row-flued span12" style="margin-left: 0;">
			<?php foreach ($item->messages as $message)
			{ ?>
                <div class="span2" style="margin-left: 0;"><h3><?php echo stripslashes($message->studytitle); ?></h3></div>
                <div class="span5" style="margin-left: 0;"><?php foreach ($message->mediafiles as $media)
					{
						$media->params->set('player', 7);
						$media->params->set('popup', 2);
						$media->params->set('internal_popup', 2);
						echo $jbsmedia->getFluidMedia($media, $media->params, $this->template);
					} ?>
                </div>
			<?php } ?>
            </div>
        </div>
        <div style="clear: both;"></div>
	<?php } ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
