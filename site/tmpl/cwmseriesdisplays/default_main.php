<?php

/**
 * Default Main
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

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Add template accent color for pagination
$accentColor = $this->params->get('seriesdisplay_color', $this->params->get('backcolor', '#287585'));
$wa = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");

// Use pre-created values from HtmlView
$input         = Factory::getApplication()->getInput();
$option        = $input->get('option', '', 'cmd');
$CWMSerieslist = $this->serieslist;
$series_menu   = $this->seriesMenu;

$params       = $this->params;
$url          = $params->get('stylesheet');
$listing      = $this->listing;
$classelement = $this->classelement;

if ($url) {
    HTMLHelper::_('stylesheet', $url);
}
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmseriesdisplays'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container proclaim-main-content" id="proclaim-main-content" role="main">
        <div class="row">
            <div class="col1-12">
                <div <?php echo $classelement; ?> class="componentheading">
                    <?php
                    if ($this->params->get('show_page_image_series') && $this->params->get('series_show_image') > 0) {
                        $seriesPageImage = Cwmimages::getImagePath($this->params->get('show_page_image_series'));
                        echo Cwmimages::renderPicture($seriesPageImage, $this->params->get('show_series_title', ''), '', false);
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
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
if ($this->items) {
    try {
        $list = $listing->getFluidListing(
            $this->items,
            $this->params,
            $this->template,
            $type = 'seriesdisplays'
        );
        echo $list;
    } catch (Exception $e) {
    }
} else {
    echo '<h4>' . Text::_('JBS_CMN_SERIES_NOT_FOUND') . '</h4>';
}
?>
                <div class="pagination-container pagelinks">
                    <?php
    if ((int) $this->params->get('series_list_show_pagination') === 2) {
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
