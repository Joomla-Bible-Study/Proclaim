<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmcpanel\HtmlView $this */

// Load the tooltip behavior.
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown')
    ->useStyle('com_proclaim.general');

$msg   = '';
$input = Factory::getApplication()->getInput();
$msg   = $input->get('msg');

if ($msg) {
    echo $msg;
}

$simple = Cwmhelper::getSimpleView();

// Check for guided tours
$tourHelper = new CwmguidedtourHelper();
$hasTour    = false;

$tourId = $tourHelper->getTourId('com_proclaim_whats_new_10_1');
if ($tourId) {
    $hasTour = true;
    // Check if the asset exists before trying to use it
    if ($wa->getRegistry()->exists('script', 'com_guidedtours.tour')) {
        $wa->useScript('com_guidedtours.tour');
        $this->document->addScriptOptions('guidedtours', ['tourId' => $tourId]);
    }
}

// Check for startTour parameter
$startTour = $input->getInt('startTour', 0);
if ($startTour && $hasTour) {
    $tourId = $tourHelper->getTourId('com_proclaim_whats_new_10_1');
    if ($tourId) {
        // Force start the tour
        $wa->addInlineScript('
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(function() {
                    if (typeof Joomla !== "undefined" && Joomla.guidedTours) {
                        Joomla.guidedTours.startTour(' . $tourId . ');
                    } else {
                        // Fallback: try to click the button if it exists
                        const btn = document.querySelector(".button-start-guidedtour[data-gt-uid=\'com_proclaim_whats_new_10_1\']");
                        if (btn) {
                            btn.click();
                        }
                    }
                }, 500);
            });
        ');
    }
}
?>
<!-- Header -->
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cpanel'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div class="row">
        <?php
        if ($this->hasPostInstallationMessages) : ?>
            <div class="alert alert-info">
                <h3>
                    <?php
                    echo Text::_('JBS_CPL_PIM_TITLE'); ?>
                </h3>

                <p>
                    <?php
                    echo Text::_('JBS_CPL_PIM_DESC'); ?>
                </p>
                <a href="<?php
                echo Route::_('index.php?option=com_postinstall&eid=' . $this->extension_id); ?>"
                   class="btn btn-primary btn-large">
                    <?php
                    echo Text::_('JBS_CPL_PIM_BUTTON'); ?>
                </a>
            </div>
            <?php elseif ($this->hasPostInstallationMessages === null) : ?>
            <div class="alert alert-error">
                <h3>
                    <?php
                    echo Text::_('JBS_CPL_PIM_ERROR_TITLE'); ?>
                </h3>

                <p>
                    <?php
                    echo Text::_('JBS_CPL_PIM_ERROR_DESC'); ?>
                </p>
                <a href="https://www.christianwebministries.org/jbs-documentation.html"
                   class="btn btn-primary btn-large">
                    <?php
                    echo Text::_('JBS_CPL_PIM_ERROR_BUTTON'); ?>
                </a>
            </div>
            <?php
            endif; ?>
        <?php
            if ($simple->mode === 1 && $simple->display === 1) {
                ?>
            <div class="alert alert-info">
                <h3>
                    <?php
                        echo Text::_('JBS_CPANEL_SIMPLE_MODE_ON'); ?>
                </h3>

                <p>
                    <?php
                        echo Text::_('JBS_CPANEL_SIMPLE_MODE_DESC'); ?>
                </p>
                <a href="<?php
                    echo Route::_('index.php?option=com_proclaim&view=cwmadmin'); ?>"
                   class="btn btn-primary btn-large" style="color: #FFFFFF">
                    <?php
                        echo Text::_('JBS_CPANEL_SIMPLE_MODE_LINK'); ?>
                </a>
            </div>
            <?php
            }

            // Podcast task warning — show when task is disabled, trashed, or not created
            if (Cwmstats::getPodcastTaskRawState() !== 1) :
?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <span class="icon-warning-circle" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_CMN_PODCAST_TASK_WARNING'); ?>
                    <a href="<?php echo Route::_('index.php?option=com_scheduler&view=tasks'); ?>" class="alert-link" target="_blank">
                        <?php echo Text::_('JBS_CMN_PODCAST_TASK_STATUS'); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        <div class="col-lg-2 rounded">
            <div class="cpanel-logo">
                <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cpanel'); ?>">
                    <img src="../media/com_proclaim/images/proclaim.jpg"
                         alt="<?php echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>" />
                </a>
            </div>
            <div class="cpanel-version">
                <strong><?php echo Text::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>
                <div><?php echo $this->xml->version . ' (' . $this->xml->creationDate . ')'; ?></div>
                <?php if ($hasTour) : ?>
                    <div class="mt-2">
                        <button class="btn btn-info btn-sm button-start-guidedtour" type="button" data-gt-uid="com_proclaim_whats_new_10_1">
                            <span class="icon-location" aria-hidden="true"></span>
                            <?php echo Text::_('COM_PROCLAIM_TOUR_START_BUTTON'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-8 cpanel-intro">
            <h3><?php echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>
            <p><?php echo Text::_('JBS_CPL_INTRO'); ?></p>
            <p>
                <a href="https://www.christianwebministries.org/documentation/8-proclaim.html" target="_blank">
                    <?php echo Text::_('JBS_CPL_ONLINE_DOCUMENTATION'); ?>
                </a> -
                <a href="https://github.com/Joomla-Bible-Study/Proclaim/discussions/categories/q-a" target="_blank">
                    <?php echo Text::_('JBS_CPL_VISIT_FAQ'); ?>
                </a>
            </p>
        </div>
        <div class="clearfix" style="margin:10px;"></div>
        <div class="cpanel-buttons">
            <h2 class="text-center">
                <?php echo Text::_('JBS_CPL_MENUE_LINKS'); ?>
            </h2>
            <div class="container">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 justify-content-center">
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmadmin'); ?>"
                           title="<?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?>" class="cpanel-btn">
                            <i class="icon-options fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessages'); ?>"
                           title="<?php echo Text::_('JBS_CMN_STUDIES'); ?>" class="cpanel-btn">
                            <i class="icon-book fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_STUDIES'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmediafiles'); ?>"
                           title="<?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?>" class="cpanel-btn">
                            <i class="icon-video fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmteachers'); ?>"
                           title="<?php echo Text::_('JBS_CMN_TEACHERS'); ?>" class="cpanel-btn">
                            <i class="icon-user fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_TEACHERS'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmseries'); ?>"
                           title="<?php echo Text::_('JBS_CMN_SERIES'); ?>" class="cpanel-btn">
                            <i class="icon-tree-2 fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_SERIES'); ?></span>
                        </a>
                    </div>
                    <?php if (!$simple->mode) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessagetypes'); ?>"
                           title="<?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?>" class="cpanel-btn">
                            <i class="icon-list-2 fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmlocations'); ?>"
                           title="<?php echo Text::_('JBS_CMN_LOCATIONS'); ?>" class="cpanel-btn">
                            <i class="icon-home fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_LOCATIONS'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtopics'); ?>"
                           title="<?php echo Text::_('JBS_CMN_TOPICS'); ?>" class="cpanel-btn">
                            <i class="icon-tags fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_TOPICS'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmcomments'); ?>"
                           title="<?php echo Text::_('JBS_CMN_COMMENTS'); ?>" class="cpanel-btn">
                            <i class="icon-comments-2 fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_COMMENTS'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmservers'); ?>"
                           title="<?php echo Text::_('JBS_CMN_SERVERS'); ?>" class="cpanel-btn">
                            <i class="icon-database fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_SERVERS'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmpodcasts'); ?>"
                           title="<?php echo Text::_('JBS_CMN_PODCASTS'); ?>" class="cpanel-btn">
                            <i class="fa-solid fa-podcast fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_PODCASTS'); ?></span>
                        </a>
                    </div>
                    <?php if (!$simple->mode) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplates'); ?>"
                           title="<?php echo Text::_('JBS_CMN_TEMPLATES'); ?>" class="cpanel-btn">
                            <i class="icon-grid fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_TEMPLATES'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplatecodes'); ?>"
                           title="<?php echo Text::_('JBS_CMN_TEMPLATECODE'); ?>" class="cpanel-btn">
                            <i class="fa-solid fa-file-code fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_TEMPLATECODE'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="clearfix" style="margin:10px;"></div>
        <!-- BEGIN: STATS -->
        <div class="fbstatscover d-none d-md-block">
            <h1 class="text-center"><?php echo Text::_('JBS_CPL_GENERAL_STAT'); ?></h1>
            <table class="table table-striped table-responsive table-hover">
                <colgroup>
                    <col class="col1">
                    <col class="col2">
                    <col class="col2">
                    <col class="col2">
                </colgroup>
                <thead class="thead-light">
                <tr>
                    <th><?php echo Text::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_PUBLISHED'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_ARCHIVED'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_TOTAL'); ?></th>
                </tr>
                </thead>
                <?php
        $yesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
$lastmonth         = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y") - 1);
$today             = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

// Entity state counts
$entityStats = [
    ['label' => 'JBS_CPL_TOTAL_MESSAGES', 'table' => '#__bsms_studies'],
    ['label' => 'JBS_CPL_TOTAL_MEDIA_FILES', 'table' => '#__bsms_mediafiles'],
    ['label' => 'JBS_CPL_TOTAL_COMMENTS', 'table' => '#__bsms_comments'],
    ['label' => 'JBS_CPL_TOTAL_TOPICS', 'table' => '#__bsms_topics'],
];
?>
                <tbody>
                <?php foreach ($entityStats as $entity) : ?>
                <tr>
                    <td><?php echo Text::_($entity['label']); ?></td>
                    <td><strong><?php echo CwmcountHelper::getCountByState($entity['table'], 1); ?></strong></td>
                    <td><strong><?php echo CwmcountHelper::getCountByState($entity['table'], 2); ?></strong></td>
                    <td><strong><?php echo CwmcountHelper::getTotalCount($entity['table']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table table-striped table-responsive table-hover mt-3">
                <colgroup>
                    <col class="col1">
                    <col class="col2">
                    <col class="col1">
                    <col class="col2">
                </colgroup>
                <thead class="thead-light">
                <tr>
                    <th><?php echo Text::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_VALUE'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php echo Text::_('JBS_CPL_VALUE'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo Text::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
                    <td><strong><?php echo Cwmstats::getTopStudies(); ?></strong></td>
                    <td><?php echo Text::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
                    <td><strong><?php echo Cwmstats::getTopThirtyDays(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo Text::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
                    <td><strong><?php echo Cwmstats::getTotalDownloads(); ?></strong></td>
                    <td><?php echo Text::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
                    <td><strong><?php echo Cwmstats::getTopDownloads(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo Text::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
                    <td><strong><?php echo Cwmstats::getDownloadsLastThreeMonths(); ?></strong></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?php echo Text::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
                    <td><strong><?php echo Cwmstats::getTopScore(); ?></strong></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
