<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.debug.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_LIB .DS. 'biblestudy.version.php');

$parser =& JFactory::getXMLParser('Simple');
$parser->loadFile(BIBLESTUDY_PATH_ADMIN_INSTALL .DS. 'biblestudy.install.upgrade.xml');

$document =& $parser->document;
$comupgrade = $document->attributes('version'); //echo $comupgrade;
$install =& $document->install;
$version =& $document->install->getElementByPath('version');
//echo $version;
for ($i = 0, $c = count($install); $i < $c; $i ++)
{
    $inst =& $install[$i];
    $version = $inst->getElementByPath('version');
    $versiondate = $inst->getElementByPath('versiondate');
    $build = $inst->getElementByPath('build');
    $versionname = $inst->getElementByPath('versionname');
  //  echo $version->data().' '.$versiondate->data().' '.$build->data().' '.$versionname->data();
}


//print_r($install);
$upgrade = $document->upgrade;
for ($i = 0, $c = count($upgrade); $i < $c; $i ++)
{
    $up =& $upgrade[$i];
    $version = $up->getElementByPath('version');
    $versiondate = $up->getElementByPath('versiondate');
    $build = $up->getElementByPath('build');
    $versionname = $up->getElementByPath('versionname');
  //  echo $version->data().' '.$versiondate->data().' '.$build->data().' '.$versionname->data();
}
//foreach ($upgrade AS $up)
//{
 //   $attributes = $up->attributes();
    //print $attributes['version'];
    //print_r($attributes);
   // print '<br />';
//}
$attributes = $document->install[0]->attributes();
$attributes = $document->upgrade[0]->attributes();
//print $attributes['version'];
//print $document->children();

//foreach ($upgrade->children() AS $child)
//{
 //   print $child->data();
 //   print '<br />';
//}
//$attributes = $xml->comupgrade->install[1]->attributes();
//print $arrtributes['version'];
$version = $document->attributes();
//print_r($version);
//echo '<br />';
$installation =& $document->getElementByPath('install');
//print_r($installation);
foreach ($installation->children() AS $install)
{
   // echo $install->data();
   // echo '<br />';
}
$upgrade =& $document->getElementByPath('upgrade');
//print_r($upgrade);
foreach ($upgrade->children() AS $up)
{
   // echo $up->data();
   // echo '<br /><br />';
}

$db = JFactory::getDBO();
$query = 'SELECT id, params FROM #__bsms_mediafiles';
$db->setQuery($query);
$db->query();
$results = $db->loadObjectList();
foreach ($results AS $result)
{
    $params = new JParameter($result->params);
    $player = $params->get('player');
    $popup = $params->get('internal_popup');
    
  //  echo $player.' - '.$popup;
    $params->set('internal_popup', '3');
    
   // echo ' - new: '.$params->get('internal_popup').'<br />';
}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CMN_ADMINISTRATION' ); //dump ($this->admin, 'admin: ');?></legend>


    <table class="admintable">
    <tr><td class="key"><?php echo JText::_('JBS_ADM_ADMINISTRATIVE_SETTINGS');?></td><td>
    <?php

	jimport('joomla.html.pane');
	$pane =& JPane::getInstance( 'sliders' );

echo $pane->startPane( 'content-pane' );

echo $pane->startPanel( JText::_( 'JBS_CMN_GENERAL' ), 'GENERAL' );
echo $this->params->render( 'params' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'JBS_ADM_IMAGE_FOLDERS' ), 'FILLIN-IMAGES' );
echo $this->params->render( 'params' , 'FILLIN-IMAGES');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'JBS_ADM_AUTO_FILL_STUDY_REC' ), 'FILLIN-STUDY' );
echo $this->params->render( 'params' , 'FILLIN-STUDY');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'JBS_ADM_AUTO_FILL_MEDIA_REC' ), 'FILLIN-MEDIAFILE' );
echo $this->params->render( 'params' , 'FILLIN-MEDIAFILE');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'JBS_ADM_FRONTEND_SUBMISSION' ), 'SUBMISSION' );
echo $this->params->render( 'params' , 'SUBMISSION');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'JBS_ADM_AVR_COMPATIBILITY' ), 'ALLVIDEOSRELOADED' );
echo $this->params->render( 'params' , 'ALLVIDEOSRELOADED');
echo $pane->endPanel();

echo $pane->endPane();?>
<tr><td class="key"><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE');?></td><td><?php
if ($this->lists['main'])
{
    echo $this->lists['main']; echo ' '.JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE');?></td><td><?php
if (isset($this->lists['study']))
{
    echo $this->lists['study']; echo ' '.JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE');?></td><td><?php
if (isset($this->lists['series']))
{
    echo $this->lists['series']; echo ' '.JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></td></tr>

<tr><td class="key"><?php echo JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE');?></td><td><?php
if (isset($this->lists['teacher']))
{
    echo $this->lists['teacher']; echo ' '.JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('JBS_ADM_DOWNLOAD_IMAGE');?></td><td><?php
if (isset($this->lists['download']))
{
    echo $this->lists['download']; echo ' '.JText::_('JBS_ADM_DOWNLOAD_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></td></tr>
<tr><td class="key"><?php echo JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE');?></td><td>
<?php echo $this->lists['showhide']; echo ' '.JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE_TT');?></td></tr>

<?php //test for sh404SEF
jimport('joomla.filesystem.file');
$dest = JPATH_SITE.DS.'/components/com_sh404sef/index.html';
$sh404exists = JFile::exists($dest);
if ($sh404exists)
{
	?>
	<tr><td class="key"><?php echo JText::_('JBS_ADM_SH404SEF_MAINTENANCE'); ?></td><td><a href="index.php?option=com_biblestudy&view=admin&controller=admin&task=updatesef"><?php echo JText::_('JBS_ADM_SH404SEF_MAINTENANCE_LINK'); ?></a></td></tr>
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
		  <legend><?php echo JText::_( 'JBS_CMN_MEDIA_FILES' ); ?></legend>
<table class="admintable">
<tr><td class="key"><?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT');?> </td><td><?php echo $this->playerstats;?></td> </tr>
<tr>
<td class="key"><?php echo JText::_('JBS_ADM_CHANGE_PLAYERS'); ?></td>
<td>

<select name="from" id="from">
<option value="x"><?php echo JText::_('JBS_ADM_SELECT_EXISTING_PLAYER');?></option>
<option value="0"><?php echo JText::_('JBS_CMN_DIRECT_LINK');?></option>
<option value="1"><?php echo JText::_('JBS_CMN_INTERNAL_PLAYER');?></option>
<option value="2"><?php echo JText::_('JBS_CMN_AVR');?></option>
<option value="3"><?php echo JText::_('JBS_CMN_AVPLUGIN');?></option>
<option value="7"><?php echo JText::_('JBS_CMN_LEGACY_PLAYER');?></option>
<option value="100"><?php echo JText::_('JBS_NO_PLAYER_LISTED');?></option>
</select>
</td>
<td>
<select name="to" id="to">
<option value="x"><?php echo JText::_('JBS_ADM_SELECT_NEW_PLAYER');?></option>
<option value="0"><?php echo JText::_('JBS_CMN_DIRECT_LINK');?></option>
<option value="1"><?php echo JText::_('JBS_CMN_INTERNAL_PLAYER');?></option>
<option value="2"><?php echo JText::_('JBS_CMN_AVR');?></option>
<option value="3"><?php echo JText::_('JBS_CMN_AVPLUGIN');?></option>
<option value="7"><?php echo JText::_('JBS_CMN_LEGACY_PLAYER');?></option>

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
		  <legend><?php echo JText::_( 'JBS_ADM_POPUP_OPTIONS' ); ?></legend>
<table class="admintable">
<tr><td class="key"><?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT');?> </td><td><?php echo $this->popups;?></td> </tr>
<tr>
<td class="key"><?php echo JText::_('JBS_ADM_CHANGE_POPUP'); ?></td>
<td>

<select name="pfrom" id="pfrom">
<option value="x"><?php echo JText::_('JBS_ADM_SELECT_EXISTING_OPTION');?></option>
<option value="2"><?php echo JText::_('JBS_CMN_INLINE');?></option>
<option value="1"><?php echo JText::_('JBS_CMN_POPUP_NEW_WINDOW');?></option>
<option value="3"><?php echo JText::_('JBS_CMN_USE_GLOBAL');?></option>
<option value="100"><?php echo JText::_('JBS_ADM_NO_OPTION_LISTED');?></option>
</select>
</td>
<td>
<select name="pto" id="pto">
<option value="x"><?php echo JText::_('JBS_ADM_SELECT_NEW_OPTION');?></option>
<option value="2"><?php echo JText::_('JBS_CMN_INLINE');?></option>
<option value="1"><?php echo JText::_('JBS_CMN_POPUP_NEW_WINDOW');?></option>
<option value="3"><?php echo JText::_('JBS_CMN_USE_GLOBAL');?></option>


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