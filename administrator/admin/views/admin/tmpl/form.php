<?php defined('_JEXEC') or die('Restricted access');
//dump ($this->admin, 'admin: ');?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Administration' ); //dump ($this->admin, 'admin: ');?></legend>

		
    <table class="admintable">
    <tr> 
        <td class="key"><?php echo JText::_( 'Compatability Mode' ); ?></td>
        <td ><select name="compat_mode" id="compat_mode" class="inputbox" size="1">
        <option <?php if ($this->admin->compat_mode == 0) {echo 'selected ';}?>value="0"><?php echo JText::_('No');?></option>
        <option <?php if ($this->admin->compat_mode == 1) {echo 'selected ';}?>value="1"><?php echo JText::_('Yes');?></option>
        </select>
          </td>
     </tr>
     <tr><td class="key"></td><td><?php echo JText::_('If you experience problems with files downloading, try using this mode');?></td></tr>
      <tr> 
        <td width="100" align="right" class="key"> <label for="allow_deletes"> <?php echo JText::_( 'Allow Deletes' ); ?>: 
          </label> </td>
        <td><select name="allow_deletes" id="allow_deletes" class="inputbox" size="1">
        <option <?php if ($this->admin->allow_deletes == 0) {echo 'selected ';}?>value="0"><?php echo JText::_('No');?></option>
        <option <?php if ($this->admin->allow_deletes == 1) {echo 'selected ';}?>value="1"><?php echo JText::_('Yes');?></option>
        </select>
          </td>
      </tr>
      <tr><td class="key"></td><td><?php echo JText::_('Allow admin to delete rows for teachers/series/servers etc. Default setting allows editing of certain rows only to avoid broken links. Set this to Allow for full control. But do so with caution.');?></td></tr>
      <tr> 
        <td class="key"><?php echo JText::_( 'Drop Tables on Uninstall' ); ?></td>
        <td><select name="drop_tables" id="drop_tables" class="inputbox" size="1">
        <option <?php if ($this->admin->drop_tables == 0) {echo 'selected ';}?>value="0"><?php echo JText::_('No');?></option>
        <option <?php if ($this->admin->drop_tables == 1) {echo 'selected ';}?>value="1"><?php echo JText::_('Yes');?></option>
        </select>
          </td>
     </tr>
     </tr>
     <tr><td class="key"></td><td><?php echo JText::_('Drop com_biblestudy database tables on uninstall? Default does NOT drop tables.');?></td></tr>
      <tr> 
     <tr> 
        <td class="key"><?php echo JText::_( 'Show Store in Admin' ); ?></td>
        <td><select name="admin_store" id="admin_store" class="inputbox" size="1">
        <option <?php if ($this->admin->admin_store == 0) {echo 'selected ';}?>value="0"><?php echo JText::_('Show');?></option>
        <option <?php if ($this->admin->admin_store == 1) {echo 'selected ';}?>value="1"><?php echo JText::_('Hide');?></option>
        </select>
          </td>
     </tr>
     </tr>
     <tr><td class="key"></td><td><?php echo JText::_('Show or Hide store on studielist edit form');?></td></tr>
      <tr> 
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="1" />
<input type="hidden" name="controller" value="admin" />
<input type="hidden" name="task" value="save" />
</form>
