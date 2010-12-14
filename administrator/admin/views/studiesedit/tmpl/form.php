<?php
	defined('_JEXEC') or die('Restricted access');
	jimport('joomla.pane.html');


	/*$js = 	"function changeDisplayImage() {
			if (document.adminForm.thumbnailm.value !='') {
				document.adminForm.imagelib.src='../images".DS.$this->admin_params->get('study_images', 'stories').DS."' + document.adminForm.thumbnailm.value;
			} else {
				document.adminForm.imagelib.src='images/blank.png';
			}";*/
$document =& JFactory::getDocument();
//$document->addScriptDeclaration($js);

?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js"></script>


<script type="text/javascript" src="components/com_biblestudy/js/plugins/jquery.tokeninput.js"></script>

<link rel="stylesheet" href="components/com_biblestudy/css/token-input-facebook.css" type="text/css" />

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
<fieldset class="adminform"><legend><?php echo JText::_( 'JBS_STY_DETAILS' ); ?></legend>
<?php $editor =& JFactory::getEditor();
$user =& JFactory::getUser();
?>
<table class="admintable">
<?php


if ($this->studiesedit->user_name == ''){$user_name = $user->name;}else{$user_name = $this->studiesedit->user_name;}?>
<!--<tr><td class="key"><?php echo JText::_('JBS_CMN_PARAMETERS');?></td>
<td>
<?php
/*$pane =& JPane::getInstance ('sliders');

echo $pane->startPane ('content-pane');
echo $pane->startPanel(JText::_('JBS_STY_STUDY_PARAMETERS'), 'STUDY_1');
echo $this->params->render ('params');
echo $pane->endPanel();
echo $pane->endPane(); */
?>
</td></tr>-->
	<tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_SUBMITTED_BY');?></td>
		<td><input class="text_area" type="text" name="user_name"
			id="user_name" size="25" maxlength="25"
			value="<?php echo $user_name;?>" />
        <?php echo '  <strong>'.JText::_('JBS_CMN_HITS').': </strong>'.$this->studiesedit->hits;?></td>

	</tr>
	<tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?></td>
		<td><?php echo $this->lists['published'];?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_STUDY_DATE' ); ?></td>
		<td><?php
		if (!$this->studiesedit->id)
		{
			echo JHTML::_('calendar', date('Y-m-d H:i:s'), 'studydate', 'studydate');
		} else {
			echo JHTML::_('calendar', date('Y-m-d H:i:s', strtotime($this->studiesedit->studydate)), 'studydate', 'studydate');
		} ?><br />
		<span style="font-family: serif; color: gray;">(<?php echo JText::_( 'JBS_CMN_YMD_HMS' ); ?>)</span>
		</td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_STUDYNUMBER' ); ?></td>
		<td><input class="text_area" type="text" name="studynumber"
			id="studynumber" size="25" maxlength="25"
			value="<?php echo $this->studiesedit->studynumber;?>" /></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_TITLE' ); ?></td>
		<td><input class="text_area" type="text" name="studytitle"
			id="studytitle" size="100" maxlength="250"
			value="<?php echo $this->studiesedit->studytitle;?>" /></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_DESCRIPTION' ); ?></td>
		<td><textarea name="studyintro" cols="100" class="text_area"
			id="studyintro"><?php echo $this->studiesedit->studyintro;?></textarea></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_SCIPTURE' ); ?></td>
		<td>
		<table width="60" border="0" cellspacing="1" cellpadding="1">
			<tr>
				<td width="20"><?php echo JText::_( 'JBS_CMN_BOOK' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_CHAPTER_BEGIN' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_VERSE_BEGIN' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_CHAPTER_END' ); ?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_VERSE_END' );?></td>
			</tr>
			<tr>
				<td><?php //echo $this->lists['booknumber'];
		$database =& JFactory::getDBO();
		$query2 = 'SELECT booknumber AS value, bookname AS text, published'
		. ' FROM #__bsms_books'
		. ' WHERE published = 1'
		. ' ORDER BY booknumber';
		$database->setQuery( $query2 );
		$bookid = $database->loadAssocList();
		echo '<select name="booknumber" id="booknumber" class="inputbox" size="1" ><option value="0"';
		echo '>- '.JText::_('JBS_CMN_SELECT_BOOK').' -'.'</option>';
		foreach ($bookid as $bookid2) {
			$format = $bookid2['text'];
			$output = JText::sprintf($format);
			$bookvalue = $bookid2['value'];
			if ($bookvalue == $this->studiesedit->booknumber){
				$selected = 'selected="selected"';
				echo '<option value="'.$bookvalue.'"'.$selected.' >'.$output.'</option>';
			} else {
				echo '<option value="'.$bookvalue.'">'.$output.'</option>';
			}
		};
		echo '</select>';?></td>
				<td><input class="text_area" type="text" name="chapter_begin"
					id="chapter_begin" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->chapter_begin;?>" /></td>
				<td><input class="text_area" type="text" name="verse_begin"
					id="verse_begin" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->verse_begin;?>" /></td>
				<td><input class="text_area" type="text" name="chapter_end"
					id="chapter_end" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->chapter_end;?>" /></td>
				<td><input class="text_area" type="text" name="verse_end"
					id="verse_end" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->verse_end;?>" /></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_SCRIPTURE2' ); ?></td>
		<td>
		<table width="60" border="0" cellspacing="1" cellpadding="1">
			<tr>
				<td width="20"><?php echo JText::_( 'JBS_STY_BOOK2' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_CHAPTER_BEGIN2' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_VERSE_BEGIN2' );?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_CHAPTER_END2' ); ?></td>
				<td width="8"><?php echo JText::_( 'JBS_STY_VERSE_END2' );?></td>
			</tr>
			<tr>
				<td><?php //echo $this->lists['booknumber2'];
		echo '<select name="booknumber2" id="booknumber2" class="inputbox" size="1" ><option value="0"';
		echo '>- '.JText::_('JBS_CMN_SELECT_BOOK').' -'.'</option>';
		foreach ($bookid as $bookid2) {
			$format = $bookid2['text'];
			$output = JText::sprintf($format);
			$bookvalue = $bookid2['value'];
			if ($bookvalue == $this->studiesedit->booknumber2){
				$selected = 'selected="selected"';
				echo '<option value="'.$bookvalue.'"'.$selected.' >'.$output.'</option>';
			} else {
				echo '<option value="'.$bookvalue.'">'.$output.'</option>';
			}
		};
		echo '</select>';?></td>
				<td><input class="text_area" type="text" name="chapter_begin2"
					id="chapter_begin2" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->chapter_begin2;?>" /></td>
				<td><input class="text_area" type="text" name="verse_begin2"
					id="verse_begin2" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->verse_begin2;?>" /></td>
				<td><input class="text_area" type="text" name="chapter_end2"
					id="chapter_end2" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->chapter_end2;?>" /></td>
				<td><input class="text_area" type="text" name="verse_end2"
					id="verse_end2" size="3" maxlength="3"
					value="<?php echo $this->studiesedit->verse_end2;?>" /></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_SECONDARY_REFERENCES' );?></td>
		<td><input class="text_area" type="text" name="secondary_reference"
			id="secondary_reference" size="150" maxlength="150"
			value="<?php echo $this->studiesedit->secondary_reference;?>" /></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_TEACHER' ); ?></td>
		<td><?php echo $this->lists['teacher_id']; ?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_LOCATION' ); ?></td>
		<td><?php echo $this->lists['location_id']; ?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_SERIES' ); ?></td>
		<td><?php echo $this->lists['series_id']; ?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_TOPIC' );?></td>
		<td><?php echo $this->lists['topics_id'];?></td>
	</tr>

  <tr>
    <td class="key" align="left">
      <?php echo JText::_( 'JBS_CMN_TOPIC' );?>
    </td>
    <td>
      <input type="text" id="topic_tags" name="topic_tags" /><!--</input>-->
    </td>
  </tr>

  <tr>
        <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_MESSAGE_TYPE' );?></td>
		<td><?php echo $this->lists['messagetype']; ?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_('JBS_CMN_DURATION');?></td>
		<td>
		<table width="60" border="0" cellspacing="1" cellpadding="1">
			<tr>
				<td width="7"><?php echo JText::_( 'JBS_CMN_HOURS');?></td>
				<td width="7"><?php echo JText::_( 'JBS_CMN_MINUTES');?></td>
				<td width="7"><?php echo JText::_( 'JBS_CMN_SECONDS');?></td>
			</tr>
			<tr>
				<td><input class="text_area" type="text" name="media_hours"
					id="media_hours" size="2" maxlength="2"
					value="<?php echo $this->studiesedit->media_hours;?>" /></td>
				<td><input class="text_area" type="text" name="media_minutes"
					id="media_minutes" size="2" maxlength="2"
					value="<?php echo $this->studiesedit->media_minutes;?>" /></td>
				<td><input class="text_area" type="text" name="media_seconds"
					id="media_seconds" size="2" maxlength="2"
					value="<?php echo $this->studiesedit->media_seconds;?>" /></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td class="key" align="left"><?php echo JText::_('JBS_STY_ALLOW_COMMENTS_FOR_THIS_STUDY');?></td>
		<td><?php echo $this->lists['comments'];?></td>
	</tr>
	<tr>
        <td class="key" align="left"><?php echo JText::_('JBS_STY_USER_LEVEL_TO_SHOW');?></td>
		<td><select name="show_level" id="show_level" class="inputbox"
			size="1">
			<?php
			$show = $this->studiesedit->show_level;
			$selected2 = '';
			?>
			<option value="0"
			<?php if ($show == '0') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_EVERYONE');?></option>
			<option value="18"
			<?php if ($show == '18') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_REGISTERED');?></option>
			<option value="19"
			<?php if ($show == '19') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_AUTHORS');?></option>
			<option value="20"
			<?php if ($show == '20') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_EDITORS');?></option>
			<option value="21"
			<?php if ($show == '21') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_PUBLISHERS');?></option>
			<option value="23"
			<?php if ($show == '23') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_MANAGERS');?></option>
			<option value="24"
			<?php if ($show == '24') {echo 'selected="selected"';}?>><?php echo JText::_('JBS_CMN_ADMIN_SUPERADMIN');?></option>
		</select></td>
	</tr>
    <tr>
    	<td class="key" alsign="left">
    		<label for="thumbnailm">
                       <?php echo JText::_('JBS_CMN_THUMBNAIL');?>
    		</label>
    	</td>
    	<td>
    		<?php echo $this->lists['thumbnailm'];//echo $this->studiesedit->thumbnailm;?>
    	</td>
    </tr>
    <tr><td valign="top" class="key">
                   <?php echo JText::_( 'JBS_STY_STUDY_IMAGE' ); ?>
        </td>
    <td> <?php  ?>
    <img <?php if(empty($this->studiesedit->thumbnailm)){echo ('style="display: none;"');}?> id="imgthumbnailm" src="<?php echo '../images/'.$this->admin_params->get('study_images', 'stories').'/'.$this->studiesedit->thumbnailm;?>">
    <?php
	?>
    </td>

    </tr>

	<?php if($this->admin_params->get('admin_store') == 0) {?>
	<tr>
		<td class="key" align="left"><?php echo JText::_('JBS_CMN_STORE');?></td>
		<td>
		<table>
			<tr>
				<td><?php echo JText::_('JBS_STY_IMAGE_CD');?>:<?php echo $this->lists['image_cd'];?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JBS_STY_STORE_URL_CD');?>:<?php echo $this->lists['server_cd'];?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JBS_STY_PRODUCT_ID_CD');?>:<input
					class="text_area" type="text" name="prod_cd" id="prod_cd" size="50"
					maxlength="50" value="<?php echo $this->studiesedit->prod_cd;?>" /></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JBS_STY_IMAGE_DVD');?>:<?php echo $this->lists['image_dvd'];?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JBS_STY_STORE_URL_DVD');?>:<?php echo $this->lists['server_dvd'];?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('JBS_STY_PRODUCT_ID_DVD');?>:<input
					class="text_area" type="text" name="prod_dvd" id="prod_dvd"
					size="50" maxlength="50"
					value="<?php echo $this->studiesedit->prod_dvd;?>" /></td>
			</tr>
		</table>
		</td>
	</tr>
	<?php }?>

	<tr>
		<td class="key"><?php echo JText::_( 'JBS_STY_STUDY_TEXT' );?></td>

		<td><?php echo $editor->display('studytext', $this->studiesedit->studytext, '100%', '400', '70', '15'); ?></td>
	</tr>
</table>
</fieldset>
</div>
<div class="editcell">
<fieldset class="adminlist"><legend><?php echo JText::_( 'JBS_STY_MEDIA_THIS_STUDY' ); ?></legend>
<table class="admintable" width=100%>
	<tr></tr>
	<thead>
		<tr>
			<th><?php echo JText::_('JBS_CMN_EDIT_MEDIA_FILE');?></th>
			<th><?php echo JText::_('JBS_CMN_MEDIA_CREATE_DATE');?></th>
			<th><?php echo JText::_('JBS_CMN_SCIPTURE');?></th>
			<th><?php echo JText::_('JBS_CMN_TEACHER');?></th>
		</tr>
	</thead>

	<?php

	//$episodes = $this->episodes;
	$k = 0;
	for ($i=0, $n=count( $this->mediafiles ); $i < $n; $i++)
	{
		$mediafiles = &$this->mediafiles[$i];
		//$row = $episodes[$i];
		//foreach ($episodes as $episode) {
		$link2 = JRoute::_( 'index.php?option=com_biblestudy&controller=mediafilesedit&task=edit&cid[]='. $mediafiles->mfid );
		$scripture = JText::sprintf($mediafiles->bookname).' '.$mediafiles->chapter_begin;?>
	<tr class="<?php echo "row$k"; ?>">
		<td><a href="<?php echo $link2; ?>"><?php echo $mediafiles->filename.' - '.$mediafiles->mfid;?></a></td>
		<td><?php echo $mediafiles->studydate;?></td>
		<td><?php echo $scripture;?></td>
		<td><?php echo $mediafiles->teachername;?></td>
	</tr>
	<?php
	$k = 1 - $k;
	}
	//} ?>
</table>
	<?php //} ?></fieldset>
</div>
<div class="clr"></div>
	<?php $user =& JFactory::getUser();
	$user_name = $this->studiesedit->user_name;
	if ($user_name == ''){$user_name = $user->name;}
	?> <input type="hidden" name="option" value="com_biblestudy" /> <input
	type="hidden" name="id" value="<?php echo $this->studiesedit->id; ?>" />
<input type="hidden" name="task" value="" /> 
<input type="hidden" name="controller" value="studiesedit" /> 
<input type="hidden" name="user_id" value="<?php echo $user->get('id');?>" />
<input type="hidden" name="hits" value="<?php echo $this->studiesedit->hits;?>" /></form>

<script>

  var vData;


  $(document).ready(function() {

  //Get Prepopulate Tags here...
  $.get("index.php?option=com_biblestudy&task=getTags&format=raw&q=<?php echo $this->studiesedit->id?>", function(data){
      vData = eval(data);
      run(vData);
    });

    function run(d) {
      $("#topic_tags").tokenInput("index.php?option=com_biblestudy&task=AjaxTags&format=raw", {
          prePopulate: d,
          classes: {
          tokenList: "token-input-list-facebook",
          token: "token-input-token-facebook",
          tokenDelete: "token-input-delete-token-facebook",
          selectedToken: "token-input-selected-token-facebook",
          highlightedToken: "token-input-highlighted-token-facebook",
          dropdown: "token-input-dropdown-facebook",
          dropdownItem: "token-input-dropdown-item-facebook",
          dropdownItem2: "token-input-dropdown-item2-facebook",
          selectedDropdownItem: "token-input-selected-dropdown-item-facebook",
          inputToken: "token-input-input-token-facebook"
          }
      });
    }

  });
</script>
