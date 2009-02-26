<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	} else {
		submitform( pressbutton );
	}
}
</script>
<?php $user =& JFactory::getUser();
global $mainframe, $option;
$params =& $mainframe->getPageParameters();
$entry_user = $user->get('gid');
$podcast_access = ($params->get('podcast_access')) - 1;
$allow_podcast = $params->get('allow_podcast');
//echo 'allow_podcast = '.$allow_podcast.' access: '.$podcast_access;

if ($podcast_access >$entry_user){ echo JText::_('You are not authorized');}else{ ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend>Podcast Details</legend>

		
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
	<?php $link = JRoute::_( 'index.php?option=com_biblestudy&view=podcastedit&controller=podcastedit&task=writeXML&cid='. $this->podcastedit->id );?>
	<tr>
		<td class="key"><b>XML File:</b></td>
		<td><a href="<?php echo $link;?>"><img src="<?php echo JURI::base()?>administrator/images/backup.png" height="48" width="48" border="0"></a><br />
		<a href="<?php echo $link;?>"><b>Write XML File</b></a><br />Be sure to save changes first.</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="key"><b>Published:</b></td>
		<td><?php echo $this->lists['published'];?></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast Name:</b></td>
		<td><input class="text_area" type="text" name="title" id="title" size="70" maxlength="100" value="<?php echo $this->podcastedit->title;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Number of Records to include:</b><br />(blank for all)</td>
		<td><input class="text_area" type="text" name="podcastlimit" id="podcastlimit" size="5" maxlength="3" value="<?php echo $this->podcastedit->podcastlimit;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Website url:</b><br />(No http://)</td>
		<td><input class="text_area" type="text" name="website" id="website" size="70" maxlength="100" value="<?php echo $this->podcastedit->website;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Description of Podcast:</b><br />(500 Characters Max)</td>
		<td><textarea cols="53" class="text_area" name="description" id="description" ><?php echo $this->podcastedit->description;?></textarea></td>
	</tr>
	<tr>
		<td class="key"><b>Image url:</b><br />(No http://)</td>
		<td><input class="text_area" type="text" name="image" id="image" size="70" maxlength="130" value="<?php echo $this->podcastedit->image;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Image Height in pixels:</b></td>
		<td><input class="text_area" type="text" name="imageh" id="imageh" size="5" maxlength="3" value="<?php echo $this->podcastedit->imageh;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Image Width in pixels:</b></td>
		<td><input class="text_area" type="text" name="imagew" id="imagew" size="5" maxlength="3" value="<?php echo $this->podcastedit->imagew;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast Author:</b></td>
		<td><input class="text_area" type="text" name="author" id="author" size="70" maxlength="100" value="<?php echo $this->podcastedit->author;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast Logo or podcastimage url:</b><br />(No http://)</td>
		<td><input class="text_area" type="text" name="podcastimage" id="podcastimage" size="70" maxlength="130" value="<?php echo $this->podcastedit->podcastimage;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast Search Words:</b><br />(seperate with commas)</td>
		<td><input class="text_area" type="text" name="podcastsearch" id="podcastsearch" size="70" maxlength="100" value="<?php echo $this->podcastedit->podcastsearch;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast XML filename:</b><br />(path from root - No http://www.site.org - Just filename to put in root - recommended) like just biblestudies.xml</td>
		<td><input class="text_area" type="text" name="filename" id="filename" size="70" maxlength="150" value="<?php echo $this->podcastedit->filename;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Podcast language:</b><br />(like en-us)</td>
		<td><input class="text_area" type="text" name="language" id="language" size="5" maxlength="10" value="<?php echo $this->podcastedit->language;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Editor's Name:</b></td>
		<td><input class="text_area" type="text" name="editor_name" id="editor_name" size="70" maxlength="150" value="<?php echo $this->podcastedit->editor_name;?>" /></td>
	</tr>
	<tr>
		<td class="key"><b>Editor's Email Address:</b></td>
		<td><input class="text_area" type="text" name="editor_email" id="editor_email" size="70" maxlength="150" value="<?php echo $this->podcastedit->editor_email;?>" /></td>
	</tr>
	</table>
	</fieldset>
</div>
<?php if ($this->podcastedit->id) {
$params = &JComponentHelper::getParams($option);?>
	<div class="editcell">
	<fieldset class="adminlist">
		<legend>Episodes for this Podcast</legend>
	<table class="admintable" width=100%><tr></tr>
	
	<thead><tr><th>Edit Media File</th>
	<th>Media Create Date</th>
	<th>Scripture</th>
	<th>Edit Study</th>
	<th>Teacher</th>
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
