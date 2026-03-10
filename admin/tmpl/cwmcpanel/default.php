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
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmupgradeHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmcpanel\HtmlView $this */

// Load the tooltip behavior.
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown')
    ->useScript('bootstrap.alert')
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
        $this->document->addScriptOptions('com_proclaim.cpanel', ['startTour' => (int) $tourId]);
    }
}

$wa->useScript('com_proclaim.cwmcpanel');
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

            // Podcast task warning — only show when published podcasts exist and task is not enabled
            if (Cwmstats::hasPublishedPodcasts() && Cwmstats::getPodcastTaskRawState() !== 1) :
?>
            <div class="col-12 d-none" id="proclaim-podcast-task-notice">
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <span class="icon-warning-circle" aria-hidden="true"></span>
                    <?php echo Text::_('JBS_CMN_PODCAST_TASK_WARNING'); ?>
                    <a href="<?php echo Route::_('index.php?option=com_scheduler&view=tasks'); ?>" class="alert-link" target="_blank">
                        <?php echo Text::_('JBS_CMN_PODCAST_TASK_STATUS'); ?>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
                </div>
            </div>
            <?php endif; ?>
        <?php
            // Location system wizard opt-in card — shown to super admins when wizard has not been configured
            $cpanelUser = Factory::getApplication()->getIdentity();
            if ($cpanelUser->authorise('core.admin') && CwmlocationHelper::shouldShowWizard()) :
        ?>
            <div class="col-12">
                <div class="alert alert-primary">
                    <span class="icon-location" aria-hidden="true"></span>
                    <strong><?php echo Text::_('JBS_CPL_WIZARD_TITLE'); ?></strong>
                    <p class="mb-1"><?php echo Text::_('JBS_CPL_WIZARD_DESC'); ?></p>
                    <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmlocationwizard'); ?>"
                       class="btn btn-primary btn-sm">
                        <?php echo Text::_('JBS_CPL_WIZARD_BUTTON'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        <?php
            // Database schema out-of-sync warning
            $schemaGap = CwmupgradeHelper::isSchemaOutOfDate();
            if ($schemaGap) :
        ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <span class="icon-warning-circle" aria-hidden="true"></span>
                    <strong><?php echo Text::_('JBS_CPL_SCHEMA_OUT_OF_SYNC'); ?></strong>
                    <p class="mb-1">
                        <?php echo Text::sprintf('JBS_CPL_SCHEMA_OUT_OF_SYNC_DESC', $schemaGap['current'], $schemaGap['expected']); ?>
                    </p>
                    <a href="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>"
                       class="btn btn-warning btn-sm">
                        <?php echo Text::_('JBS_CPL_SCHEMA_FIX_BUTTON'); ?>
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
            <?php $isAdmin = $cpanelUser->authorise('core.admin'); ?>
            <div class="container">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 justify-content-center">
                    <?php if ($isAdmin) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmadmin'); ?>"
                           title="<?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?>" class="cpanel-btn">
                            <i class="icon-options fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
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
                    <?php if (!$simple->mode && $isAdmin) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessagetypes'); ?>"
                           title="<?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?>" class="cpanel-btn">
                            <i class="icon-list-2 fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($isAdmin) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmlocations'); ?>"
                           title="<?php echo Text::_('JBS_CMN_LOCATIONS'); ?>" class="cpanel-btn">
                            <i class="icon-home fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_LOCATIONS'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!$simple->mode && $isAdmin) : ?>
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
                    <?php if ($isAdmin) : ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmservers'); ?>"
                           title="<?php echo Text::_('JBS_CMN_SERVERS'); ?>" class="cpanel-btn">
                            <i class="icon-database fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_SERVERS'); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmpodcasts'); ?>"
                           title="<?php echo Text::_('JBS_CMN_PODCASTS'); ?>" class="cpanel-btn">
                            <i class="fa-solid fa-podcast fa-3x"></i>
                            <span><?php echo Text::_('JBS_CMN_PODCASTS'); ?></span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmanalytics'); ?>"
                           title="<?php echo Text::_('JBS_ANA_ANALYTICS'); ?>" class="cpanel-btn">
                            <i class="fa-solid fa-chart-bar fa-3x"></i>
                            <span><?php echo Text::_('JBS_ANA_ANALYTICS'); ?></span>
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
        <!-- BEGIN: YOUTUBE API HEALTH -->
        <?php
        if ($isAdmin) :
            $ytServers = [];

            try {
                $cpDb    = Factory::getContainer()->get(DatabaseInterface::class);
                $cpQuery = $cpDb->getQuery(true)
                    ->select([$cpDb->quoteName('id'), $cpDb->quoteName('server_name'), $cpDb->quoteName('params')])
                    ->from($cpDb->quoteName('#__bsms_servers'))
                    ->where($cpDb->quoteName('type') . ' = ' . $cpDb->quote('youtube'))
                    ->where($cpDb->quoteName('published') . ' = 1');
                $cpDb->setQuery($cpQuery);
                $cpServers = $cpDb->loadObjectList();

                foreach ($cpServers as $cpSrv) {
                    $cpParams   = new \Joomla\Registry\Registry($cpSrv->params);
                    $cpSrvId    = (int) $cpSrv->id;
                    $cpBudget   = max(1, (int) $cpParams->get('youtube_daily_quota', 10000));
                    $cpUsed     = CwmyoutubeQuota::getUsedToday($cpSrvId);
                    $cpPctUsed  = $cpBudget > 0 ? round(($cpUsed / $cpBudget) * 100) : 0;

                    $ytServers[] = [
                        'id'      => $cpSrvId,
                        'name'    => $cpSrv->server_name,
                        'budget'  => $cpBudget,
                        'used'    => $cpUsed,
                        'pctUsed' => min(100, $cpPctUsed),
                    ];
                }
            } catch (\Exception $e) {
                // DB not available
            }

            if (!empty($ytServers)) :
        ?>
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">
                            <i class="fa-brands fa-youtube text-danger me-1" aria-hidden="true"></i>
                            <?php echo Text::_('JBS_ADM_YOUTUBE_QUOTA_STATUS'); ?>
                        </h5>
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmadmin'); ?>#youtubelog"
                           class="btn btn-sm btn-outline-secondary">
                            <?php echo Text::_('JBS_CPL_YT_VIEW_DETAILS'); ?>
                        </a>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($ytServers as $ytS) :
                            $barClass = $ytS['pctUsed'] >= 90 ? 'bg-danger' : ($ytS['pctUsed'] >= 70 ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="d-flex justify-content-between small mb-1">
                                <span><?php echo htmlspecialchars($ytS['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="<?php echo $ytS['pctUsed'] >= 80 ? 'text-danger fw-bold' : 'text-body-secondary'; ?>">
                                    <?php echo $ytS['pctUsed']; ?>%
                                    <?php if ($ytS['pctUsed'] >= 80) : ?>
                                        <i class="icon-warning-circle" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;" role="progressbar"
                                 aria-valuenow="<?php echo $ytS['pctUsed']; ?>" aria-valuemin="0" aria-valuemax="100"
                                 aria-label="<?php echo htmlspecialchars($ytS['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="progress-bar <?php echo $barClass; ?>"
                                     style="width: <?php echo $ytS['pctUsed']; ?>%"></div>
                            </div>
                            <div class="text-body-secondary" style="font-size: 0.75rem;">
                                <?php echo Text::sprintf('JBS_ADM_YOUTUBE_QUOTA_USED', number_format($ytS['used']), number_format($ytS['budget'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
            endif; // !empty($ytServers)
        endif; // $isAdmin
        ?>
        <!-- BEGIN: STATS -->
        <?php if ($isAdmin) : ?>
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
        <?php endif; ?>
        <div style="clear: both;"></div>
    </div>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
