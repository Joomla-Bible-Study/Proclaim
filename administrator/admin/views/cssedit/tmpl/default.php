<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'CSS - which controls the look and feel' ); ?></legend>

		<table class="admintable">
        <?php $link = JRoute::_( 'index.php?option=com_biblestudy&controller=cssedit&task=saveCSS');?>
	<tr>
		<td class="key"><b><?php echo JText::_('CSS File');?>:</b></td>
		<td><a href="<?php echo $link;?>"><img src="<?php echo JURI::base()?>images/backup.png" height="48" width="48" border="0"></a><br />
		<a href="<?php echo $link;?>"><b><?php echo JText::_('Write CSS File')?></b></a>
        </td>
	</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="css">
					<?php echo JText::_( 'CSS' ); ?>:
				</label>
			</td>
			<td>
				
				<textarea name="filecontent" id="filecontent" class="inputbox" rows="40" cols="92"><?php echo $this->lists->filecontent;?></textarea>
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="clr">
  
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="task" value="CSSsave" />
<input type="hidden" name="controller" value="cssedit" />
</form>
