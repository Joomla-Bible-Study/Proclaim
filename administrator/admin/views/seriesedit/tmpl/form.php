<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="series">
					<?php echo JText::_( 'Series' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="series_text" id="series_text" size="100" maxlength="250" value="<?php echo $this->seriesedit->series_text;?>" />
			</td>
            <td>
				<input class="text_area" type="text" name="series_thumbnail" id="series_thumbnail" size="100" maxlength="250" value="<?php echo $this->seriesedit->series_thumbnail;?>" />
			</td>
		</tr>
        
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->seriesedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="seriesedit" />
</form>
