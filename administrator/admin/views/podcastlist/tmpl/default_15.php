<?php

/**
 * @version     $Id: viewj16.html.php 1328 2011-01-06 04:14:05Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
?>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
      <thead>
        <tr>
          <th width="5"> <?php echo JText::_( 'JBS_CMN_ROW' ); ?> </th>
          <th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
          </th>
          <th width="20" align="center"> <?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?>
          </th>
          <th> <?php echo JText::_( 'JBS_PDC_PODCAST' ); ?> </th>
          <th><?php echo JText::_( 'JBS_CMN_ID' ); ?></th>
        </tr>
      </thead>
      <?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&controller=podcastedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
      <tr class="<?php echo "row$k"; ?>">
        <td width="5"> <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
        <td width="20"> <?php echo $checked; ?> </td>
        <td width="20" align="center"> <?php echo $published; ?> </td>
        <td> <a href="<?php echo $link; ?>"><?php echo $row->title; ?></a>
        </td>
        <td><?php echo $row->id; ?>
		</td>
      </tr>
      <?php
		$k = 1 - $k;
	}
	?>

      <tfoot><tr>
      <td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="podcastlist" />
</form>
