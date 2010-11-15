<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>


    <table class="admintable">
    <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?></td>
        <td > <?php echo $this->lists['published'];
		?>
          </td>
      </tr>
      <tr>
        <td width="100" align="right" class="key"> <label for="location_text"> <?php echo JText::_( 'JBS_LOC_LOCATION_NAME' ); ?>
          </label> </td>
        <td> <input class="text_area" type="text" name="location_text" id="location_text" size="100" maxlength="250" value="<?php echo $this->locationsedit->location_text;?>" />
        </td>
      </tr>

    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->locationsedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="locationsedit" />
</form>
