<?php

/**
 * Default Custom
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmseriesdisplays\HtmlView $this */

use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

$app         = Factory::getApplication();
$input       = new Input();
$option      = $input->get('option', '', 'cmd');
$series_menu = (int) $this->params->get('series_id', 1);
$document    = $app->getDocument();
$url         = $this->params->get('stylesheet');

if ($url) {
    $wa = $document->getWebAssetManager();
    $wa->registerAndUseStyle($url);
}

$CWMSerieslist = new Cwmserieslist();
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseriesdisplays'); ?>" method="post" name="adminForm">
    <div id="proclaim" class="noRefTagger"> <!-- This div is the container for the whole page -->
        <div id="bsmHeader">
            <h1 class="componentheading">
                <?php
                if ($this->params->get('show_page_image_series') > 0) {
                    echo $this->page->main;
                    // End of column for logo
                }
?>
                <?php
if ($this->params->get('show_series_title') > 0) {
    echo $this->params->get('series_title');
}
?>
            </h1>
            <!--header-->
            <div id="bsdropdownmenu">
                <?php
//                @todo Need to find the correct solution for this section, as it looks to be broken.
if ($this->params->get('search_series') > 0) {
    echo $this->lists['seriesid'];
}
?>
            </div>
            <!--dropdownmenu-->
            <?php
            switch ($this->params->get('series_wrapcode')) {
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
echo $this->params->get('series_headercode');

foreach ($this->items as $row) { // Run through each row of the data result from the model
    $listing = $CWMSerieslist->getSerieslistExp($row, $this->params, $this->template);
    echo $listing;
}

switch ($this->params->get('series_wrapcode')) {
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
            <!--end of footer div-->
        </div>
        <!--end of bspagecontainer div-->
        <input name="option" value="com_proclaim" type="hidden">
        <input name="task" value="" type="hidden">
        <input name="boxchecked" value="0" type="hidden">
        <input name="controller" value="cwmseriesdisplays" type="hidden">
    </div>
</form>
