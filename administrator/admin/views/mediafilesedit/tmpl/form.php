<?php defined('_JEXEC') or die('Restricted access'); 

?>

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
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

  
  <div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Media File Details' ); ?></legend>
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
if ($isbsms){echo '<strong>All Videos Reloaded is Bible Study Ready. Set parameters in Administration tab.</strong>';} else {echo '<strong>All Videos Reloaded NOT Bible Study ready. <br><a href="'.JURI::base().'index.php?option=com_biblestudy&controller=mediafilesedit&task=fixAVR">Click HERE to attempt a fix</a> or go to: <a href="http://www.JoomlaBibleStudy.org" target="_blank"> JoomlaBibleStudy.org </a> and look for All Videos Reloaded Fix file in the Downloads area, then install.</strong>';}
}
//End AVR test


jimport('joomla.html.pane');
$pane =& JPane::getInstance( 'sliders');
echo $pane->startPane ('content-pane');
echo $pane->startPanel(JText::_('Media File Parameters'), 'MEDIAFILE_1');
echo $this->params->render ('params');
echo $pane->endPanel();
echo $pane->endPane();
?>


	
    <table class="admintable">
    <?php if ($this->mediafilesedit->id)
	{
    	?><tr><td class="key"><?php echo JText::_('Downloads'); ?></td><td><?php echo $this->mediafilesedit->downloads; ?></td></tr><?php	
    } ?>
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
        <br />
		<span style="font-family: serif; color: gray;">(<?php echo JText::_( 'YYYY-MM-DD HH:MM:SS' ); ?>)</span>
        </td>
		</tr>
        <tr> 
        <td class="key"><?php echo JText::_( 'Study' );?></td>
        <td >
        
        <?php echo $this->lists['studies'];?></td></tr>
        <tr>
        <td class="key"><?php echo JText::_( 'Ordering' );?></td>
			<td >
				
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
     <?php if ($this->dmenabled > 0)
	 { ?>
      <tr>
      	<td class="key">
		<?php echo JText::_('Use DocMan')?>:</td>
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
      		<?php echo JText::_('Item').': '; ?><select id="docManItems" name="docManItem"></select>
      	</div>
      	</td>
      </tr>      
      <?php } //end of if $this->docManItem ?>
      
      <tr> 
      
      	<td class="key">
		<?php echo JText::_('Use Article')?>:</td>
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
      	echo JText::_('Category');
      	?>
      	<select id="articleSectionCategories" name="articleSectionCategories"><option selected="selected">- Select a category -</option></select>
      	</div>
      	<div id="articlesItemsContainer" class="selectContainer">
      	<?php
      	echo JText::_(' Item').': ';
      	?>
      	<select id="categoryItems" name="categoryItem"><option selected="selected">- Select an Article -</option></select>
      	</div>
      	</td>
      </tr>  
      
       <?php if ($this->vmenabled > 0)
	   { ?>
      <tr>
      	<td class="key">
		<?php echo JText::_('Use VirtueMart')?>:</td>
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
      
              
            <tr><td class="key"><?php echo JText::_('All Videos Reloaded');?></td><td><?php echo JText::_('AVRELOADED');?></td></tr>
            <tr><td class="key"></td><td><input class="text_area" name="mediacode" id="mediacode" size="200" maxlength="500" onChange="AvReloadedInsert(this.mtag);" onKeyUp="AvReloadedInsert(this.mtag);" onKeyPress="AvReloadedInsert(this.mtag);" value="<?php echo $this->mediafilesedit->mediacode;?>" /><?php 
			if (JPluginHelper::importPlugin('system', 'avreloaded'))
					{echo $this->mbutton;}?></td></tr>
            <tr>
             <?php //<tr>?> 
             <td class="key"> <?php echo JText::_('Image: ');?> 
              </td><td>  <?php echo $this->lists['image'];?></td>
            </tr>
            <tr>
            <td class="key">
            <?php echo JText::_( 'Filesize: ');?></td>
            <td>
            <input class="text_area" type="text" name="size" id="size" size="20" maxlength="20" onChange="decOnly(this);" onKeyUp="decOnly(this);" onKeyPress="decOnly(this);" value="<?php echo $this->mediafilesedit->size;?>"/>
            <a href="javascript:openConverter1();">
            <?php echo JText::_('- Filesize Converter');?>
            </a>
            </td>
            </tr>
             
            <tr>
              <td class="key"><?php echo JText::_('Server: ');?></td><td> <?php echo $this->lists['server'];?></td>
            </tr>
            <tr>
              <td class="key" ><?php echo JText::_('Path or Folder: ');?></td><td><?php echo $this->lists['path'];?></td>
            </tr>
           <tr>
              <td class="key" ><?php echo JText::_('Filename: ');?></td><td><input class="text_area" type="text" name="filename" id="filename" size="100" maxlength="250" value="<?php echo $this->mediafilesedit->filename;?>"  /></td></tr>
  

      <tr><td class="key"><?php echo JText::_( ' Or Upload File: ' ); ?></td><td><input type="file" id="file" name="file" size="75"/><?php echo JText::_(' Try also using the Upload button at the top. You will still have to enter the server/folder/filename information.');?></td>
            </tr>
            <tr>
              <td class="key"></td><td><?php echo JText::_('Maximum upload allowed in your php.ini file using post_max_size is: ').ini_get('upload_max_filesize');?></td>
            </tr>
			<tr>
			  <td class="key"><?php echo JText::_('Target');?></td><td><?php echo JText::_('Use file name as entire path if you wish. Just don\'t select a server or path.(Don\'t use this option if uploading)');?>
              <?php echo JText::_('- Target for link (ie: _self, _blank): ')?> <input class="text_area" type="text" name="special" id="special" size="15" maxlength="15" value="<?php echo $this->mediafilesedit->special;?>" /></td>
            </tr>
            <tr>
            	<td class="key"><?php echo JText::_('Choose a Podcast: ');?> </td><td>
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
           
                <tr><td class="key"><?php echo JText::_('Choose a Mime Type: ');?></td><td> <?php echo $this->lists['mime_type'];?>
				</td>
            </tr>
            
            <tr><td class="key"><?php echo JText::_('Show Download Icon');?></td><td>
			
			<select id="link_type" name="link_type"><?php echo $this->mediafilesedit->link_type;?>
				<option value="0" <?php if ($this->mediafilesedit->link_type == 0){echo ' selected ';}?> > <?php echo JText::_('No Download icon');?></option>
				<option value="1" <?php if ($this->mediafilesedit->link_type == 1){echo ' selected ';}?> > <?php echo JText::_('Show Download icon');?></option>
				<option value="2" <?php if ($this->mediafilesedit->link_type == 2){echo ' selected ';}?> > <?php echo JText::_('Show Only Download icon');?></option>
			</select>
			</td></tr>
			<tr><td class="key"><?php echo JText::_('Comment');?></td><td><input class="text_area" type="text" name="comment" id="comment" size="150" maxlength="150" value="<?php echo $this->mediafilesedit->comment;?>" /><?php echo '  '.JText::_('Appears under file or in Tooltip - set in Template parameters');?></td>
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

