<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */


//No Direct Access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'JBS_CMN_ID' ); ?>
			</th>
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
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&task=topicsedit.edit&id='. (int) $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="5">
				<?php echo $row->id; ?>
			</td>
			<td width="20">
				<?php echo $checked; ?>
			</td>
			<td width="20" align="center">
				<?php echo $published; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->topic_text; ?></a>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="topicsedit" />
</form>
