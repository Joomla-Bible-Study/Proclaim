<?php
/**
 * Default
 * @package BibleStudy.Admin
 * @author Joomla Bible Study
 * @copyright 2010
 */
//No Direct Access
defined('_JEXEC') or die;

$msg = '';
$msg = JRequest::getVar('msg', '', 'post');
if ($msg) {
    echo $msg;
}
?>
<!-- Header -->
<form action="index.php" method="post" name="adminForm">
    <input type="hidden" name="option" value="com_biblestudy" />
    <input type="hidden" name="view" value="cpanel" />
</form>
<!-- Begin: AdminLeft -->
<div class="fltlft">
    <div id="fbheader">
        <a href = "index.php?option=com_biblestudy&view=cpanel"><img src = "../media/com_biblestudy/images/logo.png"  border="0" alt = "<?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"/></a>
        <!-- Here is where the version information will go -->
    </div>
    <div id="fbmenu">
        <strong><?php echo JText::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>
        <div class="fbmainmenu"><?php echo $this->version . ' (' . $this->versiondate . ')'; ?></div>
    </div>
</div>

<div class="fltrt" style="width: 87%">
    <div class="fbwelcome">
        <h3><?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>
        <p><?php echo JText::_('JBS_CPL_INTRO') . ' - <a href="http://www.joomlabiblestudy.org/jbs-documentation/user-guide-7-0.html" target="_blank">' . JText::_('JBS_CPL_ONLINE_DOCUMENTATION') . '</a> - <a href="http://www.joomlabiblestudy.org/forum/" target="_blank">' . JText::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
    </div>
    <div style="border:1px solid #ddd; background:#FBFBFB;">
        <h3 style="text-align: center">
            <?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
        </h3>
        <div id = "cpanel" style="padding-left: 20px">
            <div style = "float:left;">
                <div class = "icon"> <a href="index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title="<?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=messages" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_STUDIES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-studies.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mediafiles" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-mp3.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=teachers" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEACHERS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-teachers.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEACHERS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=series" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERIES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-series.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERIES'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=messagetypes" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-messagetype.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=locations" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_LOCATIONS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-locations.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=topics" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TOPICS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-topics.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=comments" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_COMMENTS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-comments.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=servers" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERVERS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-servers.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=folders" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_FOLDERS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-folder.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_FOLDERS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=podcasts" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_PODCASTS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-podcast.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=shares" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-social.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=templates" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEMPLATES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-templates.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mediaimages" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIAIMAGES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-mediaimages.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIAIMAGES'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mimetypes" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MIME_TYPES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-mimetype.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MIME_TYPES'); ?> </a> </div>
            </div>
            <div style = "float:left;">
                <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=styles" <?php if (BibleStudyHelper::debug() === '1'): ?>style="text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_STYLES'); ?>"<?php endif; ?>> <img src = "../media/com_biblestudy/images/icons/icon-48-css.png" alt="" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_STYLES'); ?> </span></a> </div>
            </div>
            <?php echo LiveUpdate::getIcon(); ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
    <!-- BEGIN: STATS -->
    <div class="fbstatscover">
        <table cellspacing="1"  border="0" width="100%" class="fbstat">
            <caption>
                <?php echo JText::_('JBS_CPL_GENERAL_STAT'); ?>
            </caption>
            <col class="col1">
            <col class="col2">
            <col class="col1">
            <col class="col2">
            <thead>
                <tr>
                    <th><?php echo JText::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php echo JText::_('JBS_CPL_VALUE'); ?></th>
                    <th><?php echo JText::_('JBS_CPL_STATISTIC'); ?></th>
                    <th><?php echo JText::_('JBS_CPL_VALUE'); ?></th>
                </tr>
            </thead>
            <?php
            $yesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
            $lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y") - 1);
            $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            ?>
            <tbody>
                <tr>
                    <td><?php echo JText::_('JBS_CPL_TOTAL_MESSAGES'); ?></td>
                    <td><strong><?php echo jbStats::get_total_messages(); ?></strong></td>
                    <td><?php echo JText::_('JBS_CPL_TOTAL_COMMENTS'); ?></td>
                    <td><strong><?php echo jbStats::get_total_comments(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('JBS_CPL_TOTAL_TOPICS'); ?></td>
                    <td><strong><?php echo jbStats::get_total_topics(); ?></strong></td>
                    <td><?php echo JText::_('JBS_CPL_TOTAL_MEDIA_FILES'); ?></td>
                    <td><strong><?php echo jbStats::total_mediafiles(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
                    <td><strong><?php echo jbStats::get_top_studies(); ?></strong></td>
                    <td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
                    <td><strong><?php echo jbStats::get_topthirtydays(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
                    <td><strong><?php echo jbStats::total_downloads(); ?></strong></td>
                    <td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
                    <td><strong><?php echo jbStats::get_top_downloads(); ?></strong></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
                    <td><strong><?php echo jbStats::get_downloads_ninety(); ?></strong></td>
                    <td></td>
                    <td><strong></strong></td>
                </tr>
                <tr>
                    <td> <?php echo JText::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
                    <td><strong><?php echo jbStats::top_score(); ?></strong></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="clear: both;"></div>
</div>
