<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');

$mainframe = JFactory::getApplication();
$input = new JInput;
$option = $input->get('option', '', 'cmd');
$JViewLegacy = new JViewLegacy;
$JBSMSerieslist = new JBSMSerieslist;
JHtml::_('bootstrap.tooltip');
$series_menu = $this->params->get('series_id', 1);

/** @type Joomla\Registry\Registry $params */
$params = $this->params;
$url = $params->get('stylesheet');
$listing = new JBSMListing;
$classelement = $listing->createelement($this->params->get('series_element'));

if ($url)
{
	$document->addStyleSheet($url);
}
?>
<div class="container-fluid">
	<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
        <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
			<div <?php echo $classelement; ?> class="componentheading">
				<?php
				if ($this->params->get('show_page_image_series'))
				{
					echo '<img src="' . JUri::base() . $this->params->get('show_page_image_series') . '" alt="' . $this->params->get('show_series_title') . '" />';

					// End of column for logo
				}
				?>
				<?php
				if ($this->params->get('show_series_title') > 0)
				{
					echo $this->params->get('series_title');
				}
				?>
			</<?php echo $classelement; ?> </div>
		</div></div>
		<!--header-->

		<div id="bsdropdownmenu">

			<?php

			if ($this->params->get('series_list_show_pagination') == 1)
			{
				echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			}
			if ($this->params->get('search_series') == 1)
			{
				echo $this->page->series;
			}
			if ($this->params->get('series_list_teachers') == 1)
			{
				echo $this->page->teachers;
			}
			if ($this->params->get('series_list_years') == 1)
			{
				echo $this->page->years;
			}
			if ($this->go > 0)
			{
				echo $this->page->gobutton;
			}
			?>
		</div>

		<?php
		$list = $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'seriesdisplays');
		echo $list;
		?>

		<div class="listingfooter pagination">
			<?php
			if ($this->params->get('series_list_show_pagination') == 2)
			{
				echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			}
			echo $this->pagination->getPageslinks();
			?>
		</div>
		<!--end of bsfooter div-->

		<!--end of bspagecontainer div-->
		<input name="option" value="com_biblestudy" type="hidden">
		<input name="task" value="" type="hidden">
		<input name="boxchecked" value="0" type="hidden">
		<input name="controller" value="seriesdisplays" type="hidden">
</form>
</div> <!-- end of container-fluid div -->
