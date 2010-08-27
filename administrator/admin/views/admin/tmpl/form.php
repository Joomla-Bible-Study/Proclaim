<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.debug.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_LIB .DS. 'biblestudy.version.php');
//dump ($this->admin, 'admin: ');


?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Administration' ); //dump ($this->admin, 'admin: ');?></legend>


    <table class="admintable">
    <tr><td class="key"><?php echo JText::_('Administrative Settings');?></td><td>
    <?php

	jimport('joomla.html.pane');
	$pane =& JPane::getInstance( 'sliders' );

echo $pane->startPane( 'content-pane' );

echo $pane->startPanel( JText::_( 'General' ), 'GENERAL' );
echo $this->params->render( 'params' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Images Folders' ), 'FILLIN-IMAGES' );
echo $this->params->render( 'params' , 'FILLIN-IMAGES');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Auto Fill Study Record' ), 'FILLIN-STUDY' );
echo $this->params->render( 'params' , 'FILLIN-STUDY');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Auto Fill Media File Record' ), 'FILLIN-MEDIAFILE' );
echo $this->params->render( 'params' , 'FILLIN-MEDIAFILE');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Front End Submission' ), 'SUBMISSION' );
echo $this->params->render( 'params' , 'SUBMISSION');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'All Videos Reloaded Compatability' ), 'ALLVIDEOSRELOADED' );
echo $this->params->render( 'params' , 'ALLVIDEOSRELOADED');
echo $pane->endPanel();

echo $pane->endPane();?>
<tr><td class="key"><?php echo JText::_('Default Study List Image');?></td><td><?php
if ($this->lists['main'])
{
    echo $this->lists['main']; echo ' '.JText::_('Default for Study List Page Image. Media images folder used (set above).');
}
else
{
    echo JText::_('There was a problem finding the list. Ensure that at minimum there is an images/stories folder. Also ensure images exist in that folder.');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('Default Study Image');?></td><td><?php
if (isset($this->lists['study']))
{
    echo $this->lists['study']; echo ' '.JText::_('Default for study thumbnail. Set Folder above.');
}
else
{
    echo JText::_('There was a problem finding the list. Ensure that at minimum there is an images/stories folder. Also ensure images exist in that folder.');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('Default Series Image');?></td><td><?php
if (isset($this->lists['series']))
{
    echo $this->lists['series']; echo ' '.JText::_('Default for series thumbnail. Set Folder above.');
}
else
{
    echo JText::_('There was a problem finding the list. Ensure that at minimum there is an images/stories folder. Also ensure images exist in that folder.');
}
?></td></tr>

<tr><td class="key"><?php echo JText::_('Default Teacher Image');?></td><td><?php
if (isset($this->lists['teacher']))
{
    echo $this->lists['teacher']; echo ' '.JText::_('Default for teacher thumbnail. Set Folder above.');
}
else
{
    echo JText::_('There was a problem finding the list. Ensure that at minimum there is an images/stories folder. Also ensure images exist in that folder.');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('Download Image');?></td><td><?php
if (isset($this->lists['download']))
{
    echo $this->lists['download']; echo ' '.JText::_('Default for download image. Must be called download.png. Media images folder used (set above).');
}
else
{
    echo JText::_('There was a problem finding the list. Ensure that at minimum there is an images/stories folder. Also ensure images exist in that folder.');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('Default Show/Hide Image for Landing Page');?></td><td>
<?php echo $this->lists['showhide']; echo ' '.JText::_('Default for Show/Hide Image on Landing Page. Media images folder used (set above).');?></td></tr>

<?php //test for sh404SEF
jimport('joomla.filesystem.file');
$dest = JPATH_SITE.DS.'/components/com_sh404sef/index.html';
$sh404exists = JFile::exists($dest);
if ($sh404exists)
{
	?>
	<tr><td class="key"><?php echo JText::_('sh404SEF maintenance'); ?></td><td><a href="index.php?option=com_biblestudy&view=admin&controller=admin&task=updatesef">Update sh404SEF links for com_biblestudy</a></td></tr>
	<?php
}
?>


    </table>
	</fieldset>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="1" />
<input type="hidden" name="controller" value="admin" />
<input type="hidden" name="task" value="save" />
</form>
<div class="clr"></div>

<form action="index.php" method="post" name="adminForm2" id="adminForm2">
    <div class="col100">
        <fieldset class="adminform">
		  <legend><?php echo JText::_( 'Media Files' ); ?></legend>
<table class="admintable">
<tr><td class="key"><?php echo JText::_('Media Players Statistics:');?> </td><td><?php echo $this->playerstats;?></td> </tr>
<tr>
<td class="key"><?php echo JText::_('Change Players'); ?></td>
<td>

<select name="from" id="from">
<option value="x"><?php echo JText::_('Select an Existing Player');?></option>
<option value="0"><?php echo JText::_('Direct');?></option>
<option value="1"><?php echo JText::_('Internal Player');?></option>
<option value="2"><?php echo JText::_('All Videos Reloaded');?></option>
<option value="3"><?php echo JText::_('All Videos Plugin');?></option>
<option value="100"><?php echo JText::_('No Player Listed');?></option>
</select>
</td>
<td>
<select name="to" id="to">
<option value="x"><?php echo JText::_('Select a New Player');?></option>
<option value="0"><?php echo JText::_('Direct');?></option>
<option value="1"><?php echo JText::_('Internal Player');?></option>
<option value="2"><?php echo JText::_('All Videos Reloaded');?></option>
<option value="3"><?php echo JText::_('All Videos Plugin');?></option>

</select>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="changePlayers" />
<input type="hidden" name="controller" value="admin" />
<input type="submit" value="Submit" />
</td>
</form>
</tr>
</table>
</fieldset>
<div class="clr"></div>

<form action="index.php" method="post" name="adminForm3" id="adminForm3">
    <div class="col100">
        <fieldset class="adminform">
		  <legend><?php echo JText::_( 'Pop Up Options' ); ?></legend>
<table class="admintable">
<tr><td class="key"><?php echo JText::_('Media Players Popup Statistics:');?> </td><td><?php echo $this->popups;?></td> </tr>
<tr>
<td class="key"><?php echo JText::_('Change Popup'); ?></td>
<td>

<select name="pfrom" id="pfrom">
<option value="x"><?php echo JText::_('Select an Existing Option');?></option>
<option value="0"><?php echo JText::_('Inline');?></option>
<option value="1"><?php echo JText::_('Popup/New Window');?></option>
<option value="3"><?php echo JText::_('Use Global Settings');?></option>
<option value="100"><?php echo JText::_('No Option Listed');?></option>
</select>
</td>
<td>
<select name="pto" id="pto">
<option value="x"><?php echo JText::_('Select a New Option');?></option>
<option value="0"><?php echo JText::_('Inline');?></option>
<option value="1"><?php echo JText::_('Popup/New Window');?></option>
<option value="3"><?php echo JText::_('Use Global Settings');?></option>


</select>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="changePopup" />
<input type="hidden" name="controller" value="admin" />
<input type="submit" value="Submit" />
</td>
</form>
</tr>
</table>
</fieldset>