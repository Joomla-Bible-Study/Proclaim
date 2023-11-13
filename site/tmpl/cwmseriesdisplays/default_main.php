<?php
/**
 * Default Main
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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

$document = Factory::getApplication();
$mainframe = Factory::getApplication();
$input = Factory::getApplication();
$option = $input->get('option', '', 'cmd');
$JViewLegacy = new HtmlView;
$CWMSerieslist = new Cwmserieslist;
$series_menu = $this->params->get('series_id', 1);

/** @type Joomla\Registry\Registry $params */
$params = $this->params;
$url = $params->get('stylesheet');
$listing = new Cwmlisting;
$classelement = $listing->createelement($this->params->get('series_element'));

if ($url)
{
    HtmlHelper::_('stylesheet', $url);
}
//echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
<div class="container-fluid">
	<form action="<?php Route::_('index.php?option=com_proclaim&view=cwmseriesdisplay') ?>" method="post" name="adminForm">
        <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
			<div <?php echo $classelement; ?> class="componentheading">
				<?php
				if ($this->params->get('show_page_image_series') && $this->params->get('series_show_image') > 0)
				{
					echo '<img src="' . Uri::base() . $this->params->get('show_page_image_series') . '" alt="' . $this->params->get('show_series_title') . '" />';

					// End of column for logo
				}
				?>
				<?php
				if ($this->params->get('show_series_title') > 0)
				{
					echo '<h1>'.$this->params->get('series_title').'</h1>';
				}
				?>

		</div>
		<!--header-->

		<div id="bsdropdownmenu">

			<?php

			if ($this->params->get('series_list_show_pagination') == 1)
			{
				?> <div style="max-width: 150px"> <?php
                echo '<span class="display-limit">' . Text::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span></div>';
			}

			?>
		</div>

		<?php
		try
		{
			$list = $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'seriesdisplays');
		}
		catch (Exception $e)
		{
		}
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
		<input name="controller" value="cwmseriesdisplays" type="hidden">
</form>
</div> <!-- end of container-fluid div -->
