<?php

/**
 * Default Main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;

$document      = Factory::getApplication();
$input         = $document->getInput();
$option        = $input->get('option', '', 'cmd');
$CWMSerieslist = new Cwmserieslist();
$series_menu   = $this->params->get('series_id', 1);

/** @type Joomla\Registry\Registry $params */
$params       = $this->params;
$url          = $params->get('stylesheet');
$listing      = new Cwmlisting();
$classelement = $listing->createelement($this->params->get('series_element'));

if ($url) {
    HtmlHelper::_('stylesheet', $url);
}
?>
<form action="<?php Route::_('index.php?option=com_proclaim&view=cwmseriesdisplay') ?>" method="post" name="adminForm" id="adminForm">
    <div class="container">
        <div class="row">
            <div class="col1-12">
                <div <?php echo $classelement; ?> class="componentheading">
                    <?php
                    if ($this->params->get('show_page_image_series') && $this->params->get('series_show_image') > 0) {
                        echo '<img src="' . Uri::base() . $this->params->get(
                            'show_page_image_series'
                        ) . '" alt="' . $this->params->get('show_series_title') . '" />';
                        // End of column for logo
                    }
                    ?>
                    <?php
                    if ($this->params->get('show_series_title') > 0) {
                        echo '<h1>' . $this->params->get('series_title') . '</h1>';
                    }
                    ?>
    
                </div>
                <!--header-->

                <?php
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                if ($this->items) {
                    try {
                        $list = $listing->getFluidListing(
                            $this->items,
                            $this->params,
                            $this->template,
                            $type = 'seriesdisplays'
                        );
                    } catch (Exception $e) {
                    }
                    echo $list;
                } else {
                    echo "<h4>" . Text::_('JBS_CMN_SERIES_NOT_FOUND') . "</h4>";
                }
                ?>
                <div class="pagination">
                    <?php
                    if ($this->params->get('series_list_show_pagination') == 2) {
                        echo '<span class="display-limit">' . Text::_(
                            'JGLOBAL_DISPLAY_NUM'
                        ) . $this->pagination->getLimitBox() . '</span>';
                    }
                    echo $this->pagination->getPageslinks();
                    ?>
                </div>
                <!--end of bsfooter div-->

                <!--end of bspagecontainer div-->
            </div> <!-- end of container-fluid div -->
        </div>
    </div>
    <input name="option" value="com_proclaim" type="hidden">
    <input name="task" value="" type="hidden">
    <input name="boxchecked" value="0" type="hidden">
    <input name="controller" value="cwmseriesdisplays" type="hidden">
</form>
