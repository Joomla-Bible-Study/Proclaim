<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Bible Study Details' ); ?></legend>
<?php $editor =& JFactory::getEditor();
$user =& JFactory::getUser();		
?>
    <table class="admintable">
<?php    if ($this->studiesedit->user_name == ''){$user_name = $user->name;}else{$user_name = $this->studiesedit->user_name;}?>
    <tr><td class="key"><?php echo JText::_( 'Submitted by: ');?></td>
    <td><input classs="text_area" type="text" name="user_name" id="user_name" size="25" maxlength="25" value="<?php echo $user_name;?>" /></td></tr>
      <tr> 
        <td class="key"><?php echo JText::_( 'Published' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Study Date YYYY-MM-DD' ); ?></td>
        <td>
        <?php 
		if (!$this->studiesedit->id) 
		{
			echo JHTML::_('calendar', $this->studiesedit->studydate, 'studydate', 'studydate'); 
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d', strtotime($this->studiesedit->studydate)), 'studydate', 'studydate'); 
        } ?>
        </td>
		</tr>
		<tr>
        <td class="key" align="left"><?php echo JText::_( 'Study Number' ); ?></td>
		<td> <input class="text_area" type="text" name="studynumber" id="studynumber" size="25" maxlength="25" value="<?php echo $this->studiesedit->studynumber;?>" />
		</td>
      </tr>
	   <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Title' ); ?></td>
        <td> <input class="text_area" type="text" name="studytitle" id="studytitle" size="100" maxlength="250" value="<?php echo $this->studiesedit->studytitle;?>" /> 
        </td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Description' ); ?></td>
        <td> <textarea name="studyintro" cols="100" class="text_area" id="studyintro"><?php echo $this->studiesedit->studyintro;?></textarea> 
        </td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Scripture' ); ?></td>
        <td> 
        <div id="references">
	        <div id="reference" style="margin-top: 2px;">
	        <?php echo JText::_('Book'); ?>:
	        	<?php 
	        	echo JHTML::_('select.genericlist', $this->books, 'scripture', null , 'value', 'text', null);
	        	?>
	        <?php echo JText::_('Ch Begin'); ?>:
		        <input type="text" id="chapter_begin" size="3" maxlength="3" value="<?php echo $this->studiesedit->chapter_begin;?>" />
	        <?php echo JText::_('Vs Begin'); ?>:
	        	<input type="text" name="verse_begin" id="verse_begin" size="3" maxlength="3" value="<?php echo $this->studiesedit->verse_begin;?>" />
	        <?php echo JText::_('Ch End'); ?>:
	        	<input type="text" name="chapter_end" id="chapter_end" size="3" maxlength="3" value="<?php echo $this->studiesedit->chapter_end;?>" />
	        <?php echo JText::_('Vs End'); ?>:
	        	<input type="text" name="verse_end" id="verse_end" size="3" maxlength="3" value="<?php echo $this->studiesedit->verse_end;?>" />
	        </div>
        </div>
        <a href="#" id="addReference">Add Reference</a>
		</td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Teacher' ); ?></td>
        <td > <?php echo $this->lists['teacher_id']; ?> </td>
      </tr>
      <tr> 
       <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Location' ); ?></td>
        <td > <?php echo $this->lists['location_id']; ?> </td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Series' ); ?></td>
        <td ><?php echo $this->lists['series_id']; ?></td>
      </tr>
	  <tr>
	  	<td class="key" align="left"><?php echo JText::_( 'Topic' );?></td>
		<td><?php echo $this->lists['topics_id'];?></td>
	  </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Message Type' );?></td>
		<td ><?php echo $this->lists['messagetype']; ?></td>
      </tr>
      	<td class="key" align="left"><?php echo JText::_('Duration');?></td>
        <td><table width="60" border="0" cellspacing="1" cellpadding="1">
        <tr>
        	<td size="7"> <?php echo JText::_( 'Hours');?></td>
            <td size="7"><?php echo JText::_( 'Minutes');?></td>
            <td size="7"><?php echo JText::_( 'Seconds');?></td>
            
        </tr>
        <tr>
        	<td><input class="text_area" type="text" name="media_hours" id="media_hours" size="2" maxlength="2" value="<?php echo $this->studiesedit->media_hours;?>"/></td>
              <td ><input class="text_area" type="text" name="media_minutes" id="media_minutes" size="2" maxlength="2" value="<?php echo $this->studiesedit->media_minutes;?>"/></td>
              <td ><input class="text_area" type="text" name="media_seconds" id="media_seconds" size="2" maxlength="2" value="<?php echo $this->studiesedit->media_seconds;?>"/></td>
              
            </tr></table>
        <tr><td class="key" align="left"><?php echo JText::_('Allow comments for this study? -No- overrides global setting for this study only');?></td><td><?php echo $this->lists['comments'];?></td></tr>
        <tr><td class="key" align="left"><?php echo JText::_('User Level to show');?></td><td>
        <select name="show_level" id="show_level" class="inputbox" size="1">
       
        <?php 
		$show = $this->studiesedit->show_level;
		$selected2 = '';
		?>
        <option value="0" <?php if ($show == '0') {echo 'selected="selected"';}?>><?php echo JText::_('Everyone');?></option>
        <option value="18" <?php if ($show == '18') {echo 'selected="selected"';}?>><?php echo JText::_('Registered Users');?></option>
		<option value="19" <?php if ($show == '19') {echo 'selected="selected"';}?>><?php echo JText::_('Authors');?></option>
		<option value="20" <?php if ($show == '20') {echo 'selected="selected"';}?>><?php echo JText::_('Editors');?></option>
		<option value="21" <?php if ($show == '21') {echo 'selected="selected"';}?>><?php echo JText::_('Publishers');?></option>
		<option value="23" <?php if ($show == '23') {echo 'selected="selected"';}?>><?php echo JText::_('Managers');?></option>
		<option value="24" <?php if ($show == '24') {echo 'selected="selected"';}?>><?php echo JText::_('Administrators or Superadmin');?></option>
        </select>
        </td></td>
        <?php if($this->enableStore == 1) {?>
        <tr>
        <td class="key" align="left"><?php echo JText::_('Store');?></td>
        <td><table><tr><td><?php echo JText::_('Image for CD');?>:<?php echo $this->lists['image_cd'];?></td>
        </tr>
        <tr>
        	<td><?php echo JText::_('Store URL for CD');?>:<?php echo $this->lists['server_cd'];?></td>
        </tr>
        <tr><td><?php echo JText::_('Product ID for CD');?>:<input class="text_area" type="text" name="prod_cd" id="prod_cd" size="50" maxlength="50" value="<?php echo $this->studiesedit->prod_cd;?>"/></td>
        </tr>
        <tr><td><?php echo JText::_('Image for DVD');?>:<?php echo $this->lists['image_dvd'];?></td></tr>
        <tr>
        	<td><?php echo JText::_('Store URL for DVD');?>:<?php echo $this->lists['server_dvd'];?></td>
        </tr>
        <tr><td>
        <?php echo JText::_('Product ID for DVD');?>:<input class="text_area" type="text" name="prod_dvd" id="prod_dvd" size="50" maxlength="50" value="<?php echo $this->studiesedit->prod_dvd;?>"/></td></tr>
          </table></td>
            
                  </tr>
        <?php }?>
     </table></td>
      </tr>
      <tr> 
        <td class="key"><?php echo JText::_( 'Study Text' );?></td>
        <td>
        	<table> 
            	
                <tr><td>
					<?php echo $editor->display('studytext', $this->studiesedit->studytext, '100%', '400', '70', '15'); ?>
        		</td></tr>
            </table>
		</td>
      </tr>
    </table>
	</fieldset>
</div>
 <div class="editcell">
	<fieldset class="adminlist">
		<legend><?php echo JText::_( 'Media Files for this Study' ); ?></legend>
    <table class="admintable" width=100%><tr></tr>
    
    <thead><tr><th><?php echo JText::_('Edit Media File');?></th>
    <th><?php echo JText::_('Media Create Date');?></th>
    <th><?php echo JText::_('Scripture');?></th>
    <th><?php echo JText::_('Teacher');?></th>
    </tr></thead>
    
    <?php
	
	//$episodes = $this->episodes;
	$k = 0;
	for ($i=0, $n=count( $this->mediafiles ); $i < $n; $i++)
	{
	$mediafiles = &$this->mediafiles[$i];
	//$row = $episodes[$i];
    //foreach ($episodes as $episode) { 
	$link2 = JRoute::_( 'index.php?option=com_biblestudy&controller=mediafilesedit&task=edit&cid[]='. $mediafiles->mfid );
	$scripture = $mediafiles->bookname.' '.$mediafiles->chapter_begin;?>
	<tr class="<?php echo "row$k"; ?>">
    	<td><a href="<?php echo $link2; ?>"><?php echo $mediafiles->filename;?></a></td>
    	<td><?php echo $mediafiles->studydate;?></td>
        <td><?php echo $scripture;?></td>
        <td><?php echo $mediafiles->teachername;?></td>
    </tr>
    <?php  
		$k = 1 - $k;
	}
	//} ?>
 </table>
    <?php //} ?>
   </fieldset>
</div>
<div class="clr"></div>
<?php $user =& JFactory::getUser();
$user_name = $this->studiesedit->user_name;
if ($user_name == ''){$user_name = $user->name;}
?>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->studiesedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="studiesedit" />
<input type="hidden" name="user_id" value="<?php echo $user->get('id');?>"  />

</form>
