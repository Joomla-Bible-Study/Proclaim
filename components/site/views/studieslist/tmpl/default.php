<?php defined('_JEXEC') or die('Restricted access'); ?>

<script
	type="text/javascript" src="components/com_biblestudy/tooltip.js"></script>
<link
	href="components/com_biblestudy/tooltip.css" rel="stylesheet"
	type="text/css" media="screen" />

<style type="text/css">
/* CSS goes here */
#
a[title]:hover :after { /*Shows the generated content*/
	content: attr(title) " (" attr(href) ")";
	visibility: visible;
}
#
</style>

<?php
global $mainframe, $option;
$message = JRequest::getVar('msg');
$database	= & JFactory::getDBO();
$teacher_menu = $this->params->get('teacher_id', 1);
$topic_menu = $this->params->get('topic_id', 1);
$book_menu = $this->params->get('booknumber', 101);
$location_menu = $this->params->get('locations', 1);
$series_menu = $this->params->get('series_id', 1);
$messagetype_menu = $this->params->get('messagetype', 1);
$imageh = $this->params->get('imageh', 24);
$imagew = $this->params->get('imagew', 24);
$color1 = $this->params->get('color1');
$color2 = $this->params->get('color2');
$page_width = $this->params->get('page_width');
if (!$page_width){
	$page_width = '100%';
}
$widpos1 = $this->params->get('widthcol1');
$widpos2 = $this->params->get('widthcol2');
$widpos3 = $this->params->get('widthcol3');
$widpos4 = $this->params->get('widthcol4');
$show_description = $this->params->get('show_description', 1);
$downloadCompatibility = $this->params->get('compatibilityMode');
?>
<table width="<?php echo $page_width; ?>">
<?php //Overall table for the listing page?>
	<form action="<?php echo $this->request_url; ?>" method="post"
		name="adminForm"><?php 

		$user =& JFactory::getUser();
		$entry_user = $user->get('gid');
		if (!$entry_user) { $entry_user = 0;}
		$entry_access = $this->params->get('entry_access');
		if (!$entry_access) {$entry_access = 23;}
		$allow_entry = $this->params->get('allow_entry_study');
		if (!$entry_user) { $entry_user = 0; }
		if (!$entry_access) { $entry_access = 23; }
		if ($allow_entry > 0) {
			if ($entry_access <= $entry_user){
				if ($message) {?>
	
	
	<tr>
		<td align="center"><?php echo '<h2>'.$message.'</h2>';
		//if ($this->params->get('study_publish') < 1){echo JText::_('Submissions must be approved prior to publication').'<br>';}?></td>
	</tr>
	<?php } //End of if $message
	?>

	</td>
	</tr>
	<?php //} //End of if $message?>
	<tr>
		<td><strong><?php echo JText::_('Studies');?></strong></td>
	</tr>
	<tr>
		<td><a
			href="<?php echo JURI::base()?>index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form"><?php echo JText::_('Add a New Study');?></a></td>
	</tr>
	<tr>
		<td><a
			href="<?php echo JURI::base()?>index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form"><?php echo JText::_('Add a New Media File Record');?></a></td>
	</tr>
	<?php if ($this->params->get('show_comments') > 0){?>
	<tr>
		<td><a
			href="<?php echo JURI::base()?>index.php?option=com_biblestudy&view=commentslist"><?php echo JText::_('Manage Comments');?></a></td>
	</tr>
	<?php } //end if show_comments?>
	<?php 	} //End of testing for if user is authorized
		}//End of testing for $allow_entry?>
		<?php // Here we start the test to see if podcast entry allowed
		if ($this->params->get('allow_podcast') > 0){
			$podcast_access = $this->params->get('podcast_access');
			if (!$podcast_access) {$podcast_access = 23;}
			if ($podcast_access <= $entry_user){
				$query = ('SELECT id, title, published FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC');
				$database->setQuery( $query );
				$podcasts = $database->loadAssocList();
				?>
	<tr>
		<td><strong><?php
		echo JText::_('Podcasts').'</td></tr>';
		?></strong>
	
	
	<tr>
		<td><?php //This is a row to hold the podcast listings
		?><a
			href="<?php echo JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form';?>"><?php echo JText::_('Add A Podcast');?></a></td>
	</tr>
	<tr>
		<td><?php foreach ($podcasts as $podcast) { $pod = $podcast['id']; $podtitle = $podcast['title'];
		?>
	
	
	<tr>
		<td><a
			href="<?php echo JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form&task=edit&cid[]='.$pod;?>"><?php echo $podtitle;?></a></td>
	</tr>
	<?php } // end foreach for podcasts as podcast
	// End row for podcast?>
	</td>
	</tr>
	<?php
			} // end of checking podcast authorization
		} // end allow_entry_podcast
		?>
	<tr>
	<?php //This is the beginning of the row for the page table?>
		<td><?php //This is the beginning of the column for the page table?>
		<table width="100%">
		<?php //Table to hold the logo?>
			<tr>
			<?php //Row for logo?>
			<?php $wtd = $this->params->get('pimagew');?>
			<?php if ($this->params->get( 'show_page_image' ) >0) {
				$src = JURI::base().$this->params->get('page_image');
				$pimagew = $this->params->get('pimagew');
				$pimageh = $this->params->get('pimageh');
				if ($pimagew) {$width = $pimagew;} else {$width = 24;}
				if ($pimageh) {$height = $pimageh;} else {$height= 24;}
				?>

				<td width="<?php echo $width +2; ?>"><?php //Column  for logo?> <img
					src="<?php echo JURI::base().$this->params->get('page_image');?>"
					alt="<?php JText::_('Bible Studies');?>"
					width="<?php echo $width;?>" height="<?php echo $height;?>"
					border="0" /></td>
					<?php //End of column for logo?>
					<?php } //End of if statement for show page image?>
					<?php if ( $this->params->get( 'show_page_title_list' ) >0 ) { ?>
				<td><?php //Column 2 for logo?>
				<H1
					class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
					<?php echo $this->params->get('page_title'); ?></H1>

				</td>
				<?php //End of column2 for logo?>
				<?php } //End of if show page title?>
			</tr>
			<?php //End of row for logo table?>

		</table>
		<?php //End of table for logo?></td>
	</tr>
	<?php //end of row above drop down boxes?>
	<?php if ($this->params->get('show_teacher_list') >0) { ?>
	<tr>
		<td cellpadding="2"
			width="<?php echo $this->params->get('teacherw');?>"><img
			src="<?php echo $this->params->get('teacherimage');?>" border="1"
			width="<?php echo $this->params->get('teacherw');?>"
			height="<?php echo $this->params->get('teacherh');?>" /><br />
			<?php echo $this->params->get('teachername');?></td>
	</tr>
	<?php }?>
	<tr>
		<td><?php //Row for drop down boxes?> <?php //This is the column that holds the search drop downs?>

		<?php if ($this->params->get('show_locations_search') > 0 && !($location_menu)) { echo $this->lists['locations'];}?>
		<?php if ($this->params->get('show_book_search') >0 && !($book_menu) ){ ?>

		<?php $query2 = 'SELECT id, booknumber AS value, bookname AS text, published'
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
				$output = JText::_($format);
				$bookvalue = $bookid2['value'];
				if ($bookvalue == $filter_book){$selected = 'selected="selected"';
				echo '<option value="'.$bookvalue.'"'.$selected.' >'.$bookid2['text'].'</option>';}
				echo '<option value="'.$bookvalue.'">'.$output.'</option>';
			};
			echo '</select>';?> <?php } ?> <?php if ($this->params->get('show_teacher_search') >0 && !($teacher_menu)) { ?>
			<?php echo $this->lists['teacher_id'];?> <?php } ?> <?php if ($this->params->get('show_series_search') >0 && !($series_menu)){ ?>
			<?php echo $this->lists['seriesid'];?> <?php } ?> <?php if ($this->params->get('show_type_search') >0 && !($messagetype_menu)) { ?>
			<?php echo $this->lists['messagetypeid'];?> <?php } ?> <?php if ($this->params->get('show_year_search') >0){ ?>
			<?php echo $this->lists['studyyear'];?> <?php } ?> <?php if ($this->params->get('show_order_search') >0) { ?>
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
				echo '</select>';?> <?php //echo $this->lists['sorting'];?> <?php } ?>
				<?php if ($this->params->get('show_topic_search') >0) { ?> <?php
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
					echo '</select>';?> <?php //echo $this->lists['topics'];?> <?php } ?>

		</td>
	</tr>
	<?php //End of row for drop down boxes?>

	<?php // The table to hold header rows ?>

	<table width="<?php echo $this->params->get('header_width');?>">
	<?php //mirrors 6 colum table below?>
		<tr>
		<?php // begin array for positions to see if we need a column for the header
		$headercheck = array( array( 	'position' => $this->params->get('position1')),
		array( 	'position' => $this->params->get('position2')),
		array( 	'position' => $this->params->get('position3')),
		array( 	'position' => $this->params->get('position4')),
		array( 	'position' => $this->params->get('position5')),
		array( 	'position' => $this->params->get('position6')),
		array( 	'position' => $this->params->get('position7')),
		array( 	'position' => $this->params->get('position8')),
		array( 	'position' => $this->params->get('position9')),
		array( 	'position' => $this->params->get('position10')),
		array( 	'position' => $this->params->get('position11')),
		array( 	'position' => $this->params->get('position12')),
		array( 	'position' => $this->params->get('position13')),
		array( 	'position' => $this->params->get('position14')),
		array( 	'position' => $this->params->get('position15')),
		array( 	'position' => $this->params->get('position16')),
		array( 	'position' => $this->params->get('position17')),
		array(	'position' => $this->params->get('position18'))
		); //print_r($headercheck);
		?>
		<?php

		//Beginning of header rows
		$isheader1 = 0;
		$isheader2 = 0;
		$isheader3 = 0;
		$isheader4 = 0;
		if ($this->params->get('use_headers') >0) {
			//$header_count = count($headercheck);
			//dump ($header_count, 'Header_count');
			$rows1=count($headercheck);
			for($j=0;$j<$rows1;$j++)
			{
				if ($headercheck[$j]['position']==1){ $isheader1 = 1;}
				if ($headercheck[$j]['position']==2){ $isheader2 = 1;}
				if ($headercheck[$j]['position']==3){ $isheader3 = 1;}
				if ($headercheck[$j]['position']==4){ $isheader4 = 1;}
			}
			if ($isheader1 == 1)
			{echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header1_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header1').'</span></th>';}
			if ($isheader2 == 1)
			{echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header2_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header2').'</span></th>';}
			if ($isheader3 == 1)
			{echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header3_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header3').'</span></th>';}
			if ($isheader4 == 1)
			{echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header4_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header4').'</span></th>';}
			?>
		</tr>
	</table>
	<?php
		} // end of if use headers
		//End of Header rows?>

		<?php //End of table for header rows?>



		<?php //This is where each result from the database of studies is diplayed with options for each 6 column table?>

		<?php
		function format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e) {
			global $mainframe, $option;
			$params =& $mainframe->getPageParameters();
			//$this->params = &JComponentHelper::getParams($option);
			$db	= & JFactory::getDBO();
			$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = '.$booknumber;
			$db->setQuery($query);
			$bookresults = $db->loadObject();
			$book=$bookresults->bookname;
			$book = JText::sprintf($book);
			$b1 = ' ';
			$b2 = ':';
			$b2a = ':';
			$b3 = '-';
			$b3a = '-';
			if ($params->get('show_verses') >0)
			{
				$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
				if ($ch_e == $ch_b) {
					$ch_e = '';
					$b2a = '';
				}
				if ($v_b == 0){
					$v_b = '';
					$v_e = '';
					$b2a = '';
					$b2 = '';
				}
				if ($v_e == 0) {
					$v_e = '';
					$b2a = '';
				}
				if ($ch_e == 0) {
					$b2a = '';
					$ch_e = '';
					if ($v_e == 0) {
						$b3 = '';
					}
				}
				$scripture = $book.$b1.$ch_b.$b2.$v_b.$b3.$ch_e.$b2a.$v_e;
			}
			else
			{
				if ($ch_e > $ch_b) {
					$scripture = $book.$b1.$ch_b.$b3.$ch_e;
				}
				else {
					$scripture = $book.$b1.$ch_b;
				}
			}
			return $scripture;
		}
		$k = 1;
		$row_count = 0;
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{ // This is the beginning of a loop that will cycle through all the records according to the query?>
		<?php $bgcolor = ($row_count % 2) ? $color1 : $color2; //This code cycles through the two color choices made in the parameters?>
		<?php $row = &$this->items[$i]; ?>
		<?php
		$query = 'SELECT #__bsms_mediafiles.*,'
		. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
		. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
		. ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,'
		. ' #__bsms_media.media_alttext AS malttext,'
		. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
		. ' FROM #__bsms_mediafiles'
		. ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
		. ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
		. ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
		. ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
		. ' WHERE #__bsms_mediafiles.study_id LIKE '.$row->id.' LIMIT 1';
		$database->setQuery( $query );
		$filesize = $database->loadObject();
		$number_rows = $database->getAffectedRows($query);
		if ($number_rows > 0) {
			$file_size = $filesize->size;
			switch ($file_size ) {

				case $file_size < 1024 :
					$file_size = $file_size.' '.'Bytes';
					break;
				case $file_size < 1048576 :
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size,0);
					$file_size = $file_size.' '.'KB';
					break;
				case $file_size < 1073741824 :
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size,1);
					$file_size = $file_size.' '.'MB';
					break;
				case $file_size > 1073741824 :
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size,1);
					$file_size = $file_size.' '.'GB';
					break;
			}

		}	//end of else for file_size

		/* Now we do this small line which is basically going to tell
		 PHP to alternate the colors between the two colors we defined above. */
		$bgcolor = ($row_count % 2) ? $color1 : $color2;
		?>
		<?php
		if (!$filesize){
		}
		else {
			if ($number_rows > 0) {$filepath = 'http://'.$filesize->spath.$filesize->fpath.$filesize->filename;} else {$filepath = '';}
		}
		$show_media = $this->params->get('show_media',1);
		$filesize_showm = $this->params->get('filesize_showm');
		$link = JRoute::_('index.php?option=com_biblestudy&view=studydetails&id=' . $row->id);
		$duration = $row->media_hours.$row->media_minutes.$row->media_seconds;
		$duration_type = $this->params->get('duration_type');
		switch ($duration_type) {
			case 1:
				$duration = $row->media_hours.$row->media_minutes.$row->media_seconds;
				if (!$duration){
				}
				else {
					if (!$row->media_hours){
						$duration = $row->media_minutes.' mins '.$row->media_seconds.' secs';
					}
					else {
						$duration = $row->media_hours.' hour(s) '.$row->media_minutes.' mins '.$row->media_seconds.' secs';
					}
				}
				break;
			case 2:
				$duration = $row->media_hours.$row->media_minutes.$row->media_seconds;
				if (!$duration){
				}
				else {
					if (!$row->media_hours){
						$duration = $row->media_minutes.':'.$row->media_seconds;
					}
					else {
						$duration = $row->media_hours.':'.$row->media_minutes.':'.$row->media_seconds;
					}
				}
				break;
		} // end switch


		$booknumber = $row->booknumber;
		$ch_b = $row->chapter_begin;
		$ch_e = $row->chapter_end;
		$v_b = $row->verse_begin;
		$v_e = $row->verse_end;
		$scripture1 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e);
		if ($row->booknumber2){
			$booknumber = $row->booknumber2;
			$ch_b = $row->chapter_begin2;
			$ch_e = $row->chapter_end2;
			$v_b = $row->verse_begin2;
			$v_e = $row->verse_end2;
			$scripture2 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e);
		}
		$df = 	($this->params->get('date_format'));
		switch ($df)
		{
			case 0:
				$date	= date('M j, Y', strtotime($row->studydate));
				break;
			case 1:
				$date	= date('M j', strtotime($row->studydate) );
				break;
			case 2:
				$date	= date('n/j/Y',  strtotime($row->studydate));
				break;
			case 3:
				$date	= date('n/j', strtotime($row->studydate));
				break;
			case 4:
				$date	= date('l, F j, Y',  strtotime($row->studydate));
				break;
			case 5:
				$date	= date('F j, Y',  strtotime($row->studydate));
				break;
			case 6:
				$date = date('j F Y', strtotime($row->studydate));
				break;
			case 7:
				$date = date('j/n/Y', strtotime($row->studydate));
				break;
			case 8:
				$date = JHTML::_('date', $row->studydate, JText::_('DATE_FORMAT_LC'));
				break;
			default:
				$date = date('n/j', strtotime($row->studydate));
				break;
		}
		$textwidth=$this->params->get('imagew');
		$textwidth = ($textwidth + 1);
		$storewidth = $this->params->get('storewidth');
		$teacher = $row->teachername;
		$study = $row->studytitle;
		$sname = $row->series_text;
		$intro = str_replace('"','',$row->studyintro);
		$mtype = $row->message_type;
		$snumber = $row->studynumber;
		$details_text = $this->params->get('details_text');
		$filesize_show = $this->params->get('filesize_show');
		$secondary = $row->secondary_reference;
		if (!$row->booknumber2){$scripture2 = '';}
		if ($number_rows < 1) {$file_size = '0';}
		$a = array( array( 	'element' => $scripture1,
					'position' => $this->params->get('position1'),
					'order' => $this->params->get('order1'),
					'islink' => $this->params->get('islink1'),
					'span' => $this->params->get('span1'),
					'isbullet' => $this->params->get('isbullet1')
		),
		array(	'element' => $row->studytitle,
					'position' => $this->params->get('position2'),
					'order' => $this->params->get('order2'),
					'islink' => $this->params->get('islink2'),
					'span' => $this->params->get('span2'),
					'isbullet' => $this->params->get('isbullet2')
		),
		array(	'element' => $duration,
					'position' => $this->params->get('position3'),
					'order' => $this->params->get('order3'),
					'islink' => $this->params->get('islink3'),
					'span' => $this->params->get('span3'),
					'isbullet' => $this->params->get('isbullet3')
		),
		array(	'element' => $row->studyintro,
					'position' => $this->params->get('position4'),
					'order' => $this->params->get('order4'),
					'islink' => $this->params->get('islink4'),
					'span' => $this->params->get('span4'),
					'isbullet' => $this->params->get('isbullet4')
		),
		array(	'element' => $date,
					'position' => $this->params->get('position5'),
					'order' => $this->params->get('order5'),
					'islink' => $this->params->get('islink5'),
					'span' => $this->params->get('span5'),
					'isbullet' => $this->params->get('isbullet5')
		),
		array(	'element' => $row->teachername,
					'position' => $this->params->get('position6'),
					'order' => $this->params->get('order6'),
					'islink' => $this->params->get('islink6'),
					'span' => $this->params->get('span6'),
					'isbullet' => $this->params->get('isbullet6')
		),
		array(	'element' => $row->teachername.' - '.$row->teachertitle,
					'position' => $this->params->get('position7'),
					'order' => $this->params->get('order7'),
					'islink' => $this->params->get('islink7'),
					'span' => $this->params->get('span7'),
					'isbullet' => $this->params->get('isbullet7')
		),
		array(	'element' => $row->teachertitle.' - '.$row->teachername,
					'position' => $this->params->get('position10'),
					'order' => $this->params->get('order10'),
					'islink' => $this->params->get('islink10'),
					'span' => $this->params->get('span10'),
					'isbullet' => $this->params->get('isbullet10')
		),
		array(	'element' => $file_size,
					'position' => $this->params->get('position8'),
					'order' => $this->params->get('order8'),
					'islink' => $this->params->get('islink8'),
					'span' => $this->params->get('span8'),
					'isbullet' => $this->params->get('isbullet8')
		),
		array(	'element' => $row->series_text,
					'position' => $this->params->get('position9'),
					'order' => $this->params->get('order9'),
					'islink' => $this->params->get('islink9'),
					'span' => $this->params->get('span9'),
					'isbullet' => $this->params->get('isbullet9')
		),
		array(	'element' => "<br />",
					'position' => $this->params->get('blank1'),
					'order' => $this->params->get('blankorder1'),
					'islink' => 0,
					'span' => 0,
					'isbullet' => 0,
		),
		array(	'element' => "<br />",
					'position' => $this->params->get('blank2'),
					'order' => $this->params->get('blankorder2'),
					'islink' => 0,
					'span' => 0,
					'isbullet' => 0,
		),
		array(	'element' => "<br />",
					'position' => $this->params->get('blank3'),
					'order' => $this->params->get('blankorder3'),
					'islink' => 0,
					'span' => 0,
					'isbullet' => 0,
		),
		array(	'element' => "<br />",
					'position' => $this->params->get('blank4'),
					'order' => $this->params->get('blankorder4'),
					'islink' => 0,
					'span' => 0,
					'isbullet' => 0,
		),
		array(	'element' => $row->secondary_reference,
					'position' => $this->params->get('position11'),
					'order' => $this->params->get('order11'),
					'islink' => $this->params->get('islink11'),
					'span' => $this->params->get('span11'),
					'isbullet' => $this->params->get('isbullet11'),
		),
		array(	'element' => $scripture2,
					'position' => $this->params->get('position13'),
					'order' => $this->params->get('order13'),
					'islink' => $this->params->get('islink13'),
					'span' => $this->params->get('span13'),
					'isbullet' => $this->params->get('isbullet13'),
		),
		array(	'element' => $row->user_name,
					'position' => $this->params->get('position14'),
					'order' => $this->params->get('order14'),
					'islink' => $this->params->get('islink14'),
					'span' => $this->params->get('span14'),
					'isbullet' => $this->params->get('isbullet14')
		),
		array(	'element' => $this->params->get('hits_label').': '.$row->hits,
					'position' => $this->params->get('position15'),
					'order' => $this->params->get('order15'),
					'islink' => $this->params->get('islink15'),
					'span' => $this->params->get('span15'),
					'isbullet' => $this->params->get('isbullet15')
		),
		array(	'element' => $row->studynumber,
					'position' => $this->params->get('position16'),
					'order' => $this->params->get('order16'),
					'islink' => $this->params->get('islink16'),
					'span' => $this->params->get('span16'),
					'isbullet' => $this->params->get('isbullet16')
		),
		array(	'element' => $row->topic_text,
					'position' => $this->params->get('position17'),
					'order' => $this->params->get('order17'),
					'islink' => $this->params->get('islink17'),
					'span' => $this->params->get('span17'),
					'isbullet' => $this->params->get('isbullet17')
		),
		array(	'element' => $row->location_text,
					'position' => $this->params->get('position18'),
					'order' => $this->params->get('order18'),
					'islink' => $this->params->get('islink18'),
					'span' => $this->params->get('span18'),
					'isbullet' => $this->params->get('isbullet18')
		),
		array(	'element' => $row->message_type,
					'position' => $this->params->get('position12'),
					'order' => $this->params->get('order12'),
					'islink' => $this->params->get('islink12'),
					'span' => $this->params->get('span11'),
					'isbullet' => $this->params->get('isbullet12'),
		)
		);
		// Obtain a list of columns
		foreach ($a as $key => $arow) {
			$position[$key]  = $arow['position'];
			$order[$key] = $arow['order'];
		}
		//Remove all rows in the array that show the element should not be displayed


		// Sort the data with position and order ascending
		// Add $a as the last parameter, to sort by the common key
		array_multisort($position, SORT_ASC, $order, SORT_ASC, $a);

		//Copy the array into four so we can deal with them individually in each column
		$column1 = $a;
		$column2 = $a;
		$column3 = $a;
		$column4 = $a;
		$color = $this->params->get('use_color');
		?>

	<tr>
		<td><?php //Beginning of row for 6 column table?> <?php if ($this->params->get('line_break') > 0) {echo '<br />'; } ?>
		<table <?php if ($color > 0){echo 'bgcolor="'.$bgcolor.'"';}?>
			width="<?php echo $page_width; ?>" cellpadding="0" cellspacing="0">
			<?php //6 Column table?>
			<tr valign="<?php echo $this->params->get('colalign');?>">
			<?php //Row for 6 column table?>

			<?php //Remove all rows in the array that are not Column1
			$rows1=count($column1);
			for($j=0;$j<$rows1;$j++)
			{
				if ($column1[$j]['position']!=1)
				{
					unset($column1[$j]);
				}
			}

			//$count1 = count($column1['element']);
			$column1 = array_values($column1);
			//print_r ($column1);
			//echo 'Rows1: '.$rows1;
			?>
			<?php if (isset($column1[0]['position'])) { //This tests to see if there is anything in column1?>
			<?php if ($entry_user >= $entry_access){//This adds a <td> for user frontend editing of the record?>
				<td width="10" valign="<?php echo $this->params->get('colalign');?>"><a
					href="<?php echo JURI::base();?>index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]=<?php echo $row->id;?>"><?php echo JText::_('[Edit]');?></a></td>
					<?php } //End of front end user authorized to edit records?>
				<td width="<?php echo $widpos1;?>"><?php //Column 1 of 5?>
				<table border="<?php echo $this->params->get('border');?>"
					cellpadding="<?php echo $this->params->get('padding');?>"
					cellspacing="<?php echo $this->params->get('spacing');?>">
					<?php //This is the table for the list. It's outside the foreach?>
					<?php

					//Now let's assign some elements and go through each of them.
					foreach ($column1 as $c1) {
						$element1 = $c1['element'];
						$position1 = $c1['position'];
						$isbullet1=$c1['isbullet'];
						$span1=$c1['span'];
						$islink1=$c1['islink'];
						?>

					<tr valign="<?php echo $this->params->get('colalign');?>">
					<?php //We make a new row and td for each record in this column ?>
						<td valign="<?php echo $this->params->get('colalign');?>"><?php 
						//Now we produce each element in turn with its parameters
						echo '<span '.$span1.'>';
						if ($isbullet1 == 1) {
							echo '<ul><li>'; }
							switch ($islink1) {
								case 1 :
									$link1 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
									echo '<a href="'.$link1.'">';
									break;
								case 2 :
									$link1 = JRoute::_($filepath);
									echo '<a href="'.$link1.'">';
									break;
							}
							echo $element1;
							if ($islink1 > 0) { echo '</a>'; }
							if ($isbullet1 == 1) { echo '</li></ul>';}
							echo '</span>';
							?></td>
					</tr>
					<?php //This is tne end of each td and row for the list ?>
					<?php
					} //End of foreach Column1
					?>
				</table>
				<?php //This ends the table inside of column 1 that holds the actual listings. ?>



				</td>
				<?php }//End of column 1 of 5 Also end of if ($column1->position)?>

				<?php //Remove all rows in the array that are not Column 2
				$rows2=count($column2);
				for($j=0;$j<$rows2;$j++)
				{
					if ($column2[$j]['position']!=2)
					{
						unset($column2[$j]);
					}
				}
				$column2 = array_values($column2);
				//print_r($column2);
				//$testit = strlen($column2[0]['element']); echo 'Testit: '.$testit;
				?>
				<?php if (isset($column2[0]['position'])) { //This tests to see if there is anything in column2?>
				<td width="<?php echo $widpos2;?>"><?php //Beginning of Column 2 of 5?>

				<table border="<?php echo $this->params->get('border');?>"
					cellpadding="<?php echo $this->params->get('padding');?>"
					cellspacing="<?php echo $this->params->get('spacing');?>">
					<?php //This is the table for the list. It's outside the foreach?>
					<?php

					//Now let's assign some elements and go through each of them.
					foreach ($column2 as $c2) {
						$element2 = $c2['element'];
						$position2 = $c2['position'];
						$isbullet2=$c2['isbullet'];
						$span2=$c2['span'];
						$islink2=$c2['islink'];
						?>

					<tr valign="<?php echo $this->params->get('colalign');?>">
					<?php //We make a new row and td for each record in this column ?>
						<td valign="<?php echo $this->params->get('colalign');?>"><?php 
						//Now we produce each element in turn with its parameters
						echo '<span '.$span2.'>';
						if ($isbullet2 == 1) {
							echo '<ul><li>'; }
							switch ($islink2) {
								case 1 :
									$link2 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
									echo '<a href="'.$link2.'">';
									break;
								case 2 :
									$link2 = JRoute::_($filepath);
									echo '<a href="'.$link2.'">';
									break;
							}
							echo $element2;
							if ($islink2 > 0) { echo '</a>'; }
							if ($isbullet2 == 1) { echo '</li></ul>';}
							echo '</span>';
							?></td>
					</tr>
					<?php //This is tne end of each td and row for the list ?>
					<?php
					} //End of foreach $column2
					?>
				</table>
				<?php //This ends the table inside of column 2 that holds the actual listings. It is outside the foreach loop?>


				</td>
				<?php }//End of Column 2 of 5 And if($column2->position?>

				<?php //Remove all rows in the array that are not Column1
				$rows3=count($column3);
				for($j=0;$j<$rows3;$j++)
				{
					if ($column3[$j]['position']!=3)
					{
						unset($column3[$j]);
					}
				}
				$column3 = array_values($column3);
				//$testit3 = count($column3[0]['position']); echo 'Testit: '.$testit3;
				//print_r($column3);?>
				<?php if (isset($column3[0]['position'])) { //This tests to see if there is anything in column3?>
				<td width="<?php echo $widpos3;?>"><?php //Begin Column 3 of 5?>

				<table border="<?php echo $this->params->get('border');?>"
					cellpadding="<?php echo $this->params->get('padding');?>"
					cellspacing="<?php echo $this->params->get('spacing');?>">
					<?php //This is the table for the list. It's outside the foreach?>
					<?php

					//Now let's assign some elements and go through each of them.
					foreach ($column3 as $c3) {
						$element3 = $c3['element'];
						$position3 = $c3['position'];
						$isbullet3=$c3['isbullet'];
						$span3=$c3['span'];
						$islink3=$c3['islink'];
						?>

					<tr valign="<?php echo $this->params->get('colalign');?>">
					<?php //We make a new row and td for each record in this column ?>
						<td valign="<?php echo $this->params->get('colalign');?>"><?php 
						//Now we produce each element in turn with its parameters
						echo '<span '.$span3.'>';
						if ($isbullet3 == 1) {
							echo '<ul><li>'; }
							switch ($islink3) {
								case 1 :
									$link3 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
									echo '<a href="'.$link3.'">';
									break;
								case 2 :
									$link3 = JRoute::_($filepath);
									echo '<a href="'.$link3.'">';
									break;
							}
							echo $element3;
							if ($islink3 > 0) { echo '</a>'; }
							if ($isbullet3 == 1) { echo '</li></ul>';}
							echo '</span>';
							?></td>
					</tr>
					<?php //This is tne end of each td and row for the list ?>
					<?php
					} //End of foreach $column3
					?>
				</table>
				<?php //This ends the table inside of column 3 that holds the actual listings. It is outside the foreach loop?>

				</td>
				<?php }//End of Column 3 of 5 and $jf($column3->position)?>

				<?php //Remove all rows in the array that are not Column1
				$rows4=count($column4);
				for($j=0;$j<$rows4;$j++)
				{
					if ($column4[$j]['position']!=4)
					{
						unset($column4[$j]);
					}
				}
				$column4 = array_values($column4);
				//print_r($column4);?>
				<?php if (isset($column4[0]['position'])) { //This tests to see if there is anything in column4?>
				<td width="<?php echo $widpos4;?>"><?php //Begin Column 4 of 5?>

				<table border="<?php echo $this->params->get('border');?>"
					cellpadding="<?php echo $this->params->get('padding');?>"
					cellspacing="<?php echo $this->params->get('spacing');?>">
					<?php //This is the table for the list. It's outside the foreach?>
					<?php

					//Now let's assign some elements and go through each of them.
					foreach ($column4 as $c4) {
						$element4 = $c4['element'];
						$position4 = $c4['position'];
						$isbullet4=$c4['isbullet'];
						$span4=$c4['span'];
						$islink4=$c4['islink'];
						?>


					<tr valign="<?php echo $this->params->get('colalign');?>">
					<?php //We make a new row and td for each record in this column ?>
						<td valign="<?php echo $this->params->get('colalign');?>"><?php 
						//Now we produce each element in turn with its parameters
						echo '<span '.$span4.'>';
						if ($isbullet4 == 1) {
							echo '<ul><li>'; }
							switch ($islink4) {
								case 1 :
									$link4 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
									echo '<a href="'.$link4.'">';
									break;
								case 2 :
									$link4 = JRoute::_($filepath);
									echo '<a href="'.$link4.'">';
									break;
							}
							echo $element4;
							if ($islink4 > 0) { echo '</a>'; }
							if ($isbullet4 == 1) { echo '</li></ul>';}
							echo '</span>';
							?></td>
					</tr>
					<?php //This is tne end of each td and row for the list ?>
					<?php
					} //End of forach $column4
					?>
				</table>
				<?php //This ends the table inside of column 4 that holds the actual listings. It is outside the foreach loop?>

				</td>
				<?php }//End of Column 4 of 5 and if($column4->position)?>

				<?php if (($this->params->get('show_full_text') + $this->params->get('show_pdf_text')) > 0) { //Tests to see if show text and/or pdf is set to "show"?>

				<td width="<?php echo $textwidth;?>"><?php //Column 5 of 6 column table - this is to hold the text and pdf images/links?>
				<table align="left">
					<tr valign="<?php echo $this->params->get('colalign');?>">

					<?php if ($this->params->get('show_full_text') > 0) { ?>
					<?php $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id ); ?>
					<?php JHTML::_('behavior.tooltip');
					?>
						<td><?php //This is the beginning of the column for the text image ?>
						<?php if ($this->params->get('tooltip') >0) { ?> <span
							class="zoomTip"
							title="<strong>Sermon Info:</strong> ::<?php if ($study) {?><strong>Title:</strong> <?php echo $study;}?><br><br> <?php if ($intro) {?><strong>Details:</strong> <?php echo $intro;?><?php } ?><br><br><?php if ($snumber) {?><strong>Sermon Number:</strong> <?php echo $snumber;}?> <br><strong>Teacher:</strong> <?php echo $teacher;?><br><br><hr /><br><?php if ($scripture1) {?><strong>Scripture: </strong><?php echo $scripture1;?><?php } ?>">
							<?php } //end of is show tooltip?> <?php
							$src = JURI::base().$this->params->get('text_image');
							if ($imagew) {$width = $imagew;} else {$width = 24;}
							if ($imageh) {$height = $imageh;} else {$height= 24;}
							?> <a href="<?php echo $link; ?>"><img
							src="<?php echo JURI::base().$this->params->get('text_image');?>"
							alt="<?php echo $details_text;?>" width="<?php echo $width;?>"
							height="<?php echo $height;?>" border="0"></a><?php if ($this->params->get('tooltip') >0) { ?></span><?php } ?>
						</td>
						<?php //End of text column ?>

						<?php } // end of show_full_text if ?>

						<?php if ($this->params->get('show_pdf_text') > 0) { ?>
						<?php $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf' ); ?>

						<td><?php $src = JURI::base().$this->params->get('pdf_image');
						if ($imagew) {$width = $imagew;} else {$width = 24;}
						if ($imageh) {$height = $imageh;} else {$height= 24;}
						?> <a href="<?php echo $link; ?>" target="_blank"
							title="<?php echo $details_text;?>"><img
							src="<?php echo JURI::base().$this->params->get('pdf_image');?>"
							alt="<?php echo $details_text.JText::_('- PDF Version');?>"
							width="<?php echo $width;?>" height="<?php echo $height;?>"
							border="0"></a></td>
							<?php //This is the end of the column for the pdf image?>

							<?php } // End of show pdf text ?>
					</tr>
				</table>
				</td>
				<?php //End column 5 of 6?>

				<?php } //This is the end of the if statement to see if text and/or pdf images set to "show"?>
				<?php if ($this->params->get('show_store') > 0){?>

				<td width="<?php echo $storewidth;?>"><?php //This td is for the store column?>
				<?php $query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
				.' s.image_cd, s.prod_cd, s.server_cd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_cd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_cd )
                        WHERE s.id ='.$row->id;
				$database->setQuery($query);
				$cd = $database->loadObject(); ?> <?php $query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
				.' s.image_dvd, s.prod_dvd, s.server_dvd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_dvd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_dvd )
                        WHERE s.id ='.$row->id;
				$database->setQuery($query);
				$dvd = $database->loadObject();
				if (($cd->mid + $dvd->mid) > 0) {?>

				<table>
					<tr>
					<?php

					if ($cd->mid > 0){
						$src = JURI::base().$cd->media_image_path;
						if ($imagew) {$width = $imagew;} else {$width = 24;}
						if ($imageh) {$height = $imageh;} else {$height= 24;}
						?>
						<td><?php echo '<a href="'.$cd->server_path.$cd->prod_cd.'" title="'.$cd->media_alttext.'"><img src="'.JURI::base().$cd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$cd->media_alttext.' "border="0"></a>';?></td>
						<?php } ?>
						<?php if ($dvd->mid > 0){
							$src = JURI::base().$dvd->media_image_path;
							if ($imagew) {$width = $imagew;} else {$width = 24;}
							if ($imageh) {$height = $imageh;} else {$height= 24;}
							?>
						<td><?php echo '<a href="'.$dvd->server_path.$dvd->prod_dvd.'" title="'.$dvd->media_alttext.'"><img src="'.JURI::base().$dvd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$dvd->media_alttext.' "border="0"></a>';?></td>
						<?php } ?>
					</tr>
					<tr>
						<td colspan="2" align="center"><span
						<?php echo $this->params->get('store_span');?>><?php echo $this->params->get('store_name');?></span></td>
					</tr>
				</table>
				<?php }?></td>
				<?php  }//End of store column?>

				<?php if ($this->params->get('show_media') > 0) { ?>

				<td width="<?php echo $this->params->get('media_width');?>"><?php //Column 6 of 6 column table. This column holds the media?>

				<?php $query_media1 = 'SELECT #__bsms_mediafiles.*,'
				. ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
				. ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
				. ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,'
				. ' #__bsms_media.media_alttext AS malttext,'
				. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
				. ' FROM #__bsms_mediafiles'
				. ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
				. ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
				. ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
				. ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
				. ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC';
				$database->setQuery( $query_media1 );
				$media1 = $database->loadObjectList('id');
					
				?>

				<table align="left">
					<tr valign="<?php echo $this->params->get('colalign');?>">

					<?php

					foreach ($media1 as $media) {
						$download_image = $this->params->get('download_image');
						if (!$download_image) { $download_image = 'components/com_biblestudy/images/download.png';}
						$link_type = $media->link_type;
						$media_size = $media->size;
						$useplayer = 0;
						if ($this->params->get('media_player') > 0) {
							//Look to see if it is an mp3
							$ismp3 = substr($media->filename,-3,3);
							if ($ismp3 == 'mp3'){$useplayer = 1;}else {$useplayer = 0;}
						} //End if media_player param test
						if (!$media_size){ $media_size = '';
						}
						else {
							switch ($media_size ) {

								case $media_size < 1024 :
									$media_size = $media_size.' '.'Bytes';
									break;
								case $media_size < 1048576 :
									$media_size = $media_size / 1024;
									$media_size = number_format($media_size,0);
									$media_size = $media_size.' '.'KB';
									break;
								case $media_size < 1073741824 :
									$media_size = $media_size / 1024;
									$media_size = $media_size / 1024;
									$media_size = number_format($media_size,1);
									$media_size = $media_size.' '.'MB';
									break;
								case $media_size > 1073741824 :
									$media_size = $media_size / 1024;
									$media_size = $media_size / 1024;
									$media_size = $media_size / 1024;
									$media_size = number_format($media_size,1);
									$media_size = $media_size.' '.'GB';
									break;
							}

						}	//end of else for media_size
						$filesize = $media_size;
						$mimetype = $media->mimetext;
						$src = JURI::base().$media->impath;
						if ($imagew) {$width = $imagew;} else {$width = 24;}
						if ($imageh) {$height = $imageh;} else {$height= 24;}
						$ispath = 0;
						if (!$media->filename){
							$path1 = '';
							$ispath = 0;
						}
						else {
							$path1 = $media->spath.$media->fpath.$media->filename;
							$isslash = substr_count($path1,'//');
							if (!$isslash) {
								$path1 = 'http://'.$path1;
							}
							$pathname = $media->fpath;
							$filename = $media->filename;
							$ispath = 1;
							$direct_link = '<a href="'.$path1.'" title="'.$media->malttext.' '.$duration.' '
							.$media_size.'" target="'.$media->special.'"><img src="'.JURI::base().$media->impath
							.'" alt="'.$media->imname.' '.$duration.' '.$media_size.'" width="'.$width
							.'" height="'.$height.'" border="0" /></a>';
						}
						$isavr = 0;
						if (JPluginHelper::importPlugin('system', 'avreloaded'))
						{
							$isavr = 1;
							$studyfile = $media->spath.$media->fpath.$media->filename;
							$mediacode = $media->mediacode;
							//dump ($mediacode, 'mediacode');
							$isrealfile = substr($media->filename, -4, 1);
							$fileextension = substr($media->filename,-3,3);
							if ($mediacode == ''){
								$mediacode = '{'.$fileextension.'remote}-{/'.$fileextension.'remote}';
							}
							$mediacode = str_replace("'",'"',$mediacode);
							$ispop = substr_count($mediacode, 'popup');
							if ($ispop < 1) {
								$bracketpos = strpos($mediacode,'}');
								$mediacode = substr_replace($mediacode,' popup="true" ',$bracketpos,0);
							}
							$isdivid = substr_count($mediacode, 'divid');
							if ($isdivid < 1) {
								$dividid = ' divid="'.$media->id.'"';
								$bracketpos = strpos($mediacode, '}');
								$mediacode = substr_replace($mediacode, $dividid,$bracketpos,0);
							}
							$isonlydash = substr_count($mediacode, '}-{');
							if ($isonlydash == 1){
								$ishttp = substr_count($studyfile, 'http://');
								if ($ishttp < 1) {
									//We want to see if there is a file here or if it is streaming by testing to see if there is an extension
									$isrealfile = substr($media->filename, -4, 1);
									if ($isrealfile == '.') {
										$isslash = substr_count($studyfile,'//');
										if (!$isslash) {
											$studyfile = substr_replace($studyfile,'http://',0,0);
										}
									}
								}

								if ($isrealfile != '.')
								{
									$studyfile = $media->filename;
								}
								$mediacode = str_replace('-',$studyfile,$mediacode);
							}
							$popuptype = 'window';
							if($this->params->get('popuptype') != 'window') {
								$popuptype = 'lightbox';
							}
							$avr_link = $mediacode.'{avrpopup type="'.$popuptype.'" id="'.$media->id
							.'"}<img src="'.JURI::base().$media->impath.'" alt="'.$media->imname
							.' '.$duration.' '.$media_size.'" width="'.$width
							.'" height="'.$height.'" border="0" title="'
							.$media->malttext.' '.$duration.' '.$media_size.'"/>{/avrpopup}';
							//dump ($avr_link, 'AVR Lnk');

						}
						$useavr = 0;
						$useavr = $useavr + $this->params->get('useavr') + $media->internal_viewer;
						$isfilesize = 0;
						if ($file_size > 0)
						{
							$isfilesize = 1;
							$media1_sizetext = '<span style="font-size:0.60em;">'.$media_size.'</span>';
						}
						else {$media1_sizetext = '';}
						$media1_link = $direct_link;

						if ($useavr > 0)
						{ $media1_link = $avr_link;
						//dump ($avr_link, 'AVR Link');
							
						}
						if ($useplayer == 1){
							$player_width = $this->params->get('player_width');
							if (!$player_width) { $player_width = '290'; }
							$media1_link =
					'<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer'.$row_count.'" height="24" width="290">
<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
<param name="FlashVars" value="playerID='.$row_count.'&amp;soundFile='.$path1.'">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object> ';}
							?>
							<?php
							/**
							 * @desc: I hope to in the future load media files using this method
							 */
							/*		echo ('<div class="inlinePlayer" id="media-'.$media->id.'"></div>');
							 echo ('<a href="'.$path1.'" class="btnPlay" alt="'.$media->id.'">Play</a>');*/


							/*$abspath    = JPATH_SITE;
							 require_once($abspath.DS.'components/com_biblestudy/classes/class.biblestudymediadisplay.php');
							 $inputtype = 0;
							 $media_display = new biblestudymediadisplay($row->id, $inputtype);
							 $media_display->id = $row->id;
							 $media_display->inputtype = 0;*/

							?>
						<td align="left"><?php //dump ($media1_link, 'Media1_link'); ?> <?php  echo $media1_link; ?>
						<?php if ($this->params->get('show_filesize') > 0)
						{ ?> <?php echo $media1_sizetext;

						}?> <?php if ($link_type > 0){ $src = JURI::base().$download_image;
						if ($this->params->get('download_side') > 0) { echo '<td>';}
						if ($imagew) {$width = $imagew;} else {$width = 24;}
						if ($imageh) {$height = $imageh;} else {$height= 24;}
						if($downloadCompatibility == 0) {
							echo '<a href="index.php?option=com_biblestudy&id='.$media->id.'&view=studieslist&controller=studieslist&task=download">';
						}else{
							echo('<a href="http://joomlaoregon.com/router.php?file='.$media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">');
						}
						?> <img src="<?php echo JURI::base().$download_image;?>"
							alt="<?php echo JText::_('Download');?>"
							height="<?php echo $height;?>" width="<?php echo $width;?>"
							title="<?php echo JText::_('Download');?>" /></a> <?php if ($this->params->get('download_side') > 0) { echo '</td>';}}?>

						</td>


						<?php } //end of foreach of media results?>
					</tr>
				</table>
				<?php //This ends the table that holds the media images and is outside the foreach?>
				</td>
				<?php //End column 6 of 6?>

				<?php } //This is the end of the if show media statement ?>

			</tr>
			<?php //End row for 6 column table?>
		</table>
		<?php //End 6 Column table?>
		<table width="<?php echo $page_width; ?>"
		<?php if ($color > 0){echo 'bgcolor="'.$bgcolor.'"';}?>>
			<?php if ($show_description > 0) { ?>
			<tr>
				<td><?php  echo '<span '.$this->params->get('descriptionspan').'> '.$row->studyintro.'</span>'; ?>
				</td>
			</tr>
			<?php if ($this->params->get('line') > 0) { ?>
			<tr>
				<td width="<?php echo $this->params->get('mod_table_width');?>"><?php //This row is to hold the line and should run along the bottom of the 5 column table?>
				<?php echo '<img src="'.JURI::base().'components/com_biblestudy/images/square.gif" height="2" width="100%">'; ?>
				<?php } //End of if show lines?></td>
			</tr>
			<?php } // End show description?>

		</table>

		<?php	  	$row_count++; // This increments the row count and adjusts the variable for the color background
		$k = 3 - $k; ?> <?php } //This is the end of the for statement for each result from the database that will create its own 6 column table?>

		</td>
	</tr>
	<?php //End of row for 6 column table?>


	</td>
	<?php //This is the end of the column for the overall table of the listing page?>
	</tr>
	<?php //This is the end of the row for the overall table of the listing page?>
	<tr>
	<?php //This is a row for the footer ?>
	
	
	<tfoot>
		<td align="center"><?php 
		echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
		echo $this->pagination->getLimitBox();
		echo $this->pagination->getPagesLinks();
		echo $this->pagination->getPagesCounter();
		//echo $this->pagination->getListFooter(); ?><?php //Column for footer?>
		</td>
		<?php //End footer column?>
	</tfoot>
	</tr>
	<?php //End footer row?>
	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="studieslist" />
	</form>
</table>
	<?php //This is the end of the table for the overall listing page?>
