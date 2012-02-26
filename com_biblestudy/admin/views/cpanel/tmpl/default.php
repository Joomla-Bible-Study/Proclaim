<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB .DIRECTORY_SEPARATOR. 'biblestudy.debug.php');
?>




<?php $msg = ''; $msg = JRequest::getVar('msg','','post'); if ($msg){echo $msg;} ?>
<!-- Header -->
 <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="170" valign="top">
    <!-- Begin: AdminLeft -->
       <div id="fbheader">
             <a href = "index.php?option=com_biblestudy&view=cpanel"><img src = "../media/com_biblestudy/images/logo.png"  border="0" alt = "<?php echo JText::_( 'JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"/></a>
             <!-- Here is where the version information will go -->
     </div>
     <div id="fbmenu">
            <table>
                <tr><td>
                        <strong><?php echo JText::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>
                </td></tr>
                <tr><td>
                    <?php ?>
                    <div class="fbmainmenu"><?php echo $this->jbsversion; ?></div>
                </td></tr>
                <tr><td>
                        <?php echo JText::_('JBS_CPL_LATEST_VERSION').':<br /> '.$this->versioncheck;?>
                </td></tr>
                <tr><td>
                        <?php echo '<a href="http://www.JoomlaBibleStudy.org">'.JText::_('JBS_CPL_GET_LATEST_VERSION').'</a>';?>
                </td></tr>
            </table>

     </div>
 </td><td>
        <div class="fbwelcome">
          <h3><?php echo JText::_( 'JBS_CMN_JOOMLA_BIBLE_STUDY');?></h3>
          <p><?php echo JText::_( 'JBS_CPL_INTRO' ).' - <a href="http://www.joomlabiblestudy.org/jbs-documentation/user-guide-7-0.html" target="_blank">'.JText::_('JBS_CPL_ONLINE_DOCUMENTATION').'</a> - <a href="http://www.joomlabiblestudy.org/forum/" target="_blank">'.JText::_('JBS_CPL_VISIT_FAQ');?></a></p>
        </div>
        <div style="border:1px solid #ddd; background:#FBFBFB;">
          <table class = "thisform">
          <caption><h3>
            <?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
            </h3></caption>
            <tr class = "thisform">
              <td width = "100%" valign = "top" class = "thisform"><div id = "cpanel">
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_ADMINISTRATION');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-administration.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=studieslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_STUDIES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-studies.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mediafileslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIA_FILES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-mp3.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=teacherlist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEACHERS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-teachers.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEACHERS');?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=serieslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERIES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-series.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERIES'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=messagetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MESSAGE_TYPES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-messagetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                   <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=locationslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_LOCATIONS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-locations.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                   <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=topicslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TOPICS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-topics.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=commentslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_COMMENTS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-comments.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=serverslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERVERS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-servers.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=folderslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_FOLDERS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-folder.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_FOLDERS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=podcastlist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_PODCASTS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-podcast.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=sharelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-social.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=templateslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEMPLATES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-templates.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=medialist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIAIMAGES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-mediaimages.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIAIMAGES');?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mimetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MIME_TYPES');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-mimetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MIME_TYPES'); ?> </a> </div>
                  </div>
                  <div style = "float:left;">
                    <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=styles" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CSS_CSS_EDIT');?>"> <img src = "../media/com_biblestudy/images/icons/icon-48-css.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CSS_CSS_EDIT'); ?> </span></a> </div>
                  </div>
                </div></td>
            </tr>
          </table>
        </div>

<!-- BEGIN: STATS -->
<div class="fbstatscover">
      <?php

      include_once (BIBLESTUDY_PATH_ADMIN_LIB .DIRECTORY_SEPARATOR. 'biblestudy.stats.class.php');

        ?>
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
            <th><?php echo JText::_('JBS_CPL_STATISTIC');?></th>
            <th><?php echo JText::_('JBS_CPL_VALUE');?></th>
            <th><?php echo JText::_('JBS_CPL_STATISTIC');?></th>
            <th><?php echo JText::_('JBS_CPL_VALUE');?></th>
          </tr>
        </thead>
        <?php
            $yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
            $lastmonth = mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")-1);
            $today = 	mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
            ?>
        <tbody>

          <tr>
            <td><?php echo JText::_('JBS_CPL_TOTAL_MESSAGES'); ?></td>
            <td><strong><?php echo jbStats::get_total_messages() ;?></strong></td>
            <td><?php echo JText::_('JBS_CPL_TOTAL_COMMENTS'); ?></td>
            <td><strong><?php echo jbStats::get_total_comments() ;?></strong></td>
          </tr>
          <tr>
            <td><?php echo JText::_('JBS_CPL_TOTAL_TOPICS'); ?></td>
            <td><strong><?php echo jbStats::get_total_topics() ;?></strong></td>
            <td><?php echo JText::_('JBS_CPL_TOTAL_MEDIA_FILES'); ?></td>
            <td><strong><?php echo jbStats::total_mediafiles() ; ?></strong></td>
            </tr>
          <tr>
            <td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
            <td><strong><?php echo jbStats::get_top_studies() ; ?></strong></td>
            <td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
            <td><strong><?php echo jbStats::get_topthirtydays() ;?></strong></td>
          </tr>
          <tr>
            <td><?php echo JText::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
            <td><strong><?php echo jbStats::total_downloads() ; ?></strong></td>
            <td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
            <td><strong><?php echo jbStats::get_top_downloads() ;?></strong></td>
          </tr>
          <tr>
            <td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
            <td><strong><?php echo jbStats::get_downloads_ninety() ; ?></strong></td>
            <td></td>
            <td><strong></strong></td>
          </tr>
          <tr>
            <td> <?php echo JText::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
            <td><strong><?php echo jbStats::top_score() ; ?></strong></td>
            <td></td>
            <td></td>
          </tr>
       </tbody>
      </table>
</div>
</td></tr>
 </table>
