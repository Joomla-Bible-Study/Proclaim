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
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('biblestudy.framework');
JHtml::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'ordering';

$jbsmedia = new JBSMMedia;
?>
<div class="container-fluid">
    <div class="span6">
		<?php echo $this->item->image; ?>
        <h2><?php echo JText::_($this->item->series_text); ?></h2>
        <p class="description"><?php echo $this->item->description; ?></p>
    </div>

	<?php if (!empty($this->media))
	{
		?>
        <div class="span6">
            <?php $this->params->set('player_width', ''); ?>
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
			{
				// Sparams are the server parameters
				$reg = new Joomla\Registry\Registry;
				$reg->loadString($item->sparams);
				$item->sparams = $reg;

				// Params are the individual params for the media file record
				$reg = new Joomla\Registry\Registry;
				$reg->loadString($item->params);
				$item->params = $reg;
				?>
                <tr>
					<?php $path1 = JBSMHelper::MediaBuildUrl($item->sparams->get('path'), $item->params->get('filename'), $item->params, true);?>
                    <td>
						<?php echo stripslashes($item->studytitle); ?>
                    </td>
                    <td>
						<?php echo JHtml::Date($item->createdate); ?>
                    </td>
                    <td class="row">
                        <a href="javascript:loadVideo('<?php echo $path1; ?>', '<?php echo $item->series_thumbnail; ?>')">
                            <?php echo JText::_('JBS_CMN_LISTEN'); ?>
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
