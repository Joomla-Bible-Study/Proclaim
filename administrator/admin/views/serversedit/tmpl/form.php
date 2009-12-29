<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		
    <table class="admintable">
      <tr> 
        <td width="100" align="right" class="key"> <label for="servername"> <?php echo JText::_( 'Server Name' ); ?>: 
          </label> </td>
        <td> <input class="text_area" type="text" name="server_name" id="server_name" size="100" maxlength="250" value="<?php echo $this->serversedit->server_name;?>" /> 
        </td>
      </tr>
      <tr>
        <td align="right" class="key"><label for="serverpath"> <?php echo JText::_( 'Server Path (start with www - no trailing /) ' ); ?> 
          </label></td>
        <td><input class="text_area" type="text" name="server_path" id="server_path" size="100" maxlength="250" value="<?php echo $this->serversedit->server_path;?>" /> </td>
      </tr>
      <tr>
        <td align="right" class="key">
          <label for="serverpath">
            <?php echo JText::_( 'Server Type (ftp or local)' ); ?>
          </label>
        </td>
        <td>
          <select id="server_type" name="server_type" class="text_area">
            <option value="local" <?php if ($this->serversedit->server_type == "local") echo "selected"; ?>>Local</option>
            <option value="ftp" <?php if ($this->serversedit->server_type == "ftp") echo "selected"; ?>>FTP</option>
          </select>
        </td>
      </tr>
      <tr>
        <td align="right" class="key">
          <label for="serverpath">
            <?php echo JText::_( 'FTP Username' ); ?>
          </label>
        </td>
        <td>
          <input class="text_area" type="text" name="ftp_username" id="ftp_username" size="100" maxlength="250" value="<?php echo $this->serversedit->ftp_username; ?>" />
            </td>
      </tr>
      <tr>
        <td align="right" class="key">
          <label for="serverpath">
            <?php echo JText::_( 'FTP Password' ); ?>
          </label>
        </td>
        <td>
          <input class="text_area" type="password" name="ftp_password" id="ftp_password" size="100" maxlength="250" value="<?php echo $this->serversedit->ftp_password; ?>" />
            </td>
      </tr>
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->serversedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="serversedit" />
</form>
