<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$mainframe   = JFactory::getApplication();
$input       = new JInput;
$option      = $input->get('option', '', 'cmd');
$JViewLegacy = new JViewLegacy;
$JViewLegacy->loadHelper('serieslist');
$JBSMSerieslist = new JBSMSerieslist;
JHTML::_('behavior.tooltip');
$series_menu = $this->params->get('series_id', 1);

$params = $this->params;
$url    = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}
?>
<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
    <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
        <div id="bsmHeader">
            <h1 class="componentheading">
				<?php
				if ($this->params->get('show_page_image_series') > 0)
				{
					echo $this->page->main;

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
					echo $this->page->series;
				}
				?>
            </div>
            <!--dropdownmenu-->
            <table id="seriestable">
                <tbody>
				<?php
				// This sets the alternativing colors for the background of the table cells
				$class1  = 'bsodd';
				$class2  = 'bseven';
				$oddeven = $class1;

				foreach ($this->items as $row)
				{ // Run through each row of the data result from the model
					if ($oddeven == $class1)
					{ // Alternate the color background
						$oddeven = $class2;
					}
					else
					{
						$oddeven = $class1;
					}

					$listing = $JBSMSerieslist->getSerieslist($row, $params, $oddeven, $this->admin_params, $this->template, $view = 0);
					echo $listing;
				}
				?>
                </tbody>
            </table>
            <div class="listingfooter">
            </div>
            <!--end of bsfooter div-->
        </div>
        <!--end of bspagecontainer div-->
        <input name="option" value="com_biblestudy" type="hidden">
        <input name="task" value="" type="hidden">
        <input name="boxchecked" value="0" type="hidden">
        <input name="controller" value="seriesdisplays" type="hidden">
    </div>
</form>