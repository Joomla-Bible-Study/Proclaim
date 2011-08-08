<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die(); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CSS_CSSEDIT_INTRO' ); ?></legend>

		<table class="admintable">
        <tr>
			<td width="100" align="right" class="key">
				<label for="css">
					<?php echo JText::_( 'JBS_CSS_CSS' ); ?>:
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
<input type="hidden" name="task" value="save" />
<input type="hidden" name="controller" value="cssedit" />

<input type="hidden" name="view" value="cssedit" />
</form>