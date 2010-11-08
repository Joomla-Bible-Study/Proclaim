<?php defined('_JEXEC') or die('Restricted access'); 

 
	jimport('joomla.filesystem.file')
    
    
?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
<table>
<tr>

<td>	<?php //echo $this->lists['bookid'];?>
<?php $database	= & JFactory::getDBO();
$mainframe = JFactory::getApplication('com_biblestudy');
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
							if ($bookvalue == $filter_book){
		                        echo '<option value="'.$bookvalue.'" selected="selected">'.$output.'</option>';
							}else{
								echo '<option value="'.$bookvalue.'">'.$output.'</option>';
							}
                        };
                         echo '</select>';?>
		<?php echo $this->lists['teacher_id'];?>
		<?php echo $this->lists['seriesid'];?>
		<?php echo $this->lists['messagetypeid'];?>
 		<?php echo $this->lists['studyyear'];?>
		<?php //echo $this->lists['sorting'];?>
         
                          <?php 
						$query8 = 'SELECT DISTINCT #__bsms_studytopics.topic_id AS value, #__bsms_topics.topic_text AS text'
						. ' FROM #__bsms_studytopics'
						. ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
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
							if ($topicsvalue == $filter_topic){
								$selected = 'selected="selected"';
	                        	echo '<option value="'.$topicsvalue.'" selected="selected">'.$output.'</option>';
							}else{
								echo '<option value="'.$topicsvalue.'">'.$output.'</option>';
							}
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
		  <th align="center"><?php echo JHTML::_( 'grid.sort', 'Hits', 'hits', $this->lists['order_Dir'], $this->lists['order']); ?></th>
          <th align="center"><?php echo JHTML::_( 'grid.sort', 'Plays', 'totalplays', $this->lists['order_Dir'], $this->lists['order']); ?></th>
          <th align="center"><?php echo JHTML::_( 'grid.sort', 'Downloads', 'totaldownloads', $this->lists['order_Dir'], $this->lists['order']); ?></th>
          
        </tr>
      </thead>
      <?php 
      //Checks Url if its Valid
      function checkUrl($url) {
		  $return_val = FALSE; 
		  $status_codes        = array("200","302"); // see function header for code details
		  $url_info=parse_url($url); 
		  $port=isset($url_info['port']) ? $url_info['port'] : 80;
		  $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 10);
		  if(!$fp) return FALSE;
		  stream_set_timeout($fp, 10);
		  $head = "HEAD ".@$url_info['path']."?".@$url_info['query'];
		  $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
		  fputs($fp, $head);
		  if($header=trim(fgets($fp, 1024))) {
		    $header_array = explode(': ',$header);
		    while((list(, $status_code)= each($status_codes)) && $return_val==FALSE) {
		      if( strstr($header_array[0], $status_code)) {
		        $return_val = TRUE;
		      }
		    }
		  }
		  fclose($fp);
		  return $return_val;
		}
		
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	
	{
		$row = &$this->rows[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=' . $option . '&controller=studiesedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );
	//	$date	= JHTML::_('date',  $row->studydate, JText::_('DATE_FORMAT_LC3'),'$offset' );
        $date	= JHTML::_('date',  $row->studydate, '',$offset );
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
		<td align="center"><?php echo $row->hits; ?></td>
        <td align="center"><?php echo $row->totalplays; ?></td>
        <td align="center"><?php echo $row->totaldownloads; ?></td>
      </tr>
      <?php
		$k = 1 - $k;
		unset($brokenLink);
	}
	?>
      <tfoot><tr>
      <td colspan="12"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="studiesedit" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
