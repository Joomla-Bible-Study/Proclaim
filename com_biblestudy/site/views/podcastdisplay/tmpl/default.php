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
<div class="container-fluid">
    <div class="span4">
		<?php echo $this->item->image; ?>
        <h2><?php echo JText::_($this->item->series_text); ?></h2>
    </div>

	<?php if (!empty($this->media))
	{
		?>
        <div class="span7">
			<?php echo $jbsmedia->getFluidMedia($this->media[0], $this->params, $this->template); ?>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>
					<?php echo JText::_('JBS_CMN_TITLE'); ?>
                </th>
                <th>
					<?php echo JText::_('JBS_CPL_DATE'); ?>
                </th>
                <th>

                </th>
            </tr>
            </thead>
			<?php foreach ($this->media as $item)
			{ ?>
                <tr>
					<?php $jbsmedia->getFluidMedia($item, $this->params, $this->template); ?>
                    <td>
						<?php echo stripslashes($item->studytitle); ?>
                    </td>
                    <td>
						<?php echo JHtml::Date($item->createdate); ?>
                    </td>
                    <td class="row"><a
                                href="javascript:loadVideo('<?php echo $item->path1; ?>', '<?php echo $item->series_thumbnail; ?>')">
                            Listen
                        </a>
                    </td>
                </tr>
			<?php } ?></table>
	<?php }
	else
	{ ?>
        <div style="clear: both"></div>
        <p><?php echo JText::_('JBS_CMN_NO_PODCASTS'); ?></p>
		<?php
	} ?>
</div>
