<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

    use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
    use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
    use Joomla\CMS\HTML\HTMLHelper;
    use Joomla\CMS\Language\Text;
    use Joomla\CMS\Router\Route;
    use Joomla\Input\Input;

    // Load the tooltip behavior.
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown');

$msg   = '';
$input = new Input();
$msg   = $input->get('msg');

if ($msg) {
    echo $msg;
}

$simple = Cwmhelper::getSimpleView();
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
            <?php
        elseif ($this->hasPostInstallationMessages === null) : ?>
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
                echo Route::_('index.php?option=com_proclaim&task=cwmadmin.edit&id=1'); ?>"
                   class="btn btn-primary btn-large">
                    <?php
                    echo Text::_('JBS_CPANEL_SIMPLE_MODE_LINK'); ?>
                </a>
            </div>
            <?php
        }
        ?>
        <div class="col-lg-4 bg-light text-dark rounded" style="padding:5px">
            <div id="fbheader">
                <a href="<?php
                echo Route::_('index.php?option=com_proclaim&view=cpanel'); ?>"><img
                            src="../media/com_proclaim/images/proclaim.jpg"
                            alt="<?php
                            echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"
                            style="width:100px;height:100px;"/></a>
            </div>
            <div id="fbmenu" class="">
                <strong><?php
                    echo Text::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>

                <div class=""><?php
                    echo $this->xml->version . ' (' . $this->xml->creationDate . ')'; ?></div>
            </div>
        </div>
        <div class="col-lg-8">
            <h3><?php
                echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>
            <br/>
            <p><?php
                echo Text::_('JBS_CPL_INTRO'); ?></p>
            <p><a href="https://www.christianwebministries.org/documentation/8-proclaim.html"
                  target="_blank">
                    <?php
                    echo Text::_('JBS_CPL_ONLINE_DOCUMENTATION'); ?></a> - <a
                        href="https://github.com/Joomla-Bible-Study/Proclaim/discussions/categories/q-a"
                        target="_blank">
                    <?php
                    echo Text::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
        </div>
        <div class="clearfix" style="margin:10px;"></div>
        <div class="visible-desktop">
            <h2 class="text-center">
                <?php
                echo Text::_('JBS_CPL_MENUE_LINKS'); ?>
            </h2>
            <div class="container">
                <div class="row">
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;task=cwmadmin.edit&amp;id=1'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_ADMINISTRATION'); ?>" class="btn"> <i
                                    class="icon-big icon-options fa-3x"></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_ADMINISTRATION'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessages'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_STUDIES'); ?>" class="btn"> <i
                                    class="icon-big icon-book fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_STUDIES'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmmediafiles'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_MEDIA_FILES'); ?>" class="btn"> <i
                                    class="icon-big icon-video fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_MEDIA_FILES'); ?> </span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmteachers'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_TEACHERS'); ?>" class="btn"> <i
                                    class="icon-user icon-big fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_TEACHERS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmseries'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_SERIES'); ?>" class="btn"> <i
                                    class="icon-big icon-tree-2 fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_SERIES'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <?php
                        if (!$simple->mode) : ?>
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessagetypes'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_MESSAGETYPES'); ?>" class="btn"> <i
                                        class="icon-big icon-list-2 fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_MESSAGETYPES'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmlocations'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_LOCATIONS'); ?>" class="btn"> <i
                                        class="icon-big icon-home fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_LOCATIONS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmtopics'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_TOPICS'); ?>" class="btn"> <i
                                        class="icon-big icon-tags fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_TOPICS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmcomments'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_COMMENTS'); ?>" class="btn"><i
                                        class="icon-big icon-comments-2 fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_COMMENTS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                            <?php
                        endif; ?>
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmservers'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_SERVERS'); ?>" class="btn"><i
                                    class="icon-big icon-database fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_SERVERS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <a href="<?php
                        echo Route::_('index.php?option=com_proclaim&amp;view=cwmpodcasts'); ?>"
                           title="<?php
                            echo Text::_('JBS_CMN_PODCASTS'); ?>" class="btn"><i
                                    class="icon-big fa-solid fa-podcast fa-3x" ></i><br>
                            <span><?php
                                echo Text::_('JBS_CMN_PODCASTS'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                        <?php
                        if (!$simple->mode) : ?>
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplates'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_TEMPLATES'); ?>" class="btn"><i
                                        class="icon-big icon-grid fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_TEMPLATES'); ?></span></a>
                    </div>
                    <div class="col" style="text-align: center">
                            <a href="<?php
                            echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplatecodes'); ?>"
                               title="<?php
                                echo Text::_('JBS_CMN_TEMPLATECODE'); ?>" class="btn"> <i
                                        class="icon-big fa-solid fa-file-code fa-3x" ></i><br>
                                <span><?php
                                    echo Text::_('JBS_CMN_TEMPLATECODE'); ?></span></a>
                    </div>
                            <?php
                        endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix" style="margin:10px;"></div>
        <!-- BEGIN: STATS -->
        <div class="fbstatscover d-none d-md-block">
            <table class="fbstat table table-striped table-responsive">

                <col class="col1">
                <col class="col2">
                <col class="col1">
                <col class="col2">
                <thead class="thead-light">
                <tr><span style="text-align: center;"><?php
                        echo Text::_('JBS_CPL_GENERAL_STAT'); ?> </span></tr>
                <tr>
                    <th><?php
                        echo Text::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php
                        echo Text::_('JBS_CPL_VALUE'); ?></th>
                    <th><?php
                        echo Text::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php
                        echo Text::_('JBS_CPL_VALUE'); ?></th>
                </tr>
                </thead>
                <?php
                $yesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
                $lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y") - 1);
                $today     = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
                ?>
                <tbody>
                <tr>
                    <td><?php
                        echo Text::_('JBS_CPL_TOTAL_MESSAGES'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTotalMessages(); ?></strong></td>
                    <td><?php
                        echo Text::_('JBS_CPL_TOTAL_COMMENTS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTotalComments(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php
                        echo Text::_('JBS_CPL_TOTAL_TOPICS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTotalTopics(); ?></strong></td>
                    <td><?php
                        echo Text::_('JBS_CPL_TOTAL_MEDIA_FILES'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTotalMediaFiles(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php
                        echo Text::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTopStudies(); ?></strong></td>
                    <td><?php
                        echo Text::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTopThirtyDays(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php
                        echo Text::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTotalDownloads(); ?></strong></td>
                    <td><?php
                        echo Text::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTopDownloads(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php
                        echo Text::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getDownloadsLastThreeMonths(); ?></strong></td>
                    <td></td>
                    <td><strong></strong></td>
                </tr>
                <tr>
                    <td> <?php
                        echo Text::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
                    <td><strong><?php
                            echo Cwmstats::getTopScore(); ?></strong></td>
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
