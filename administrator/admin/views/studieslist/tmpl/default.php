<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
<table>
<tr>

<td>	<?php //echo $this->lists['bookid'];?>
<?php $database	= & JFactory::getDBO();
$query2 = 'SELECT booknumber AS value, bookname AS text, published'
                        . ' FROM #__bsms_books'
                        . ' WHERE published = 1'
                        . ' ORDER BY booknumber';
						$database->setQuery( $query2 );
						$bookid = $database->loadAssocList();
						$filter_book		= $mainframe->getUserStateFromRequest( $option.'filter_book', 'filter_book',0,'int' );
						
						echo '<select name="filter_book" id="filter_book" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
						if (!$filter_book ) {
						echo 'selected="selected"';}
						echo '>- '.JText::_('Select a Book').' -'.'</option>';
                        foreach ($bookid as $bookid2) {
                        $format = $bookid2['text'];
                        $output = JText::sprintf($format);
                        $bookvalue = $bookid2['value'];
						if ($bookvalue == $filter_book){$selected = 'selected="selected"';
                        echo '<option value="'.$bookvalue.'"'.$selected.' >'.$output.'</option>';}
						echo '<option value="'.$bookvalue.'">'.$output.'</option>';
                        };
                         echo '</select>';?>
		<?php echo $this->lists['teacher_id'];?>
		<?php echo $this->lists['seriesid'];?>
		<?php echo $this->lists['messagetypeid'];?>
 		<?php echo $this->lists['studyyear'];?>
		<?php //echo $this->lists['sorting'];?>
         <?php
                    $query6 = ' SELECT * FROM #__bsms_order '
					. ' ORDER BY id ';
					$database->setQuery( $query6 );
					$sortorder = $database->loadAssocList();
					$filter_orders		= $mainframe->getUserStateFromRequest( $option.'filter_orders','filter_orders','DESC','word' );
					echo '<select name="filter_orders" id="filter_orders" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
						if (!$filter_orders ) {
						echo 'selected="selected"';}
						echo '>- '.JText::_('Select an Order').' -'.'</option>';
                        foreach ($sortorder as $sortorder2) {
                        $format = $sortorder2['text'];
                        $output = JText::sprintf($format);
                        $sortvalue = $sortorder2['value'];
						if ($sortvalue == $filter_orders){$selected = 'selected="selected"';
                        echo '<option value="'.$sortvalue.'"'.$selected.' >'.$output.'</option>';}
						echo '<option value="'.$sortvalue.'">'.$output.'</option>';
                        };
                         echo '</select>';?>
                          <?php 
						$query8 = 'SELECT DISTINCT #__bsms_studies.topics_id AS value, #__bsms_topics.topic_text AS text'
						. ' FROM #__bsms_studies'
						. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
						. ' WHERE #__bsms_topics.published = 1'
						. ' ORDER BY #__bsms_topics.topic_text ASC';
						$database->setQuery( $query8 );
						$topicsid = $database->loadAssocList();
						$filter_topic		= $mainframe->getUserStateFromRequest( $option.'filter_topic', 'filter_topic',0,'int' );
						echo '<select name="filter_topic" id="filter_topic" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
						if (!$filter_topic ) {
						echo 'selected="selected"';}
						echo '>- '.JText::_('Select a Topic').' -'.'</option>';
                        foreach ($topicsid as $topicsid2) {
                        $format = $topicsid2['text'];
                        $output = JText::sprintf($format);
                        $topicsvalue = $topicsid2['value'];
						if ($topicsvalue == $filter_topic){$selected = 'selected="selected"';
                        echo '<option value="'.$topicsvalue.'"'.$selected.' >'.$output.'</option>';}
						echo '<option value="'.$topicsvalue.'">'.$output.'</option>';
                        };
                         echo '</select>';?>
		<?php //echo $this->lists['topics'];?></td>

</tr>
<!--<tr><td>Pagination: <?php //print_r($this->pagination);?></td></tr>-->
</table>
<div id="editcell">
	<table class="adminlist">
      <thead>
        <tr> 
          <th width="5"> <?php echo JText::_( 'ID' ); ?> </th>
          <th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" /> 
		  <!-- changed $this->items to rows in above -->
          </th>
          <th width="20" align="center"> <?php echo JText::_( 'Published' ); ?> 
          </th>
		  <th> <?php echo JText::_( 'Date' ); ?> </th>
          <th><?php echo JText::_( 'Type' ); ?></th>
          <th><?php echo JText::_( 'Scripture' ); ?></th>
          <th><?php echo JText::_( 'Teacher' ); ?></th>
		  <th><?php echo JText::_( 'Title' ); ?></th>
          <th><?php echo JText::_( 'Series' ); ?></th>
		  <th><?php echo JText::_( 'Topic' );?></th>
		  <th align="center"><?php echo JText::_( 'Hits'); ?></th>
        </tr>
      </thead>
      <?php
	
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	
	{
		$row = &$this->rows[$i];
		
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=' . $option . '&controller=studiesedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );
		$date	= JHTML::_('date',  $row->studydate, JText::_('DATE_FORMAT_LC3') );
		?>
      <tr class="<?php echo "row$k"; ?>"> 
        <td> <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
        <td> <?php echo $checked; ?> </td>
        <td align="center"> <?php echo $published; ?> </td>
		<td> <a href="<?php echo $link; ?>"><?php echo $date; ?></a> </td>
        <td><?php echo $row->message_type; ?></td>
        <td><?php echo $row->bookname; echo ' '; echo $row->chapter_begin; echo ':'; echo $row->verse_begin; echo '-'; echo $row->chapter_end; echo ':'; echo $row->verse_end;?></td>
        <td><?php echo $row->teachername; ?></td>
		<td><?php echo $row->studytitle; ?></td>
        <td><?php echo $row->series_text; ?></td>
		<td><?php echo $row->topic_text; ?></td>
		<td align="center"><?php echo $row->hits; ?> </td>
      </tr>
      <?php
		$k = 1 - $k;
	}
	?>
      <tfoot>
      <td colspan="12"> <?php echo $this->pagination->getListFooter(); ?> </td></tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="studiesedit" />
</form>
