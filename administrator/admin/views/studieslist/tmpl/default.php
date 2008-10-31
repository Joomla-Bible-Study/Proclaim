<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php 
	jimport('joomla.filesystem.file')
?>
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
          <th width="5"> <?php echo JHTML::_( 'grid.sort','ID','id', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>
          <th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" /> 
		  <!-- changed $this->items to rows in above -->
          </th>
          <th width="20" align="center"><?php echo JHTML::_('grid.sort','Published','published',$this->lists['order_Dir'],$this->lists['order']); ?></th>
		  <th><?php echo JHTML::_('grid.sort','Date','studydate',$this->lists['order_Dir'],$this->lists['order']); ?></th>
          <th><?php echo JHTML::_('grid.sort', 'Type', 'messagetype', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th><?php echo JHTML::_( 'grid.sort','Scripture', 'booknumber', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th><?php echo JHTML::_( 'grid.sort','Teacher' , 'teacher_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		  <th><?php echo JHTML::_( 'grid.sort', 'Title', 'studytitle', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th><?php echo JHTML::_( 'grid.sort', 'Series', 'series_id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		  <th><?php echo JHTML::_( 'grid.sort','Topic','topics_id', $this->lists['order_Dir'], $this->lists['order'] );?></th>
		  <th><?php echo JHTML::_('grid.sort', 'Files', 'media_files', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		  <th align="center"><?php echo JHTML::_( 'grid.sort', 'Hits', 'hits', $this->lists['order_Dir'], $this->lists['order']); ?></th>
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
		
		//Check the mediafiles associated with the study
		foreach($this->mediaFiles[$row->id] as $studyMediaFiles) {
			$url = $studyMediaFiles['server_path'].$studyMediaFiles['folderpath'].$studyMediaFiles['filename'];
			$headers = get_headers($url);
			if(!stristr($headers[0],'OK')) {
				$brokenLink = true;
			}
		}
		if($brokenLink && count($this->mediaFiles[$row->id]) == 1) {
			$mediaStatus = '<img border="0" alt="1 Bad" src="'.JPATH_COMPONENT_SITE.DS.'images'.DS.'1bad.png">';
		}elseif($brokenLink && count($this->mediaFiles[$row->id] > 1)) {
			$mediaStatus = '<img border="0" alt="Multiple Bad" src="'.JPATH_COMPONENT_SITE.DS.'images'.DS.'multiple.png">';
		}else{
			$mediaStatus = '<img border="0" alt="All Good" src="'.JPATH_COMPONENT_SITE.DS.'images'.DS.'good.png">';
		}
		?>
      <tr class="<?php echo "row$k"; ?>"> 
        <td> <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
        <td> <?php echo $checked; ?> </td>
        <td align="center"> <?php echo $published; ?> </td>
		<td> <a href="<?php echo $link; ?>"><?php echo $date; ?></a> </td>
        <td><?php echo $row->id; ?></td>
        <td><?php echo $row->bookname; echo ' '; echo $row->chapter_begin; echo ':'; echo $row->verse_begin; echo '-'; echo $row->chapter_end; echo ':'; echo $row->verse_end;?></td>
        <td><?php echo $row->teachername; ?></td>
		<td><?php echo $row->studytitle; ?></td>
        <td><?php echo $row->series_text; ?></td>
		<td><?php echo $row->topic_text; ?></td>
		<td align="center"><?php echo $mediaStatus; ?></td>
		<td align="center"><?php echo $row->hits; ?></td>
      </tr>
      <?php
		$k = 1 - $k;
		unset($brokenLink);
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
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
