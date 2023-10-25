<?php
/**
 * Default Custom
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Site\Helper\CWMSerieslist;
$mainframe = Factory::getApplication();
$input = Factory::getApplication();
$option = $input->get('option', '', 'cmd');
$series_menu = $this->params->get('series_id', 1);
$document = Factory::getApplication()->getDocument();
/** @var Joomla\Registry\Registry $params */
$params = $this->params;
$url = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}

$CWMSerieslist = new CWMSerieslist;
?>
<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
	<div id="proclaim" class="noRefTagger"> <!-- This div is the container for the whole page -->
		<div id="bsmHeader">
			<h1 class="componentheading">
				<?php
				if ($this->params->get('show_page_image_series') > 0)
				{
					?>
					<img src="<?php echo JUri::base() . $this->main->path; ?>"
					     alt="<?php echo $this->params->get('series_title') ?>"
					     width="<?php echo $this->main->width; ?>"
					     height="<?php echo $this->main->height; ?>"/>
					<?php
					// End of column for logo
				}
				?>
				<?php
				if ($this->params->get('show_series_title') > 0)
				{
					echo $this->params->get('series_title');
				}
				?>
			</h1>
			<!--header-->
			<div id="bsdropdownmenu">
				<?php
				if ($this->params->get('search_series') > 0)
				{
					echo $this->lists['seriesid'];
				}
				?>
			</div>
			<!--dropdownmenu-->
			<?php
			switch ($params->get('series_wrapcode'))
			{
				case '0':
					// Do Nothing
					break;
				case 'T':
					// Table
					echo '<table class="table table-striped" id="bsms_studytable" style="width: 100%;">';
					break;
				case 'D':
					// DIV
					echo '<div>';
					break;
			}
			echo $params->get('series_headercode');

			foreach ($this->items as $row)
			{ // Run through each row of the data result from the model
				$listing = $CWMSerieslist->getSerieslistExp($row, $params, $this->template);
				echo $listing;
			}

			switch ($params->get('series_wrapcode'))
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
		<!--end of bspagecontainer div-->
		<input name="option" value="com_proclaim" type="hidden">
		<input name="task" value="" type="hidden">
		<input name="boxchecked" value="0" type="hidden">
		<input name="controller" value="cwmseriesdisplays" type="hidden">
	</div>
</form>
