<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_TPC_TOPICS_DETAIL' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="topic">
                                        <?php echo JText::_( 'JBS_CMN_TOPIC' ); ?>
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="topic_text" id="topic_text" size="100" maxlength="250" value="<?php echo $this->topicsedit->topic_text;?>" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->topicsedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="topicsedit" />
</form>
