<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>

		<table class="admintable">
        <tr>
        <td width="100" class="key"><label for="published"><?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?></label></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr>
        <td width="100" class="key"><label for="list_show"><?php echo JText::_( 'JBS_TCH_SHOW_LIST_VIEW' ); ?></label></td>
        <td > <?php echo $this->lists['list_show'];
		?>
          </td>
      </tr>
      <tr>
        <td width="100" align="right" class="key">
				<label for="ordering">
                               <?php echo JText::_( 'JBS_CMN_ORDERING' ); ?>
				</label>
				</td>
                <td>
				<?php echo $this->lists['ordering']; ?>
			</td>

		<tr>
			<td width="100" align="right" class="key">
				<label for="teacher">
                              <?php echo JText::_( 'JBS_CMN_TEACHER' ); ?>
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="teachername" id="teachername" size="32" maxlength="250" value="<?php echo $this->teacheredit->teachername;?>" />
			</td>
        </tr>
        <tr>
        <td width="100" align="right" class="key">
				<label for="title">
                                <?php echo JText::_( 'JBS_CMN_TITLE' ); ?>
				</label>
			</td>
            <td>
            	<input class="text_area" type="text" name="title" id="title" size="50" maxlength="50" value="<?php echo $this->teacheredit->title;?>" />
            </td>
        </tr>
        <tr><td class="key"><?php echo JText::_('JBS_TCH_CHOOSE_LARGE_IMAGE');?></td><td><?php echo $this->lists['teacher_image']; echo '  '.JText::_('JBS_CMN_CURRENT_FOLDER').': '.$this->directory.' -  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('JBS_CMN_SET_DEFAULT_FOLDER').'</a>';?><br /><?php echo JText::_('JBS_CMN_THIS_FIELD_IS_USED_INSTEAD_BELOW');?></td>
      </tr>
       <tr><td valign="top" class="key">
                              <?php echo JText::_( 'JBS_TCH_TEACHER_IMAGE' ); ?>
           </td>
    <td> <?php  ?>
    <img <?php if(empty($this->teacheredit->teacher_image)){echo ('style="display: none;"');}?> id="imgteacher_image" src="<?php echo '../images/'.$this->admin_params->get('teachers_imagefolder', 'stories').'/'.$this->teacheredit->teacher_image;?>" name="teacherImage">
    <?php
	?>
    </td>

    </tr>
        <tr><td class="key"><?php echo JText::_('JBS_TCH_CHOOSE_THUMBNAIL');?></td><td><?php echo $this->lists['teacher_thumbnail']; echo '  '.JText::_('JBS_CMN_CURRENT_FOLDER').': '.$this->directory.' -  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('JBS_CMN_SET_DEFAULT_FOLDER').'</a>';?><br /><?php echo JText::_('JBS_CMN_THIS_FIELD_IS_USED_INSTEAD_BELOW');?></td>
      </tr>
       <tr><td valign="top" class="key">
                              <?php echo JText::_( 'JBS_TCH_TEACHER_THUMBNAIL' ); ?>
           </td>
    <td> <?php  ?>
    <img <?php if(empty($this->teacheredit->teacher_thumbnail)){echo ('style="display: none;"');}?> id="imgteacher_thumbnail" src="<?php echo '../images/'.$this->admin_params->get('teachers_imagefolder', 'stories').'/'.$this->teacheredit->teacher_thumbnail;?>" name="teacherThumb">
    <?php
	?>
    </td>

    </tr>
        <tr>
        <td width="100" align="right" class="key">
				<label for="image">
                               <?php echo JText::_( 'JBS_TCH_LARGE_IMAGE_URL' ); ?>
				</label>
			</td>
            <td>
            	<input class="text_area" type="text" name="image" id="image" size="150" maxlength="150" value="<?php echo $this->teacheredit->image;?>" />
            </td>
        </tr>
        <tr>
        <td width="100" align="right" class="key">
				<label for="imageh">
                               <?php echo JText::_( 'JBS_CMN_IMAGE_HEIGHT_PIXELS' ); ?>
				</label>
			</td>
            <td>
            	<input class="text_area" type="text" name="imageh" id="imageh" size="5" maxlength="5" value="<?php echo $this->teacheredit->imageh;?>" />
            </td>
        </tr>
        <tr>
        <td width="100" align="right" class="key">
				<label for="imagew">
                               <?php echo JText::_( 'JBS_CMN_IMAGE_WIDTH_PIXELS' ); ?>
				</label>
			</td>
            <td>
            	<input class="text_area" type="text" name="imagew" id="imagew" size="5" maxlength="5" value="<?php echo $this->teacheredit->imagew;?>" />
            </td>
        </tr>
        <tr>
        <td width="100" align="right" class="key">
				<label for="phone">
                              <?php echo JText::_( 'JBS_TCH_PHONE' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="phone" id="phone" size="50" maxlength="50" value="<?php echo $this->teacheredit->phone;?>" />
            </td>
        </tr>
         <tr>
         <td width="100" align="right" class="key">
				<label for="email">
                               <?php echo JText::_( 'JBS_TCH_EMAIL_CONTACT' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="email" id="email" size="100" maxlength="100" value="<?php echo $this->teacheredit->email;?>" />
            </td>
        </tr>
         <tr>
         <td width="100" align="right" class="key">
				<label for="website">
                              <?php echo JText::_( 'JBS_TCH_WEBSITE' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="website" id="website" size="100" maxlength="100" value="<?php echo $this->teacheredit->website;?>" />
            </td>
        </tr>
         <tr>
         <td width="100" align="right" class="key">
				<label for="short">
                              <?php echo JText::_( 'JBS_TCH_SHORT_DESCRIPTION_LIST_PAGE' ); ?>
				</label>
			</td>
        	<td><textarea class="text_area" name="short" cols="150" rows="4" id="short" ><?php echo $this->teacheredit->short;?></textarea>
            </td>
        </tr>
        <tr>
         <td width="100" align="right" class="key">
				<label for="thumb">
                               <?php echo JText::_( 'JBS_TCH_THUMBNAIL_URL' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="thumb" id="thumb" size="100" maxlength="100" value="<?php echo $this->teacheredit->thumb;?>" />
            </td>
        </tr>
        <tr>
         <td width="100" align="right" class="key">
				<label for="thumbh">
                               <?php echo JText::_( 'JBS_TCH_THUMBNAIL_HEIGHT' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="thumbh" id="thumbh" size="3" maxlength="3" value="<?php echo $this->teacheredit->thumbh;?>" />
            </td>
        </tr>
        <tr>
         <td width="100" align="right" class="key">
				<label for="thumbw">
                               <?php echo JText::_( 'JBS_TCH_THUMBNAIL_WIDTH' ); ?>
				</label>
			</td>
        	<td>
            	<input class="text_area" type="text" name="thumbw" id="thumbw" size="3" maxlength="3" value="<?php echo $this->teacheredit->thumbw;?>" />
            </td>
        </tr>
		 <tr>
         <td width="100" align="right" class="key">
				<label for="information">
                              <?php echo JText::_( 'JBS_TCH_OTHER_INFORMATION' ); ?>
				</label>
			</td>
        	<td>
            	<?php echo $this->editor->display('information', $this->teacheredit->information, '100%', '400', '70', '15'); ?>
            </td>
        </tr>
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->teacheredit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="teacheredit" />
<input type="hidden" name="catid" value="1" />
</form>
