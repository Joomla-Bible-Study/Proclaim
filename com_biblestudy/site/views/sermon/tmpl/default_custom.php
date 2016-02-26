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
?>
<script type="text/javascript" language="JavaScript">
	function HideContent(d) {
		document.getElementById(d).style.display = "none";
	}
	function ShowContent(d) {
		document.getElementById(d).style.display = "block";
	}
	function ReverseDisplay(d) {
		if (document.getElementById(d).style.display == "none") {
			document.getElementById(d).style.display = "block";
		}
		else {
			document.getElementById(d).style.display = "none";
		}
	}
</script>
<?php
$mainframe = JFactory::getApplication();
$input = new JInput;
$option = $input->get('option', '', 'cmd');
JHTML::_('behavior.tooltip');
$params = $this->item->params;
$document = JFactory::getDocument();

$row = $this->studydetails;
?>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
	<?php
	// @todo need to be converted to bootstrap.
	$details = $JBSMListing->getStudyExp($row, $params, $this->template);
	echo $details;

	switch ($this->item->params->get('show_passage_view', '0'))
	{
		case 0:
			break;

		case 1:
			?>
			<strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>>
					<?php echo JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE'); ?><<</a>
			</strong>
			<div id="scripture" style="display:none;">
				<?php
				$response = $JBSMListing->getPassage($params, $row);
				echo $response;
				?>
			</div>
			<?php
			break;

		case 2:
			?>
			<div id="scripture">
				<?php
				$response = $JBSMListing->getPassage($params, $row);
				echo $response;
				?>
			</div>
			<?php
			break;
	}
	?>
</div><!--End of page container div-->
