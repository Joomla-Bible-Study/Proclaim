<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
$mainframe = & JFactory::getApplication();
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=sharelist'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                    </th>
                    <th width="20" align="center">
<?php echo JText::_('JBS_CMN_PUBLISHED'); ?>
                    </th>
                    <th><?php echo JText::_('JBS_CMN_IMAGE'); ?></th>
                    <th>
<?php echo JText::_('JBS_SHR_SOCIAL_NETWORK'); ?>
                    </th>
                </tr>
            </thead>
<?php
foreach ($this->items as $i =>
    $item) :
$params = new JParameter($row->params);
$link = JRoute::_( 'index.php?option=com_biblestudy&task=shareedit.edit&id='. (int) $item->id );
?>
            <tr class="row<?php echo $i % 2; ?>">
                <td width="20">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td width="20" align="center">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'sharelist.', true, 'cb', '', ''); ?>
                </td>
                <td width="60" align="left">
                    <?php
                    $isweb = stristr($params->get('shareimage'), 'http');
                    if ($isweb) { echo '<img src="'.$params->get('shareimage').'">';
                    }
                    else {echo '<img src="'.$mainframe->getCfg('live_site').'/'.$params->get('shareimage').'">';
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo $link; ?>"><?php echo $item->name; ?></a>
                </td
            </tr>
            <?php endforeach; ?>
                    <tfoot>
                        <tr><td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
                </table>
            </div>
 <input type="hidden" name="task" value=""/>
                        <input type="hidden" name="boxchecked" value="0"/>
                        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
                        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>

</form>
