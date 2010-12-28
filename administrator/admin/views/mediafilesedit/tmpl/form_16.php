<?php defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<script language="javascript" type="text/javascript">

function sizebutton(remotefilesize)
{
    
    var objTB = document.getElementById("size");
    objTB.value = remotefilesize;
}
</script>

<script type="text/javascript">

function openConverter1()
		{
			var Wheight=125;
			var Wwidth=300;
			var winl = (screen.width - Wwidth) / 2;
			var wint = (screen.height - Wheight) / 2;

			var msg1=window.open('components/com_biblestudy/convert1.htm',"Window",'scrollbars=1,width='+Wwidth+',height='+Wheight+',top='+wint+',left='+winl	);
			if (!msg1.closed) {
				msg1.focus();
			}
		}

</script>
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
			if (form.study_id.value == "0")
			{
				alert( "<?php echo JText::_( 'JBS_MED_CHOOSE_STUDY_TO_LINK', true ); ?>" );
			}
			else if (form.media_image.value == "0")
			{
				alert( "<?php echo JText::_( 'JBS_MED_CHOOSE_IMAGE', true ); ?>" );
			}
			else
			{
				submitform( pressbutton );
			}
		}
        </script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
  <div class="width-70 fltlft">
	<fieldset class="panelform">
		<legend><?php echo JText::_( 'JBS_MED_MEDIA_FILES_DETAILS' ); ?></legend>
		      	<img id="loading" src="<?php echo JURI::base().'components/com_biblestudy/images/loading.gif'; ?>"/>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('published'); ?> 
				<?php echo $this->form->getInput('published');?>
			</li>
			<li>
				<?php echo $this->form->getLabel('createdate'); ?>
				<?php echo $this->form->getInput('createdate'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('study_id'); ?>
				<?php echo $this->form->getInput('study_id'); ?>
			</li>	
			<li>
				<?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?>
			</li>			
		</ul>
	</fieldset>
	<fieldset class="panelform">
		<legend><?php echo JText::_('JBS_MED_MEDIA_FILES_LINKER'); ?></legend>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('docMan_id'); ?>
				<?php echo $this->form->getInput('docMan_id'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('article_id'); ?>
				<?php echo $this->form->getInput('article_id'); ?>
			</li>
		</ul>
	</fieldset>	
	<fieldset class="panelform">
		<legend><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
		<ul class="adminformlist">
			<li>
				<?php echo $this->form->getLabel('player'); ?>
				<?php echo $this->form->getInput('player'); ?>
			</li>	
			<li>
				<?php echo $this->form->getLabel('popup'); ?>
				<?php echo $this->form->getInput('popup'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('mediacode'); ?>
				<?php echo $this->form->getInput('mediacode'); ?>
			</li>
		</ul>
	</fieldset>
	</div>	
	<div class="width-30 fltrt">
		<fieldset class="panelform">
			<legend><?php echo JText::_('JBS_MED_MEDIA_FILES_STATS'); ?></legend>
			<ul>
				<li>
					<?php echo $this->form->getLabel('plays'); ?>
					<?php echo $this->form->getInput('plays'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('downloads'); ?>
					<?php echo $this->form->getInput('downloads'); ?>
				</li>
			</ul>
		</fieldset>
	</div>
    
   
    <div class="width-30 fltrt">
                 
               <?php  echo JHtml::_('sliders.start', 'biblestudy-slider'); ?>
<?php foreach ($params as $name => $fieldset): ?>
                <?php echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name.'-params');?>
        <?php if (isset($fieldset->description) && trim($fieldset->description)): ?>
                <p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
        <?php endif;?>
                <fieldset class="panelform" >
                <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
                        <ul class="adminformlist">
        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
        <?php endforeach; ?>
                        </ul>
                </fieldset>
<?php endforeach; ?>
 
                <?php echo JHtml::_('sliders.end'); ?>
        </div>
	<div class="width-100 fltlft">
		<fieldset class="panelform">
			<legend>Details</legend>
<?php $editor =& JFactory::getEditor();?>



    <table class="admintable">
     <?php if ($this->dmenabled > 0)
	 { ?>
      <tr>
      	<td class="key">
                <?php echo JText::_('JBS_MED_USE_DOCMAN')?></td>
      	<td>
      	<?php
      	if(isset($this->docManItem)){
      		echo '<span id="activeDocMan">'.$this->docManItem.'</span>';
      		echo ' <a href="#" id="docmanChange">'.JText::_('JBS_CMN_CHANGE').'</a>';
      	}
      	?>
      	<div id="docMainCategoriesContainer" class="selectContainer" style="<?php echo $this->docManStyle; ?>">
      	<?php
                echo JText::_('JBS_MED_CATEGORY');
      		echo JHTML::_('select.genericlist', $this->docManCategories, 'docManCategory', null, 'id', 'title', null, 'docManCategories');
      	?>
      	</div>
      	<div id="docManItemsContainer" class="selectContainer">
                <?php echo JText::_('JBS_CMN_ITEM'); ?><select id="docManItems" name="docManItem"></select>
      	</div>
      	</td>
      </tr>
      <?php } //end of if $this->docManItem ?>
<?php if (JOOMLA_VERSION == '5')
{ ?>
    
     <tr>

      	<td class="key">
                <?php echo JText::_('JBS_MED_USE_ARTICLE');?></td>
      	<td>
      	<?php
      	if(isset($this->articleItem)){
      		echo '<span id="activeArticle">'.$this->articleItem.'</span>';
      		echo ' <a href="#" id="articleChange">'.JText::_('JBS_CMN_CHANGE').'</a>';
      	}
      	?>
      	<div id="articlesSectionsContainer" class="selectContainer" style="<?php echo $this->articleStyle; ?>">
      	<?php
                echo JText::_('JBS_MED_SECTION');
      //		echo JHTML::_('select.genericlist', $this->articlesSections, 'articlesSections', null, 'id', 'title', null, 'articlesSections');
      	?>
      	</div>
      	<div id="articlesCategoriesContainer" class="selectContainer">
      	<?php
      	echo JText::_('JBS_MED_CATEGORY');
      	?>
      	<select id="articleSectionCategories" name="articleSectionCategories"><option selected="selected"><?php echo '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -'; ?></option></select>
      	</div>
      	<div id="articlesItemsContainer" class="selectContainer">
      	<?php
        echo JText::_('JBS_CMN_ITEM');
      	?>
      	<select id="categoryItems" name="categoryItem"><option selected="selected"><?php echo '- '.JTEXT::_('JBS_MED_SELECT_ARTICLE').' -'; ?></option></select>
      	</div>
      	</td>
      </tr>
<?php } // end if Joomla 1.5 for articles
else
{ ?>
    
  <tr>

      	<td class="key">
                <?php echo JText::_('JBS_MED_USE_ARTICLE');?></td>
      	<td>
      	<?php
      	if(isset($this->articlesItem)){
      		echo '<span id="activeArticle">'.$this->articlesItem.'</span>';
      		echo ' <a href="#" id="articleChange">'.JText::_('JBS_CMN_CHANGE').'</a>';
      	}
      	?>
      	
      	<div id="articlesCategoriesContainer" class="selectContainer">
      	<?php
      	echo JText::_('JBS_MED_CATEGORY');
      	?>
      	<select id="articleSectionCategories" name="articleSectionCategories"><option selected="selected"><?php echo '- '.JTEXT::_('JBS_MED_SELECT_CATEGORY').' -'; ?></option></select>
      	</div>
      	<div id="articlesItemsContainer" class="selectContainer">
      	<?php
        echo JText::_('JBS_CMN_ITEM');
      	?>
      	<select id="categoryItems" name="categoryItem"><option selected="selected"><?php echo '- '.JTEXT::_('JBS_MED_SELECT_ARTICLE').' -'; ?></option></select>
      	</div>
      	</td>
      </tr>

<?php } //end if Joomla 1.6 for articles ?>
       <?php if ($this->vmenabled > 0)
	   { ?>
      <tr>
      	<td class="key">
                <?php echo JText::_('JBS_MED_USE_VIRTUEMART');?></td>
      	<td>
      	<?php
      	if(isset($this->virtueMartItem)){
      		echo '<span id="activeVirtueMart">'.$this->virtueMartItem.'</span>';
      		echo ' <a href="#" id="virtueMartChange">'.JText::_('JBS_CMN_CHANGE').'</a>';
      	}
      	?>
      	<div id="virtueMartCategoriesContainer" class="selectContainer" style="<?php echo $this->virtueMartStyle; ?>">
      	<?php
                echo JText::_('JBS_MED_CATEGORY');
      		echo JHTML::_('select.genericlist', $this->virtueMartCategories, 'virtueMartCategory', null, 'id', 'title', null, 'virtueMartCategories');
      	?>
      	</div>
      	<div id="virtueMartItemsContainer" class="selectContainer">
                <?php echo JText::_('JBS_CMN_ITEM'); ?><select id="virtueMartItems" name="virtueMartItem"></select>
      	</div>
      	</td>
      </tr>
      <?php } // end if $this->virtueMartItem ?>


            <tr><td class="key"><?php echo JText::_('JBS_CMN_AVR');?></td><td><?php echo JText::_('JBS_MED_AVRELOADED_TT');?></td></tr>
            <tr><td class="key"></td><td><input class="text_area" name="mediacode" id="mediacode" size="200" maxlength="500" onChange="AvReloadedInsert(this.mtag);" onKeyUp="AvReloadedInsert(this.mtag);" onKeyPress="AvReloadedInsert(this.mtag);" value="<?php echo $this->mediafilesedit->mediacode;?>" /><?php
			?></td></tr>
            <tr>
             <?php //<tr>?>
             <td class="key"> <?php echo JText::_('JBS_CMN_IMAGE');?>
              </td><td>  <?php echo $this->lists['image'];?></td>
            </tr>
            <tr>
            <td class="key">
            <?php echo JText::_( 'JBS_CMN_FILESIZE');?></td>
            <td>
			<?php $file_jbs_media = $this->filepath; $remotefilesize=getSizeFile("$file_jbs_media");
            if (!$remotefilesize){}
			elseif ($remotefilesize == 404){echo 'File not found. Check your link<br />';}
			elseif ($remotefilesize == -1){}
			else{
            	echo '<b>'.JText::_('JBS_MED_SERVER_LOOKUP').': </b>'.$remotefilesize.
            '<button type="button" onClick="sizebutton('.$remotefilesize.')"> '.JText::_('JBS_MED_ADD_SIZE').'</button><br />'; }?>
            <input class="text_area" type="text" name="size" id="size" size="20" maxlength="20" onChange="decOnly(this);" onKeyUp="decOnly(this);" onKeyPress="decOnly(this);" value="<?php echo $this->mediafilesedit->size;?>"/>
            <a href="javascript:openConverter1();">
            <?php echo '- '.JText::_('JBS_MED_FILESIZE_CONVERTER');?>
            </a>
            </td>
            </tr>

            <tr>
              <td class="key"><?php echo JText::_('JBS_CMN_SERVER');?></td><td> <?php echo $this->lists['server'];?></td>
            </tr>
            <tr>
              <td class="key" ><?php echo JText::_('JBS_MED_PATH_OR_FOLDER');?></td><td><?php echo $this->lists['path'];?></td>
            </tr>
           <tr>
              <td class="key" ><?php echo JText::_('JBS_MED_FILENAME');?></td><td><input class="text_area" type="text" name="filename" id="filename" size="100" maxlength="250" value="<?php echo $this->mediafilesedit->filename;?>"  /></td></tr>


      <tr><td class="key"><?php echo JText::_( 'JBS_MED_UPLOAD_FILE' ); ?></td><td><input type="file" id="file" name="file" size="75"/><?php echo JText::_('JBS_MED_TRY_USING_UPLOAD_BUTTON');?></td>
            </tr>
            <tr>
              <td class="key"></td><td><?php echo JText::_('JBS_MED_MAX_UPLOAD_PHP').': '.ini_get('upload_max_filesize');?></td>
            </tr>
			<tr>
              <td class="key"><?php echo JText::_('JBS_MED_TARGET');?></td><td><?php echo JText::_('JBS_MED_USE_FILENAME_AS_PATH');?>
              <?php echo JText::_('JBS_MED_TARGET_FOR_LINK')?> <input class="text_area" type="text" name="special" id="special" size="15" maxlength="15" value="<?php echo $this->mediafilesedit->special;?>" /></td>
            </tr>
     <tr><td class="key"><?php echo JText::_('JBS_CMN_PARAMETERS');?></td><td>
     <?php jimport('joomla.html.pane');
 ?>

<?php /*
$pane =& JPane::getInstance( 'sliders');
echo $pane->startPane ('content-pane');
echo $pane->startPanel(JText::_('JBS_MED_MEDIA_FILE_PARAMETERS'), 'MEDIAFILE_1');
echo $this->params->render ('params');
echo $pane->endPanel();
echo $pane->endPane();
*/ ?>

</td></tr>
<tr><td class="key"><?php echo JText::_('JBS_MED_CHOOSE_PODCAST');?></td><td><?php echo $this->lists['podcasts'];?></td></tr>
  <tr>

<tr><td class="key"><?php echo JText::_('JBS_MED_CHOOSE_MIME_TYPE');?></td><td> <?php echo $this->lists['mime_type'];?>
</td>
</tr>

<tr><td class="key"><?php echo JText::_('JBS_MED_SHOW_DOWNLOAD_ICON');?></td><td>

<select id="link_type" name="link_type"><?php echo $this->mediafilesedit->link_type;?>
<option value="0" <?php if ($this->mediafilesedit->link_type == 0){echo ' selected ';}?> > <?php echo JText::_('JBS_MED_NO_DOWNLOAD_ICON');?></option>
<option value="1" <?php if ($this->mediafilesedit->link_type == 1){echo ' selected ';}?> > <?php echo JText::_('JBS_MED_SHOW_DOWNLOAD_ICON');?></option>
<option value="2" <?php if ($this->mediafilesedit->link_type == 2){echo ' selected ';}?> > <?php echo JText::_('JBS_MED_SHOW_ONLY_DOWNLOAD_ICON');?></option>
</select>
</td></tr>
<tr><td class="key"><?php echo JText::_('JBS_CMN_COMMENT');?></td><td><input class="text_area" type="text" name="comment" id="comment" size="150" maxlength="150" value="<?php echo $this->mediafilesedit->comment;?>" /><?php echo '  '.JText::_('JBS_MED_APPEARS_UNDER_FILE_OR_TOOLTIP');?></td>
</tr>



    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->mediafilesedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="mediafilesedit" />

</form>

