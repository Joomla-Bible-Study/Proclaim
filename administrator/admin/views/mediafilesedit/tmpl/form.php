<?php defined('_JEXEC') or die('Restricted access');

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


  <div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_MED_MEDIA_FILES_DETAILS' ); ?></legend>
		      	<img id="loading" src="<?php echo JURI::base().'components/com_biblestudy/images/loading.gif'; ?>"/>
<?php $editor =& JFactory::getEditor();

//AVR test
jimport('joomla.filesystem.file');
$dest = JPATH_SITE.DS.'/components/com_avreloaded/views/popup/view.html.php';
$avrexists = JFile::exists($dest);
if ($avrexists)
{
$avrread = JFile::read($dest);
$isbsms = substr_count($avrread,'JoomlaBibleStudy');
if ($isbsms){echo '<strong>'.JText::_('JBS_MED_AVR_IS_BS_READY').'</strong>';} else {echo '<strong>'.JText::_('JBS_MED_AVR_IS_NOT_BS_READY').'<br><a href="'.JURI::base().'index.php?option=com_biblestudy&controller=mediafilesedit&task=fixAVR">'.JText::_('JBS_MED_AVR_TRY_FIX').' <a href="http://www.JoomlaBibleStudy.org" target="_blank"> JoomlaBibleStudy.org </a> '.JText::_('JBS_MED_AVR_LOOK_FOR_FIX_DOWNLOAD').'</strong>';}
}
//End AVR test



?>



    <table class="admintable">
    <?php if ($this->mediafilesedit->id)
	{
    	?><tr><td class="key"><?php echo JText::_('JBS_CMN_DOWNLOADS'); ?></td><td><?php echo $this->mediafilesedit->downloads; ?></td><tr>
		<tr><td class="key"><?php echo JText::_('JBS_CMN_PLAYS'); ?></td><td><?php echo $this->mediafilesedit->plays; ?></td></tr><?php
    } ?>
      <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>

      </tr>
      <tr>
       <td class="key" align="left"><?php echo JText::_( 'JBS_CMN_CREATE_DATE_YMD_HMS' ); ?></td>
        <td>
        <?php if (!$this->mediafilesedit->id)
		{
			echo JHTML::_('calendar', date('Y-m-d H:i:s'), 'createdate', 'createdate');
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d H:i:s', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate');
        }

		//echo JHTML::_('calendar', date('D M j Y', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate'); ?>
        <br />
		<span style="font-family: serif; color: gray;">(<?php echo JText::_( 'JBS_CMN_YMD_HMS' ); ?>)</span>
        </td>
		</tr>
        <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_STUDY' );?></td>
        <td >

        <?php echo $this->lists['studies'];?></td></tr>
        <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_ORDERING' );?></td>
			<td >

				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
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
			if (JPluginHelper::importPlugin('system', 'avreloaded'))
					{echo $this->mbutton;}?></td></tr>
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
$pane =& JPane::getInstance( 'sliders');
echo $pane->startPane ('content-pane');
echo $pane->startPanel(JText::_('JBS_MED_MEDIA_FILE_PARAMETERS'), 'MEDIAFILE_1');
echo $this->params->render ('params');
echo $pane->endPanel();
echo $pane->endPane();
?>
</td></tr>
  <tr>
<td class="key"><?php echo JText::_('JBS_MED_CHOOSE_PODCAST');?></td><td>
					<?php

//jimport('joomla.html.pane');

echo $pane->startPane( 'content-pane' );
echo $pane->startPanel( JText::_( 'JBS_CMN_PODCASTS' ), 'PODCAST' );
echo $this->params->render( 'params','PODCAST' );
echo $pane->endPanel();
echo $pane->endPane();
					?>
				</td>
            </tr>

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

