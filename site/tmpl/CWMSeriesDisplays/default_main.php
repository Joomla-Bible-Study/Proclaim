<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use CWM\Component\Proclaim\Site\Helper\CWMSerieslist;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
HtmlHelper::_('bootstrap.framework');
$document = Factory::getApplication();
$mainframe = Factory::getApplication();
$input = Factory::getApplication();
$option = $input->get('option', '', 'cmd');
$JViewLegacy = new HtmlView;
$CWMSerieslist = new CWMSerieslist;
$series_menu = $this->params->get('series_id', 1);

/** @type Joomla\Registry\Registry $params */
$params = $this->params;
$url = $params->get('stylesheet');
$listing = new CWMListing;
$classelement = $listing->createelement($this->params->get('series_element'));

if ($url)
{
    HtmlHelper::_('stylesheet', $url);
}
?>
<div class="container-fluid">
	<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
        <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
			<div <?php echo $classelement; ?> class="componentheading">
				<?php
				if ($this->params->get('show_page_image_series'))
				{
					echo '<img src="' . Uri::base() . $this->params->get('show_page_image_series') . '" alt="' . $this->params->get('show_series_title') . '" />';

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
		</div>
		<!--header-->

		<div id="bsdropdownmenu">

			<?php

			if ($this->params->get('series_list_show_pagination') == 1)
			{
				?> <div style="max-width: 150px"> <?php
                echo '<span class="display-limit">' . Text::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span></div>';
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
				echo '<span class="display-limit">' . Text::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			}
			echo $this->pagination->getPageslinks();
			?>
		</div>
		<!--end of bsfooter div-->

		<!--end of bspagecontainer div-->
		<input name="option" value="com_proclaim" type="hidden">
		<input name="task" value="" type="hidden">
		<input name="boxchecked" value="0" type="hidden">
		<input name="controller" value="seriesdisplays" type="hidden">
</form>
</div> <!-- end of container-fluid div -->
