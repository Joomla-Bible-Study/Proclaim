<?php
/**
 * Default Custom
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$input = new JInput;
$option = $input->get('option', '', 'cmd');
$params = $this->params;

$t = $params->get('teachertemplateid');

if (!$t)
{
	$t = $input->get('t', 1, 'int');
}
$JBSMTeacher = new JBSMTeacher;
?>
<div id="biblestudy" class="noRefTagger">
	<table class="table table-striped" id="bsm_teachertable">
		<tbody>
		<tr class="titlerow">
			<td style="text-align: center;" colspan="3" class="title">
				<?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?>
			</td>
		</tr>
		</tbody>
	</table>
	<?php
	switch ($params->get('teacher_wrapcode'))
	{
		case '0':
			// Do Nothing
			break;
		case 'T':
			// Table
			echo '<table class="table table-striped" id="bsms_teachertable" style="width: 100%;">';
			break;
		case 'D':
			// DIV
			echo '<div>';
			break;
	}
	echo $params->get('teacher_headercode');


	foreach ($this->items as $row)
	{ // Run through each row of the data result from the model
		$listing = $JBSMTeacher->getTeacherListExp($row, $params, $oddeven = 0, $t);
		echo $listing;
	}

	switch ($params->get('teacher_wrapcode'))
	{
		case '0':
			// Do Nothing
			break;
		case 'T':
			// Table
			echo '</table>';
			break;
		case 'D':
			// DIV
			echo '</div>';
			break;
	}
	?>
	<div class="listingfooter">
		<?php
		echo $this->pagination->getPagesLinks();
		echo $this->pagination->getPagesCounter();
		?>
	</div>
	<!--end of bsfooter div-->
</div>
