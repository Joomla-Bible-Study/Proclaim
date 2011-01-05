<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

$params = $this->form->getFieldsets();
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
<div class="width-40 fltlft">

<?php echo JHtml::_('sliders.start', 'biblestudy-slider'); ?>
    <?php foreach ($params as $name => $fieldset): ?>
            <?php echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name.'-params');?>
    <?php if (isset($fieldset->description) && trim($fieldset->description)): ?>
            <p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
    <?php endif;?>
            <fieldset class="panelform" >
                    <ul class="adminformlist">
        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                            <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
        <?php endforeach; ?>
                    </ul>
            </fieldset>
            
            
    <?php endforeach; ?>
 
 <?php echo JHtml::_('sliders.end'); ?>
 
 </div>
 <div class="width-60 fltrt">
<fieldset class="adminform">
<legend><?php echo JText::_( 'JBS_CMN_DEFAULT_IMAGES' ); ?></legend>
    
  <ul>


<li><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE');?><?php
if ($this->lists['main'])
{
    echo $this->lists['main']; echo ' '.JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></li>
<li><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE');?><?php
if (isset($this->lists['study']))
{
    echo $this->lists['study']; echo ' '.JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></li>
<li><?php echo JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE');?><?php
if (isset($this->lists['series']))
{
    echo $this->lists['series']; echo ' '.JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></li>

<li><?php echo JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE');?><?php
if (isset($this->lists['teacher']))
{
    echo $this->lists['teacher']; echo ' '.JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></li>
<li><?php echo JText::_('JBS_ADM_DOWNLOAD_IMAGE');?><?php
if (isset($this->lists['download']))
{
    echo $this->lists['download']; echo ' '.JText::_('JBS_ADM_DOWNLOAD_IMAGE_TT');
}
else
{
    echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
}
?></li>
<li><?php echo JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE');?>
<?php echo $this->lists['showhide']; echo ' '.JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE_TT');?></td></tr>
</li>
</ul>
	</fieldset>
    </div>
    <input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>



<div class="width-100 fltlft">

<form action="index.php" method="post" name="adminForm2" id="adminForm2">
    
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
</div>
<div class="width-100 fltlft">

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
</div>

</form>