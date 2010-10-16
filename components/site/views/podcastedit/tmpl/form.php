<?php defined('_JEXEC') or die('Restricted access'); ?>
	<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			if (pressbutton == 'cancel')
			{
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "")
			{
				alert( "<?php echo JText::_( 'Please fill in the Podcast Title.', true ); ?>" );
			}
			else if (form.website.value == "")
			{
				alert( "<?php echo JText::_( 'Please fill in the Website.', true ); ?>" );
			}
			else if (form.filename.value == "")
			{
				alert( "<?php echo JText::_( 'Please fill in the XML Filename.', true ); ?>" );
			}
			else
			{
				submitform( pressbutton );
			}
		}
        </script>
<?php $user =& JFactory::getUser();
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
$params =& $mainframe->getPageParameters();
$entry_user = $user->get('gid');
$podcast_access = ($params->get('podcast_access')) - 1;
$allow_podcast = $params->get('allow_podcast');
//echo 'allow_podcast = '.$allow_podcast.' access: '.$podcast_access;

if ($podcast_access >$entry_user){ echo JText::_('You are not authorized');} else {?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Podcast Details' ); ?></legend>

		
	<table cellpadding="5" class="admintable">
	<tr>
		<td colspan="2">
		<button type="button" onclick="submitbutton('save')">
		<?php echo JText::_('Save') ?>
		</button>
		<button type="button" onclick="submitbutton('cancel')">
		<?php echo JText::_('Cancel') ?>
		</button>
		</td>
	</tr>
	<?php if ($this->podcastedit->id) {?>
	<?php $link = JRoute::_( 'index.php?option=com_biblestudy&view=podcastedit&layout=form&controller=podcastedit&task=writeXMLFile&cid='. $this->podcastedit->id );?>
	<tr>
		<td class="key"><b><?php echo JText::_('XML File');?>:</b></td>
		<td><a href="<?php echo $link;?>"><img src="<?php echo JURI::base()?>administrator/images/backup.png" height="48" width="48" border="0"></a><br />
		<a href="<?php echo $link;?>"><b><?php echo JText::_('Write XML File')?></b></a><br /><?php echo JText::_('Be sure to save changes first.')?></td>
	</tr>
	<?php } ?>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Published' ); ?>:</b></td>
		<td><?php echo $this->lists['published'];?></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast Name' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="title" id="title" size="70" maxlength="100" value="<?php echo $this->podcastedit->title;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Number of Records to include (blank for all)' ); ?></b></td>
		<td><input class="text_area" type="text" name="podcastlimit" id="podcastlimit" size="5" maxlength="3" value="<?php echo $this->podcastedit->podcastlimit;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Website url (NO http://)' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="website" id="website" size="70" maxlength="100" value="<?php echo $this->podcastedit->website;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Description of Podcast (500 Max)' ); ?>:</b></td>
		<td><textarea cols="53" class="text_area" name="description" id="description" ><?php echo $this->podcastedit->description;?></textarea></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Image url (NO http://)' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="image" id="image" size="70" maxlength="130" value="<?php echo $this->podcastedit->image;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Image Height in pixels' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="imageh" id="imageh" size="5" maxlength="3" value="<?php echo $this->podcastedit->imageh;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Image Width in pixels' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="imagew" id="imagew" size="5" maxlength="3" value="<?php echo $this->podcastedit->imagew;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast Author' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="author" id="author" size="70" maxlength="100" value="<?php echo $this->podcastedit->author;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast Logo or podcastimage url (NO http://)' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="podcastimage" id="podcastimage" size="70" maxlength="130" value="<?php echo $this->podcastedit->podcastimage;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast Search Words (seperate with commas)' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="podcastsearch" id="podcastsearch" size="70" maxlength="100" value="<?php echo $this->podcastedit->podcastsearch;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast XML filename (path from root - NO http://www.site.org - Just filename to put in  root - recommended) like just biblestudies.xml' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="filename" id="filename" size="70" maxlength="150" value="<?php echo $this->podcastedit->filename;?>" /></td>
	</tr>
     <tr>
		<td class="key"><b><?php echo JText::_( 'Template for Details View Link' ); ?>:</b></td>
		<td><?php echo $this->lists['templates'];?></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Podcast language (like en-us)' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="language" id="language" size="5" maxlength="10" value="<?php echo $this->podcastedit->language;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Editor\'s Name' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="editor_name" id="editor_name" size="70" maxlength="150" value="<?php echo $this->podcastedit->editor_name;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b><?php echo JText::_( 'Editor\'s Email Address' ); ?>:</b></td>
		<td><input class="text_area" type="text" name="editor_email" id="editor_email" size="70" maxlength="150" value="<?php echo $this->podcastedit->editor_email;?>" /></td>
	</tr>
     <tr>
    	<td class="key"><b><?php echo JText::_('Episode Title');?>:</b></td>
        <td><select name="episodetitle" id="episodetitle">
        	
      		<option value="z"><?php echo '- '.JText::_('Select an item').' -';?></option>
        	<option <?php if ($this->podcastedit->episodetitle == 0) {echo 'selected ';}?>value="0"><?php echo JText::_('Scripture + Title');?></option>
			<option <?php if ($this->podcastedit->episodetitle == 1) {echo 'selected ';}?>value="1"><?php echo JText::_('Title Only');?></option>
			<option <?php if ($this->podcastedit->episodetitle == 2) {echo 'selected ';}?>value="2"><?php echo JText::_('Scripture Only');?></option>
			<option <?php if ($this->podcastedit->episodetitle == 3) {echo 'selected ';}?>value="3"><?php echo JText::_('Title + Scripture');?></option>
			<option <?php if ($this->podcastedit->episodetitle == 4) {echo 'selected ';}?>value="4"><?php echo JText::_('Date + Scripture + Title');?></option>
            <option <?php if ($this->podcastedit->episodetitle == 5) {echo 'selected ';}?>value="5"><?php echo JText::_('Custom');?></option>
    		</select>
            
        </td>
    </tr>
    <tr>
    	<td class="key"><b><?php echo JText::_('Custom');?></b></td>
        <td><input class="text_area" type="text" name="custom" id="custom" size="200" value="<?php echo $this->podcastedit->custom;?>" /></td>
    </tr>
	</table>
	</fieldset>
</div>
<?php if ($this->podcastedit->id) {
$params = &JComponentHelper::getParams($option);?>
	<div class="editcell">
	<fieldset class="adminlist">
		<legend><?php echo JText::_( 'Episodes for this Podcast' ); ?></legend>
	<table class="admintable" width=100%><tr></tr>
	
	<thead><tr><th><?php echo JText::_('Edit Media File');?></th>
	<th><?php echo JText::_('Media Create Date');?></th>
	<th><?php echo JText::_('Scripture');?></th>
	<th><?php echo JText::_('Edit Study');?></th>
	<th><?php echo JText::_('Teacher');?></th>
	</tr></thead>
	
	<?php
	
	//$episodes = $this->episodes;
	$k = 0;
	for ($i=0, $n=count( $this->episodes ); $i < $n; $i++)
	{
	$episode = &$this->episodes[$i];
	//$row = $episodes[$i];
	//foreach ($episodes as $episode) {
	$link2 = JRoute::_( 'index.php?option=com_biblestudy&controller=mediafilesedit&task=edit&cid[]='. $episode->mfid );
	$scripture = $episode->bookname.' '.$episode->chapter_begin;
	$study = JRoute::_('index.php?option=com_biblestudy&controller=studiesedit&task=edit&cid[]='. $episode->study_id);?>
	<tr class="<?php echo "row$k"; ?>">
		<td><a href="<?php echo $link2; ?>"><?php echo $episode->filename;?></a></td>
		<td><?php echo $episode->createdate;?></td>
		<td><?php echo $scripture;?></td>
		<td><a href="<?php echo $study;?>"><?php echo $episode->studytitle;?></a></td>
		<td><?php echo $episode->teachername;?></td>
	</tr>
	<?php
		$k = 1 - $k;
	}
	//} ?>
 </table>
	<?php } ?>
 </fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->podcastedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="podcastedit" />
</form>
<?php
} // End of authorization check ?>