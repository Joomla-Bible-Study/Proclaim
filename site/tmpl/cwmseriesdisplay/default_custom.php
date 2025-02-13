<?php

/**
 * Default Custom
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$mainframe = Factory::getApplication();
$input     = Factory::getApplication();
$option    = $input->get('option', '', 'cmd');
$document  = Factory::getApplication()->getDocument();
$params    = $this->params;
$url       = $this->params->get('stylesheet');

if ($url) {
    $document->addStyleSheet($url);
}
$JBSMSerieslist = new Cwmserieslist();
$t              = $this->template->id;
?>
<form action="<?php
echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
    <div id="proclaim" class="noRefTagger"> <!-- This div is the container for the whole page -->
        <?php
        echo $JBSMSerieslist->getSeriesDetailsExp($this->items, $this->params, $this->template);
        ?>
        <table class="table table-striped bslisttable"> <?php
            $studies = $JBSMSerieslist->getSeriesstudiesExp($this->items->id, $this->params, $this->template);
            echo $studies;
        ?></table>
        <?php
        if ($this->params->get('series_list_return') > 0) {
            ?>
            <table class="table table-striped">
                <tr class="seriesreturnlink">
                    <td>
                        <?php
                        echo '<a href="' . Route::_('index.php?option=com_proclaim&view=cwmseriesdisplays&t=' . $t)
                            . '"><< ' . Text::_('JBS_SER_RETURN_SERIES_LIST') . '</a> | <a href="'
                            . Route::_(
                                'index.php?option=com_proclaim&view=cwmsermons&filter_series=' . $this->items->id . '&t=' . $t
                            )
                            . '">' . Text::_('JBS_CMN_SHOW_ALL') . ' ' . Text::_(
                                'JBS_SER_STUDIES_FROM_THIS_SERIES'
                            ) . ' >>'
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
    <input name="controller" value="cwmseriesdisplay" type="hidden">
</form>
