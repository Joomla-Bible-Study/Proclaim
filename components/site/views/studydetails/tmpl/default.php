<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript" language="JavaScript">
function HideContent(d) {
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
document.getElementById(d).style.display = "block";
}
function ReverseDisplay(d) {
if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
else { document.getElementById(d).style.display = "none"; }
}
</script>


<?php

global $mainframe;

//$menu = JSite::getMenu();
//$item = $menu->getActive();
$message = JRequest::getVar('msg');
//$path =& JURI::base()
$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$esv = 0;
$imageh = $this->params->get('imageh', 24);
$imagew = $this->params->get('imagew', 24);
$downloadCompatibility = $this->params->get('compatibilityMode');
function format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e, $esv) {
	global $mainframe, $option;
	$params =& $mainframe->getPageParameters();
	//$params = &JComponentHelper::getParams($option);
	$db	= & JFactory::getDBO();
	$query = 'SELECT bookname, booknumber FROM #__bsms_books WHERE booknumber = '.$booknumber;
	$db->setQuery($query);
	$bookresults = $db->loadObject();
	if ($bookresults->bookname) {$book=$bookresults->bookname;} else {$book = '';}
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
	if ($esv = 1){
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
	return $scripture;
}


$booknumber = $this->studydetails->booknumber;
$ch_b = $this->studydetails->chapter_begin;
$ch_e = $this->studydetails->chapter_end;
$v_b = $this->studydetails->verse_begin;
$v_e = $this->studydetails->verse_end;
$scripture1 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e, $esv);

$booknumber = $this->studydetails->booknumber2;
$ch_b = $this->studydetails->chapter_begin2;
$ch_e = $this->studydetails->chapter_end2;
$v_b = $this->studydetails->verse_begin2;
$v_e = $this->studydetails->verse_end2;
if ($this->studydetails->booknumber2){$scripture2 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e, $esv);}
//if ($studydetails->secondary_reference) { $scripture .= ' - '.$studydetails->secondary_reference; }
$picture = $this->params->get('show_picture_view');
switch ($picture) {
	case 1:
		$image = $this->studydetails->image;
		$imageh = $this->studydetails->imageh;
		$imagew = $this->studydetails->imagew;
		$space = ($imagew + 2);
		break;
	case 2:
		$image = $this->studydetails->thumb;
		$imageh = $this->studydetails->thumbh;
		$imagew = $this->studydetails->thumbw;
		$space = ($imagew + 2);
		break;
}

$details_text = $this->params->get('details_text');
$filesize_show = $this->params->get('filesize_show');
$duration = $this->studydetails->media_hours.$this->studydetails->media_minutes.$this->studydetails->media_seconds;
$duration_type = $this->params->get('duration_type');
switch ($duration_type) {
	case 1:
		$duration = $this->studydetails->media_hours.$this->studydetails->media_minutes.$this->studydetails->media_seconds;
		if (!$duration){
		}
		else {
			if (!$this->studydetails->media_hours){
				$duration = $this->studydetails->media_minutes.' mins '.$this->studydetails->media_seconds.' secs';
			}
			else {
				$duration = $this->studydetails->media_hours.' hour(s) '.$this->studydetails->media_minutes.' mins '.$this->studydetails->media_seconds.' secs';
			}
		}
		break;
	case 2:
		$duration = $this->studydetails->media_hours.$this->studydetails->media_minutes.$this->studydetails->media_seconds;
		if (!$duration){
		}
		else {
			if (!$this->studydetails->media_hours){
				$duration = $this->studydetails->media_minutes.':'.$this->studydetails->media_seconds;
			}
			else {
				$duration = $this->studydetails->media_hours.':'.$this->studydetails->media_minutes.':'.$this->studydetails->media_seconds;
			}
		}
		break;
}
$this->assignRef ('duration', $duration);// end switch
?>
<table
	class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<tr>
	<?php if ($this->params->get('show_title_view') >0) : ?>
		<td
			class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>"
			width="100%"><?php echo '<span '.$this->params->get('style_title_view').'>'.$this->studydetails->studytitle.'</span>'; ?>
			<?php endif; ?> <?php if ($this->params->get('show_print_view') > 0) : ?>
		
		
		<td align="left" width="100%" class="buttonheading"><?php 
		$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ) );
		echo '<a href="#&tmpl=component" onclick="window.print();return false;">'.$text.'</a>';
		?></td>
		<?php endif; ?>
		<?php if ($this->params->get('show_pdf_view')) : ?>
		<td align="right" width="100%" class="buttonheading"><?php 
		$url = 'index.php?option=com_biblestudy&view=studydetails&id='.$this->studydetails->id.'&format=pdf';
		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		//if ($params->get('show_icons')) {
		$text = JHTML::_('image.site', 'pdf24.png', '/components/com_biblestudy/images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
		//} else {
		//$text = JText::_('PDF').'&nbsp;';
		//}

		$attribs['title']	= JText::_( 'PDF' );
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']     = 'nofollow';

		$link = JHTML::_('link', JRoute::_($url), $text, $attribs);
		echo $link; ?></td>
		<?php endif; ?>
	</tr>
	<?php if ($message) {?>
	<tr>
		<td align="center"><?php echo '<h2>'.$message.'</h2><br>';
		if ($this->params->get('comment_publish') < 1){echo JText::_('Submissions may need approval prior to publication').'<br>';}?></td>
	</tr>
	<?php } //End of if $message?>
	</tr>
	<tr>
		<td><span class="small"><?php echo '<strong>Scripture: </strong>'.$scripture1; ;
		if ($this->studydetails->booknumber2 > 0){echo ' - '.$scripture2;}
		if ($this->studydetails->secondary_reference) {echo ' - '.$this->studydetails->secondary_reference;}?>
		</span></td>
	</tr>
	<tr>
	<?php if ($this->params->get('show_picture_view') > 0) {

		?>
		<td width="<?php $space;?>"><span class="small"> <?php	echo '<img src="'.$image.'" width="'.$imagew.'" height="'.$imageh.'"><br />';

		if ($this->params->get('show_teacher_view') > 0){
			if (!$this->studydetails->tname) {
			}
			else { ?> <?php echo '<strong> By: </strong>';?> <?php echo $this->studydetails->tname; ?>

			<?php }
			echo '</span></td>';
		}
	} ?> <?php if ($this->params->get('show_picture_view') < 1) { echo '<td><span class="small">';
	if ($this->params->get('show_teacher_view')) {
		if ($this->studydetails->tname) {
			echo '<strong> By: </strong>';
			echo $this->studydetails->tname;
		}
	}
	echo '</span></td>';}?>
		
		
		<td valign="top"><span class="small"> </span> &nbsp;</td>
	</tr>
	<tr>
	<?php if ($this->params->get('show_date_view') > 0){ ?>
		<td valign="top"><?php
		$df = 	($this->params->get('date_format'));
		switch ($df)
		{
			case 0:
				$date	= date('M j, Y', strtotime($this->studydetails->studydate));
				break;
			case 1:
				$date	= date('M j', strtotime($this->studydetails->studydate) );
				break;
			case 2:
				$date	= date('n/j/Y',  strtotime($this->studydetails->studydate));
				break;
			case 3:
				$date	= date('n/j', strtotime($this->studydetails->studydate));
				break;
			case 4:
				$date	= date('l, F j, Y',  strtotime($this->studydetails->studydate));
				break;
			case 5:
				$date	= date('F j, Y',  strtotime($this->studydetails->studydate));
				break;
			case 6:
				$date = date('j F Y', strtotime($this->studydetails->studydate));
				break;
			case 7:
				$date = date('j/n/Y', strtotime($this->studydetails->studydate));
				break;
			case 8:
				$date = JHTML::_('date', $this->studydetails->studydate, JText::_('DATE_FORMAT_LC'));
				break;
			default:
				$date = date('n/j', strtotime($this->studydetails->studydate));
				break;
		}
		?> <?php if (!$date) {
		}
		else { ?> <span class="small"> <?php echo '<strong>'.JText::_('Date: ').'</strong>'.$date; ?>
		</span> <?php } 
	}?> <?php if ($this->params->get('show_locations') > 0) {
		if (!$this->studydetails->location_text) {} else {?> <span
			class="small"> <?php echo '<strong>'.JText::_('Location: ').'</strong>'.$this->studydetails->location_text; ?>
		</span> <?php } }?> <?php if ($this->params->get('show_series_view') > 0){ ?>
		<?php if (!$this->studydetails->stext) { ?> <?php }
		else { ?> <span class="small"> <?php echo '<strong>'.JText::_('Series: ').'</strong>'.$this->studydetails->stext; ?>
		</span> <?php } ?> <?php } ?> <?php if ($this->params->get('show_studynumber_view') > 0){ ?>
		<?php if (!$this->studydetails->studynumber) {
		}
		else { ?> <span class="small"> <?php echo '<strong>'.JText::_('Study Number: ').'</strong>'.$this->studydetails->studynumber;?>
		</span> <?php } ?> <?php } ?> <?php if ($this->params->get('show_duration') > 0) { ?>
		<span class="small"> <?php echo '<strong>'.JText::_('Duration: ').'</strong>'.$duration;?>
		</span> <?php } ?></td>
	</tr>

	<?php if ($this->params->get('show_description_view') > 0){ ?>
	<tr>
		<td valign="top"><?php if (!$this->studydetails->studyintro) {
		}
		else { ?> <span class="small"> <?php echo $this->studydetails->studyintro; ?>
		</span> <?php } ?></td>
	</tr>
</table>
		<?php } ?>
		<?php if ($this->show_media > 0):
		?>
<table>
	<td>
	
	
	<tr>
	<?php
	// Here's where get the media information from the mediafiles table, joining other tables to complete the information on images, servers, and paths


	$query_media1 = 'SELECT #__bsms_mediafiles.*,'
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
	. ' WHERE #__bsms_mediafiles.study_id = '.$this->studydetails->id;
	$database->setQuery( $query_media1 );
	$media1 = $database->loadObjectList('id');
	if (!$media1){} else { // This tests to make sure that there is a result to the media query or it will generate an arror
		foreach ($media1 as $media) {
			$params =& $mainframe->getPageParameters();
			$download_image = $params->get('download_image');
			if (!$download_image) { $download_image = 'components/com_biblestudy/images/download.png';}
			$link_type = $media->link_type;
			$useavr = 0;
			$useavr = $useavr + $this->params->get('useavr') + $media->internal_viewer;
			$media_size = $media->size;
			$useplayer = 0;
			if ($params->get('media_player') > 0) {
				//Look to see if it is an mp3
				$ismp3 = substr($media->filename,-3,3);
				if ($ismp3 == 'mp3'){$useplayer = 1;}else {$useplayer = 0;}
			} //End if media_player param test

			if (!$media_size){
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
				$direct_link = '<a href="'.$path1.'"title="'.$media->malttext.' '.$duration.' '
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
				$isrealfile = substr($studyfile, -4, 1);
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
						$isrealfile = substr($studyfile, -4, 1);
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
				$avr_link = $mediacode.'{avrpopup type="window" id="'.$media->id
				.'"}<img src="'.JURI::base().$media->impath.'" alt="'.$media->imname
				.' '.$duration.' '.$media_size.'" width="'.$width
				.'" height="'.$height.'" border="0" "title="'
				.$media->malttext.' '.$duration.' '.$media_size.'"/>{/avrpopup}';
			}
			$useavr = 0;
			$useavr = $useavr + $this->params->get('useavr') + $media->internal_viewer;
			$isfilesize = 0;
			if ($filesize > 0)
			{
				$isfilesize = 1;
				$media1_sizetext = '<span style="font-size:0.60em;">'.$media_size.'</span>';
			}
			else {$media1_sizetext = '';}
			$media1_link = $direct_link;

			if ($useavr > 0)
			{ $media1_link = $avr_link;

			}
			if ($useplayer == 1){
				$player_width = $params->get('player_width');
				if (!$player_width) { $player_width = '290'; }
				$media1_link =
					'<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer'.$media->id.'" height="24" width="290">
<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
<param name="FlashVars" value="playerID='.$media->id.'&amp;soundFile='.$path1.'">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object> ';}
				?>
		<!-- this is where the media column td begins -->
		<td width="<?php echo $this->params->get('media_width');?>"><?php echo $media1_link; ?>
		<?php if ($link_type > 0){$src = JURI::base().$download_image;
		if ($imagew) {$width = $imagew;} else {$width = 24;}
		if ($imageh) {$height = $imageh;} else {$height= 24;}
		//list($width,$height)=getimagesize($src);?> <?php
		if($downloadCompatibility == 0) {
			echo '<a href="index.php?option=com_biblestudy&id='.$media->id.'&view=studieslist&controller=studieslist&task=download">';
		}else{
			echo('<a href="http://joomlaoregon.com/router.php?file='.$media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">');
		}
		?>
		<img src="<?php echo JURI::base().$download_image;?>"
							alt="<?php echo JText::_('Download');?>"
							height="<?php echo $height;?>" width="<?php echo $width;?>"
							title="<?php echo JText::_('Download');?>" /></a>
		<?php } ?>
    <?php if ($this->params->get('show_filesize') > 0) 
		{ ?>
         <br>
         <?php echo $media1_sizetext; 
		}?>
<!-- This is where the media column ends -->
</td>      
           
         

		
<?php } //end of foreach 
} //end of the if test/else for the $media array?>			
</tr></td></table>
<?php //} Took this out - seems left over from studylist if/endif show media ?>
<!-- This is where the column that holds text and/or media ends, as well as the row and table for media and/or text -->

	
        	
 
<table><tr><td>      			
<?php endif; //end of if params show media ?>	
	<?php if ($this->params->get('show_text_view') > 0): ?>
		<tr>
		<td>
			<br />
			<?php echo '<span '.$params->get('detailspan').'>'.$this->studydetails->studytext.'</span>'; ?>
		</td>
		</tr>
	<?php endif; ?>
<?php if ($this->params->get('show_comments') > 0) {?>
<?php if ($this->studydetails->comments > 0) { ?>

    <tr><td><?php //Row and column to hold overall commment table?>
    <strong><a class="heading" href="javascript:ReverseDisplay('comments')">>><?php echo JText::_('Show/Hide Comments');?><<</a>
<div id="comments" style="display:none;"></strong>
<table width="<?php echo $this->params->get('comment_table');?>" border="0" bgcolor="#000000"><tr valign="top" align="center"><td bgcolor="#FFFFFF"><?php //Row for title of comments table?><h1><font color="#000000">Comments</font></h1>
<img src="<?php echo JURI::base().'components/com_biblestudy/images/square.gif'?>" height="3" width="100%" /><?php //Beginning of overall comment table?>
<table width="100%" bgcolor="#FFFFFF"><?php //Inside comment table?>
<?php if (count($this->comments)) {?>
<?php 
foreach ($this->comments as $comment){?>
<tr><td><?php
$comment_date_display = JHTML::_('date',  $comment->comment_date, JText::_('DATE_FORMAT_LC3') , '$offset' );
echo '<strong>'.$comment->full_name.'</strong> <i>'.$comment_date_display.'</i><br>';
echo 'Comment: '.$comment->comment_text.'<br><hr>';?>
</td></tr>
<?php } ?>
</td></tr>
<?php } // End of if(count($this->comments))?>


</table><?php //End of inside comment table?>
</td></tr></table><?php //End of overall comment table?> 
</div><?php //End of div for show/hide comments?>
</td></tr><?php //End of row and column for overall comment able?>

<?php } // End of if $studydetails->comments > 0?>
<?php if ($this->params->get('show_comments') > 0) {?>
<tr><td><?php //Row for submit form for comments?>

<?php $user =& JFactory::getUser();
$this->assignRef('thestudy',$this->studydetails->study_id);
$comment_access = $this->params->get('comment_access');
$comment_user = $user->usertype;
if (!$comment_user) { $comment_user = 0;}
//$comment_access = $this->params->get('comment_access');
//dump ($comment_access, 'Comment Access'); dump ($comment_user, 'Comment User');
if ($comment_access > $comment_user){echo '<strong><br />'.JText::_('You must be registered to post comments').'</strong>';}else{
if ($user->name){$full_name = $user->name; } else {$full_name = ''; } ?>
<?php if ($user->email) {$user_email = $user->email;} else {$user_email = '';}?>

<form action="index.php" method="post">
<table><tr><td><strong><?php echo JText::_('Post a Comment');?></strong></td></tr>
<tr><td ><?php echo JText::_('First & Last Name: ');?></td><td><input class="text_area" size="50" type="text" name="full_name" id="full_name" value="<?php echo $full_name;?>" /></td></tr>
<tr><td><?php echo JText::_('Email (Not displayed): ');?></td><td><input class="text_area" type="text" size="50" name="user_email" id="user_email" value="<?php echo $user->email;?>" /></td></tr>
<tr><td><?php echo JText::_('Comment: ');?></td><td><textarea class="text_area" cols="20" rows="4" style="width:400px" name="comment_text" id="comment_text"></textarea></td></tr>
<?php if ($this->params->get('use_captcha') == 1) { ?>
<tr><td><?php // Beginning of row for captcha
// Begin captcha . Thanks OSTWigits 
//Must be installed. Here we check that
if (JPluginHelper::importPlugin('system', 'captcha'))
	{ 								
		echo JText::_('Enter the text in the picture').'&nbsp;'?>
		<input name="word" type="text" id="word" value="" style="vertical-align:middle" size="10">&nbsp;
        <img src=<?php echo JURI::base().'index.php?option=com_biblestudy&view=studydetails&controller=studydetails&task=displayimg';?>>
		<br />
	<?php } else { echo JText::_('Captcha plugin not installed. Please inform site administrator'); } //end of check for OSTWigit plugin?>							
</td></td><?php //end of row for captcha?>
<?php
	} // end of if for use of captcha
?>
</table><?php //End of Form table?>
<input type="hidden" name="study_id" id="study_id" value="<?php echo $this->studydetails->id;?>" />
<input type="hidden" name="task" value="comment" />
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="published" id="published" value="<?php echo $this->params->get('comment_publish');?>"  />
<input type="hidden" name="view" value="studydetails" />
<input type="hidden" name="controller" value="studydetails" />
<input type="hidden" name="comment_date" id="comment_date" value="<?php echo date('Y-m-d H:i:s');?>"  />
<input type="hidden" name="study_detail_id" id="study_detail_id" value="<?php echo $this->studydetails->id;?>"  />
<input type="submit" class="button" id="button" value="Submit"  />
</form>
<?php } //End of if $comment_access < $comment_user?>
</td></tr><?php //End of row for submit form?>
<?php } //End of show_comments on for submit form?>

<?php } //End of params if show_comments?>
<?php //code added to provide Scripture reference at bottom ?>
<?php if ($this->params->get('show_passage_view') > 0) { ?>
<?php if ($scripture1) { ?>
	<tr>
		<td><br />
<?php
  
  $key = "IP";
$booknumber = $this->studydetails->booknumber;
$ch_b = $this->studydetails->chapter_begin;
$ch_e = $this->studydetails->chapter_end;
$v_b = $this->studydetails->verse_begin;
$v_e = $this->studydetails->verse_end;
$esv = 1;
$scripture3 = format_scripture($booknumber, $ch_b, $ch_e, $v_b, $v_e, $esv);
 
  $passage = urlencode($scripture3);
  $options = "include-passage-references=false";
  $url = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";
  $p = (get_extension_funcs("curl")); // This tests to see if the curl functions are there. It will return false if curl not installed
  if ($p) { // If curl is installed then we go on
  $ch = curl_init($url); // This will return false if curl is not enabled
  if ($ch) { //This will return false if curl is not enabled
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $response = curl_exec($ch);
  curl_close($ch);?>
  <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>><?php echo JText::_('Show/Hide Scipture Passage');?><<</a>
<div id="scripture" style="display:none;"></strong>
  <?php echo "".$scripture1." (ESV)";
  print $response;?>
  </div>
  <?php } // End of if ($ch)
  } // End if ($p)
?>
		</td>
	</tr>
	<?php } 
	} // end of if show_passage_view ?>
		<tr>
	<td align="center">
	<?php 
	
    $link_text = $this->params->get('link_text');
	if (!$link_text) {
		$link_text = JText::_('Return to Studies List');
		}
	if ($this->params->get('view_link') == 0){}else{
	if ($this->params->get('view_link') == 1){
	$item = JRequest::getVar('Itemid');
	$link = JRoute::_('index.php?option='.$option.'&view=studieslist');}
	if ($item){
    $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&Itemid='.$item);}?>
	<a href="<?php echo $link;?>">&lt; <?php echo $link_text; ?>
    
    </a>
    <?php } //End of if view_link not 0?>
    
	</td>
	</tr>
</table>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->studydetails->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="studydetails" />
</form>
