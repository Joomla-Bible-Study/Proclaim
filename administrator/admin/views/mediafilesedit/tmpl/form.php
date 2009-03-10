<?php defined('_JEXEC') or die('Restricted access'); ?>

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
<?php $editor =& JFactory::getEditor();?>
	
    <table class="admintable">
      <tr> 
        <td class="key"><?php echo JText::_( 'Published' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr>
      	<td class="key">
      	<img id="loading" src="<?php echo JURI::base().'components/com_biblestudy/images/loading.gif'; ?>" style="display: none;" />
		<?php echo JText::_('Use DOCman')?>:</td>
      	<td>
      	<?php 
      	echo JText::_('Category').':';
      	echo JHTML::_('select.genericlist', $this->docManCategories, 'docManCategories', null, 'id', 'title', null, 'docManCategory');
      	?>
      	<?php 
      	echo JText::_(' Item').': ';
      	?>
      	<select id="docmanItems"><option selected="selected">- Select an Item -</option></select>
      	</td>
      </tr>
      <tr> 
        <td class="key" align="left"><?php echo JText::_( 'Create Date YYYY-MM-DD' ); ?></td>
        <td>
        <?php if (!$this->mediafilesedit->id) 
		{
			echo JHTML::_('calendar', $this->mediafilesedit->createdate, 'createdate', 'createdate'); 
		}
		else {
			echo JHTML::_('calendar', date('Y-m-d', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate'); 
        }
		
		//echo JHTML::_('calendar', date('D M j Y', strtotime($this->mediafilesedit->createdate)), 'createdate', 'createdate'); ?>
        </td>
		</tr>

      <tr> 
        <td class="key"><?php echo JText::_( 'Media File' );?></td>
        <td >
        
        <table width="100%" border="0" cellspacing="1" cellpadding="1">
        <tr><td><?php echo JText::_( 'Study: '); echo $this->lists['studies'];?></td></tr>
        <tr>
			<td >
				<label for="ordering">
					<?php echo JText::_( 'Ordering' ); ?>:
				</label>
			
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
        
              <tr><td><?php echo JText::_(' Use <a href="http://extensions.joomla.org/component/option,com_mtree/task,viewlink/link_id,3955/Itemid,35/" target="_blank">AVReloaded Viewer</a> (1.2.4 of higher Must be installed): ').$this->lists['internal_viewer']; ?></td>
			</tr>
            <tr><td><?php echo JText::_('AVRELOADED');?></td></tr>
            <tr><td><input class="text_area" name="mediacode" id="mediacode" size="200" maxlength="500" onChange="AvReloadedInsert(this.mtag);" onKeyUp="AvReloadedInsert(this.mtag);" onKeyPress="AvReloadedInsert(this.mtag);" value="<?php echo $this->mediafilesedit->mediacode;?>" /><?php 
			if (JPluginHelper::importPlugin('system', 'avreloaded'))
					{echo $this->mbutton;}?></td></tr>
            <tr>
             <?php //<tr>?> 
             <td> <?php echo JText::_('Image: ');?> 
                <?php echo $this->lists['image'];?></td>
            </tr><tr><td><?php echo JText::_( 'Filesize (in bytes): ');?><input class="text_area" type="text" name="size" id="size" size="20" maxlength="20" onChange="decOnly(this);" onKeyUp="decOnly(this);" onKeyPress="decOnly(this);" value="<?php echo $this->mediafilesedit->size;?>"/><a href="javascript:openConverter1();"> <?php echo JText::_('- Filesize Converter');?></a></td></tr>
             
            <tr>
              <td  ><?php echo JText::_('Server: ');?> <?php echo $this->lists['server'];?></td>
            </tr>
            <tr>
              <td  ><?php echo JText::_('Path or Folder: ');?><?php echo $this->lists['path'];?></td>
            </tr>
            <tr>
              <td  ><?php echo JText::_('Filename: ');?><input class="text_area" type="text" name="filename" id="filename" size="100" maxlength="250" value="<?php echo $this->mediafilesedit->filename;?>"  /></td></tr>
              <tr><td><?php echo JText::_( ' Or Upload File: ' ); ?><input type="file" id="file" name="file" size="75"/></td>
            </tr>
            <tr>
              <td><?php echo JText::_('Maximum upload allowed in your php.ini file using post_max_size is: ').ini_get('upload_max_filesize');?></td>
            </tr>
			<tr>
			  <td><?php echo JText::_('Use file name as entire path if you wish. Just don\'t select a server or path.(Don\'t use this option if uploading)');?>
              <?php echo JText::_('- Target for link (ie: _self, _blank): ')?> <input class="text_area" type="text" name="special" id="special" size="15" maxlength="15" value="<?php echo $this->mediafilesedit->special;?>" /></td>
            </tr>
            <tr>
            	<td><?php echo JText::_('Choose a Podcast: ');?> <?php echo $this->lists['podcast'];?></td>
                </tr>
                <tr><td><?php echo JText::_('Choose a Mime Type: ');?> <?php echo $this->lists['mime_type'];?>
				</td>
            </tr>
            <tr><td><?php echo JText::_('Show Download Icon');?><?php echo $this->lists['link_type'];?></td></tr>

          </table>
          </td>
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
