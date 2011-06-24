<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */


//No Direct Access
defined('_JEXEC') or die('Restricted access'); 
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
include_once (JPATH_COMPONENT_ADMINISTRATOR .DS. 'helpers' .DS. 'translated.php');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=topicslist'); ?>" method="post" name="adminForm" id="adminForm">
<fieldset id="filter-bar">
    <div class="filter-select fltrt">

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
   </div>
</fieldset>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="20" align="center">
				<?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?>
			</th>			
			<th>
				<?php echo JText::_( 'JBS_CMN_TOPICS' ); ?>
			</th>
		</tr>			
	</thead>
    	<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	<?php
	foreach ($this->items as $i => $item) :
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&task=topicsedit.edit&id='. (int) $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
			<td class="center">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'topicslist.', true, 'cb', '', ''); ?>
                    </td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo getTopicItemTranslated($item); ?></a>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>

<input type="hidden" name="task" value=""/>
                        <input type="hidden" name="boxchecked" value="0"/>
                        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
                        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
</form>
