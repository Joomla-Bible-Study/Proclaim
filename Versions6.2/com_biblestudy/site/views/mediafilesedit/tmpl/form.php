<?php defined('_JEXEC') or die('Restricted access');

?>
</script>
	<script language="javascript" type="text/javascript">
		<!--
        function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
    else {
		submitform( pressbutton );
	}

	}
    //-->
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
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" >

<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Media File Details' ); ?></legend>
		      	<img id="loading" src="<?php echo JURI::base().'components/com_biblestudy/images/loading.gif'; ?>"/>
<?php $editor =& JFactory::getEditor();

?>

    <table >
   <!-- <tr><td><input type="submit" value="submit" onclick="validate_form()"/> <input type="button" value="cancel"> </td></tr>-->
    <tr><td></td>
    <td align="left"><table width="200"><tr><td >	<button type="button" onclick="submitbutton('save')">
		<?php echo JText::_('Save');  ?>
	</button></td><td>
	<button type="button" onclick="submitbutton('cancel')">
		<?php echo JText::_('Cancel') ?>
	</button></td><td align="left"><?php echo $this->toolbar;?></td></tr></table></td></tr>
<tr><td class="key"><?php echo JText::_('Parameters');?></td><td width="75">
<?php
jimport('joomla.html.pane');
$pane =& JPane::getInstance ('sliders');

echo $pane->startPane ('content-pane');
echo $pane->startPanel(JText::_('Media File Parameters'), 'MEDIAFILE_1');
echo $this->params->render ('params');
echo $pane->endPanel();
echo $pane->endPane();
?>
</td></tr>
      <tr>
        <td class="key"><?php echo JText::_( 'Published' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr>
       <td class="key" align="left"><?php echo JText::_( 'Create Date YYYY-MM-DD H:M:S' ); ?></td>
        <td>
        <?php if (!$this->mediafilesedit->id)
		{
			echo JHTML::_('calendar', date('Y-m-d H:i:s'), 'createdate', 'createdate');
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d H:i:s', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate');
        }

		//echo JHTML::_('calendar', date('D M j Y', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate'); ?>
        </td>
		</tr>
        <tr>
        <td class="key"><?php echo JText::_( 'Study' );?>:</td>
        <td >

        <?php echo $this->studies;

		//echo $this->lists['studies'];?></td></tr>
        <tr>
        <td class="key"><?php echo JText::_( 'Ordering' );?>:</td>
			<td >

				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
     <?php if ($this->dmenabled->enabled)
	 { ?>
      <tr>
      	<td class="key">
        <?php echo JText::_('Use DOCman');?>:</td>
      	<td>
      	<?php
      	if(isset($this->docManItem)){
      		echo '<span id="activeDocMan">'.$this->docManItem.'</span>';
      		echo ' <a href="#" id="docmanChange">'.JText::_('Change').'</a>';
      	}
      	?>
      	<div id="docMainCategoriesContainer" class="selectContainer" style="<?php echo $this->docManStyle; ?>">
      	<?php
      		echo JText::_('Category').':';
      		echo JHTML::_('select.genericlist', $this->docManCategories, 'docManCategory', null, 'id', 'title', null, 'docManCategories');
      	?>
      	</div>
      	<div id="docManItemsContainer" class="selectContainer">
      		<?php echo JText::_('Item').':'; ?><select id="docManItems" name="docManItem"></select>
      	</div>
      	</td>
      </tr>
      <?php } //end of if $this->docManItem ?>

      <tr>

      	<td class="key">
        <?php echo JText::_('Use Article');?>:</td>
      	<td>
      	<?php
      	if(isset($this->articleItem)){
      		echo '<span id="activeArticle">'.$this->articleItem.'</span>';
      		echo ' <a href="#" id="articleChange">'.JText::_('Change').'</a>';
      	}
      	?>
      	<div id="articlesSectionsContainer" class="selectContainer" style="<?php echo $this->articleStyle; ?>">
      	<?php
      		echo JText::_('Section').':';
      		echo JHTML::_('select.genericlist', $this->articlesSections, 'articlesSections', null, 'id', 'title', null, 'articlesSections');
      	?>
      	</div>
      	<div id="articlesCategoriesContainer" class="selectContainer">
      	<?php
            echo JText::_('Category').':';
      	?>
      	<select id="articleSectionCategories" name="articleSectionCategories"><option selected="selected"><?php echo '- '.JTEXT::_('Select a category').' -'; ?></option></select>
      	</div>
      	<div id="articlesItemsContainer" class="selectContainer">
      	<?php
      	echo JText::_('Item').':';
      	?>
      	<select id="categoryItems" name="categoryItem"><option selected="selected"><?php echo '- '.JTEXT::_('Select an Article').' -'; ?></option></select>
      	</div>
      	</td>
      </tr>

       <?php if ($this->vmenabled->enabled)
	   { ?>
      <tr>
      	<td class="key">
        <?php echo JText::_('Use VirtueMart');?>:</td>
      	<td>
      	<?php
      	if(isset($this->virtueMartItem)){
      		echo '<span id="activeVirtueMart">'.$this->virtueMartItem.'</span>';
      		echo ' <a href="#" id="virtueMartChange">'.JText::_('Change').'</a>';
      	}
      	?>
      	<div id="virtueMartCategoriesContainer" class="selectContainer" style="<?php echo $this->virtueMartStyle; ?>">
      	<?php
      		echo JText::_('Category').':';
      		echo JHTML::_('select.genericlist', $this->virtueMartCategories, 'virtueMartCategory', null, 'id', 'title', null, 'virtueMartCategories');
      	?>
      	</div>
      	<div id="virtueMartItemsContainer" class="selectContainer">
      		<?php echo JText::_('Item').': '; ?><select id="virtueMartItems" name="virtueMartItem"></select>
      	</div>
      	</td>
      </tr>
      <?php } // end if $this->virtueMartItem ?>
     <?php if (isset($this->mediafilesedit->internal_viewer) )
	{ ?>
     <tr>
        <td class="key"><?php echo ('<a href="http://extensions.joomla.org/component/option,com_mtree/task,viewlink/link_id,3955/Itemid,35/" target="_blank">'.JText::_('AVReloaded Viewer').'</a>')?>
        </td>
        <td>
	<?php	echo JText::_('Use of AVR now set from parameters above. This media will continue to use AVR unless you change the parameter above'); ?>
		</td>
	</tr>
    <?php   } ?>
            <tr><td class="key"><?php echo JText::_('All Videos Reloaded');?></td><td><?php echo JText::_('AVRELOADED');?></td></tr>
            <tr><td class="key"></td><td><input class="text_area" name="mediacode" id="mediacode" size="100" maxlength="500" onChange="AvReloadedInsert(this.mtag);" onKeyUp="AvReloadedInsert(this.mtag);" onKeyPress="AvReloadedInsert(this.mtag);" value="<?php echo $this->mediafilesedit->mediacode;?>" /><?php
			if (JPluginHelper::importPlugin('system', 'avreloaded'))
					{echo $this->mbutton;}?></td></tr>
            <tr>
             <?php //<tr>?>
             <td class="key"> <?php echo JText::_('Image');?>:
              </td><td>  <?php echo $this->lists['image'];?></td>
            </tr>
            <tr>
            <td class="key">
            <?php echo JText::_('Filesize');?>:</td>
            <td>
            <input class="text_area" type="text" name="size" id="size" size="20" maxlength="20" onChange="decOnly(this);" onKeyUp="decOnly(this);" onKeyPress="decOnly(this);" value="<?php echo $this->mediafilesedit->size;?>"/>
            <a href="javascript:openConverter1();">
            <?php echo '- '.JText::_('Filesize Converter');?>
            </a>
            </td>
            </tr>

            <tr>
              <td class="key"><?php echo JText::_('Server');?>:</td><td> <?php echo $this->lists['server'];?></td>
            </tr>
            <tr>
              <td class="key" ><?php echo JText::_('Path or Folder');?>:</td><td><?php echo $this->lists['path'];?></td>
            </tr>
            <tr>
              <td class="key" ><?php echo JText::_('Filename');?>:</td><td><input class="text_area" type="text" name="filename" id="filename" size="100" maxlength="250" value="<?php echo $this->mediafilesedit->filename;?>"  /></td></tr>
              <tr><td class="key"><?php echo JText::_( 'Or Upload File' ); ?>:</td><td><input type="file" id="file" name="file" size="75"/><?php echo '<br>'.JText::_('Try also using the Upload button at the top. You will still have to enter the server/folder/filename information.');?></td>
            </tr>
            <tr>
              <td class="key"></td><td><?php echo JText::_('Maximum upload allowed in your php.ini file using post_max_size is').': '.ini_get('upload_max_filesize');?></td>
            </tr>
			<tr>
              <td class="key"><?php echo JText::_('Target');?>:</td><td><?php echo JText::_('Use file name as entire path if you wish. Just don\'t select a server or path.(Don\'t use this option if uploading)').' ';?>
              <?php echo JText::_('Target for link (ie: _self, _blank)');?> <input class="text_area" type="text" name="special" id="special" size="15" maxlength="15" value="<?php echo $this->mediafilesedit->special;?>" /></td>
            </tr>
             <tr>
                <td class="key"><?php echo JText::_('Choose a Podcast');?>:</td><td>
					<?php
$pane =& JPane::getInstance( 'sliders' );
//jimport('joomla.html.pane');
echo $pane->startPane( 'content-pane' );
echo $pane->startPanel( JText::_( 'Podcasts' ), 'PODCAST' );
echo $this->params->render( 'params','PODCAST' );
echo $pane->endPanel();
echo $pane->endPane();
					?>
				</td>
            </tr>
                <tr><td class="key"><?php echo JText::_('Choose a Mime Type');?>:</td><td> <?php echo $this->lists['mime_type'];?>
				</td>
            </tr>
            <tr><td class="key"><?php echo JText::_('Show Download Icon');?>:</td><td><?php echo $this->lists['link_type'];?></td></tr>
			<tr><td class="key"><?php echo JText::_('Comment');?>:</td><td><input class="text_area" type="text" name="comment" id="comment" size="75" maxlength="150" value="<?php echo $this->mediafilesedit->comment;?>" /><?php echo '  '.JText::_('Appears under file or in Tooltip - set in Template parameters');?></td>
            </tr>



    </table>
	</fieldset>
</div>
	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="id" value="<?php echo $this->mediafilesedit->id; ?>" />
	<input type="hidden" name="controller" value="mediafilesedit" />
	<input type="hidden" name="view" value="mediafilesedit" />
	<input type="hidden" name="task" value="save" />
    <input type="hidden" name="item" id="item" value="<?php echo $item;?>" />
</form>
