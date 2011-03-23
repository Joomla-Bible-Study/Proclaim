<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
JHtml::_('script', 'system/multiselect.js', false, true);
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'mediafile.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=medialist'); ?>" method="post" name="adminForm" id="adminForm">
<fieldset id="filter-bar">
    <div class="filter-select fltrt">

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
   </div>
</fieldset>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)"/>
                </th>
                <th width="1%">
                    <?php echo JText::_('JPUBLISHED'); ?>
                </th>
                <th width="1%">
                    <?php
                        echo JText::_('JBS_CMN_IMAGE');
                    ?>
                </th>
                <th width=77%">
                    <?php
                        echo JText::_('JBS_CMN_MEDIA');
                    ?>
                </th>               
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="4">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <?php 
        foreach($this->items as $i => $item) :
            $ordering = ($listOrder == 'mediafile.ordering');
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="center">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="center">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'mediafileslist.', true, 'cb', '', ''); ?>
            </td>
            <td class="order">
               <?php //echo $this->directory;
               $path = JURI::base().'../';
               if ($item->path2)
               {
                    if (!substr_count($item->path2,'/')) {$image = $this->directory.'/'.$item->path2;}
                    else
                    {
                        $image = $item->path2;
                    }
               }
               else
               {
                    $image = $item->media_image_path;
                    $path = '../';
               }
               
               ?>
              <img src=" <?php echo $path.$image; ?>" alt="<?php echo $item->media_alttext;?>"/>
            </td>
            <td class="left">
                <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=mediaedit.edit&id='.(int)$item->id); ?>">
                    <?php echo $this->escape($item->media_image_name); ?>
                </a>
            </td>            
        </tr>
        <?php endforeach; ?>
    </table>
    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
