<?php
/**
 * Default Custom
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$mainframe = Factory::getApplication();
$input = new JInput;
$option = $input->get('option', '', 'cmd');
$document = Factory::getDocument();
$params = $this->params;
$url = $this->params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}
$JBSMSerieslist = new JBSMSerieslist;
$t = $this->template->id;
?>
<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
	<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
		<?php
		echo $JBSMSerieslist->getSeriesDetailsExp($this->items, $this->params, $this->template);
		?>
		<table class="table table-striped bslisttable"> <?php
			$studies = $JBSMSerieslist->getSeriesstudiesExp($this->items->id, $this->params, $this->template);
			echo $studies;
			?></table>
		<?php
		if ($this->params->get('series_list_return') > 0)
		{
			?>
			<table class="table table-striped">
				<tr class="seriesreturnlink">
					<td>
						<?php echo '<a href="' . JRoute::_('index.php?option=com_proclaim&view=seriesdisplays&t=' . $t)
							. '"><< ' . JText::_('JBS_SER_RETURN_SERIES_LIST') . '</a> | <a href="'
							. JRoute::_('index.php?option=com_proclaim&view=sermons&filter_series=' . $this->items->id . '&t=' . $t)
							. '">' . JText::_('JBS_CMN_SHOW_ALL') . ' ' . JText::_('JBS_SER_STUDIES_FROM_THIS_SERIES') . ' >>'
							. '</a>';
						?>
					</td>
				</tr>
			</table>
		<?php
		}
		?>
	</div>
	<!--end of bspagecontainer div-->
	<input name="option" value="com_proclaim" type="hidden">
	<input name="task" value="" type="hidden">
	<input name="boxchecked" value="0" type="hidden">
	<input name="controller" value="seriesdisplay" type="hidden">
</form>
