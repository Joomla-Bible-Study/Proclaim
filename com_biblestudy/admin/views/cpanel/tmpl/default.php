<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.debug.php');
?>
<style>
#fbadmin {
text-align:left;
}
#fbheader {
clear:both;
}
#fbmenu {
margin-top:15px;
border-top:1px solid #ccc;
}
#fbmenu a{
display:block;
font-size:11px;
border-left:1px solid #ccc;
border-bottom:1px solid #ccc;

}
.fbmainmenu {
background:#FBFBFB;
padding:5px;
}
.fbactivemenu {
background:#fff;
padding:5px;
}
.fbsubmenu {
background:#fff;
padding-left:10px;
padding:5px 5px 5px 15px;
}
.fbright {
border:1px solid #ccc;
background:#fff;
padding:5px;
}
.fbfooter {
font-size:10px;
text-align: right;
padding:5px;
background:#FBFBFB;
border-bottom:1px solid #CCC;
border-left:1px solid #CCC;
border-right:1px solid #CCC;
}
.fbfunctitle {
font-size:16px;
text-align: left;
padding:5px;
background:#FBFBFB;
border:1px solid #CCC;
font-weight:bold;
margin-bottom:10px;
clear:both;
}
.fbfuncsubtitle {
font-size:14px;
text-align: left;
padding:5px;
border-bottom:3px solid  #7F9DB9;
font-weight:bold;
color:#7F9DB9;
margin:10px 0 10px 0;
}
.fbrow0 td {
padding:8px 5px;
text-align:left;
border-bottom:1px  dotted #ccc;
}
.fbrow1 td {
padding:8px 5px;
text-align:left;
border-bottom:1px dotted #ccc;
}
td.fbtdtitle {
font-weight:bold;
padding-left:10px;
color:#666;
}
#fbcongifcover fieldset {
border: 1px solid #CFDCEB;
}
#fbcongifcover fieldset legend{
color:#666;
}
</style>
<div id="fbadmin">
<!-- Header -->
 <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="170" valign="top">
    <!-- Begin: AdminLeft -->
       
<style>
.fbwelcome {
	clear:both;
	margin-bottom:10px;
	padding:10px;
	font-size:12px;
	color:#536482;
	line-height:140%;
	border:1px solid #ddd;
}
.fbwelcome h3 {
	margin:0;
	padding:0;
}
table.thisform {
	width: 100%;
	padding: 10px;
	border-collapse: collapse;
}
table.thisform tr.row0 {
	background-color: #F7F8F9;
}
table.thisform tr.row1 {
	background-color: #eeeeee;
}
table.thisform th {
	font-size: 15px;
	font-weight: normal;
	font-variant: small-caps;
	padding-top: 6px;
	padding-bottom: 2px;
	padding-left: 4px;
	padding-right: 4px;
	text-align: left;
	height: 25px;
	color: #666666;
	background: url(../images/background.gif);
	background-repeat: repeat;
}
table.thisform td {
	padding: 3px;
	text-align: left;
}
.fbstatscover {
	padding:0px;
}
table.fbstat {
	background-color:#FFFFFF;
	border:1px solid #ddd;
	padding:1px;
	width:100%;
}
table.fbstat th {
	background:#EEE;
	border-bottom:1px solid #CCC;
	border-top:1px solid #EEE;
	color:#666;
	font-size:11px;
	padding:3px 4px;
	text-align:left;
}
table.fbstat td {
	font-size:11px;
	line-height:140%;
	padding:4px;
	text-align:left;
}
table.fbstat caption {
	clear:both;
	font-size:14px;
	font-weight:bold;
	margin:10px 0 2px 0;
	padding:2px;
	text-align:left;
}
table.fbstat .col1 {
	background-color:#F1F3F5;
}
table.fbstat .col2 {
	background-color: #FBFBFB;
}
</style>
<?php $msg = ''; $msg = JRequest::getVar('msg','','post'); if ($msg){echo $msg;} ?>
<!-- Header -->
 <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="170" valign="top">
    <!-- Begin: AdminLeft -->
        <div id="fbheader">
 <a href = "index.php?option=com_biblestudy&view=cpanel"><img src = "components/com_biblestudy/images/logo.png"  border="0" alt = "<?php echo JText::_( 'JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"/></a>
 <!-- Here is where the version information will go -->
 </div>
 <div id="fbmenu">
<table><tr><td><strong><?php echo JText::_('JBS_CPL_VERSION_INFORMATION'); ?></strong></td></tr>
<tr><td>
<?php require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.version.php');?>
<div class="fbmainmenu"><?php echo CBiblestudyVersion::version(); ?></div>
</td></tr>
<tr><td><div><?php echo JText::_('JBS_CPL_LATEST_VERSION').': '.$this->versioncheck;?></td></tr>
<tr><td><?php echo '<a href="http://www.JoomlaBibleStudy.org">'.JText::_('JBS_CPL_GET_LATEST_VERSION').'</a>';?></div></td></tr>
</table> 
<table>
<tr><td>
<strong><?php echo JText::_('JBS_CPL_ASSETID_CHECK');?></strong>
</td></tr>
<tr><td><?php echo JText::_('JBS_CPL_JBS_ASSETID');?></td>
<td><?php echo $this->jbs_asset_id;?></td></tr>
<td><?php echo JText::_('JBS_CPL_JOOMLA_ASSETID');?></td>
<td><?php echo $this->joomla_asset_id;?></td></tr>
<tr><td>
<?php if ($this->jbs_asset_id != $this->joomla_asset_id)
    {
    echo '<p style=color:red;><a href="index.php?option=com_biblestudy&view=cpanel&function=fixassetid">'.JText::_('JBS_CPL_FIX_ASSETID_LINK').'</a></p>';
    }
    else echo '<p style=color:green;>'.JText::_('JBS_CPL_ASSETID_OKAY').'</p>';
    ?>
</td></tr>
 
</table>
 </div>
 </td><td>
<div class="fbwelcome">
 
  <h3><?php echo JText::_( 'JBS_CMN_JOOMLA_BIBLE_STUDY');?></h3>
  <p><?php echo JText::_( 'JBS_CPL_INTRO' ).' - <a href="http://www.joomlabiblestudy.org/jbsdocs" target="_blank">'.JText::_('JBS_CPL_ONLINE_DOCUMENTATION').'</a> - <a href="http://www.joomlabiblestudy.org/forums.html" target="_blank">'.JText::_('JBS_CPL_VISIT_FAQ');?></a></p>
</div>
<div style="border:1px solid #ddd; background:#FBFBFB;">
  <table class = "thisform">
  <caption><h3>
    <?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
    </caption></h3>
    <tr class = "thisform">
      <td width = "100%" valign = "top" class = "thisform"><div id = "cpanel">
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_ADMINISTRATION');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-administration.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=studieslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_STUDIES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-studies.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mediafileslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIA_FILES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mp3.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=teacherlist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEACHERS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-teachers.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEACHERS');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=serieslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERIES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-series.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERIES'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=messagetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MESSAGE_TYPES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-messagetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=locationslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_LOCATIONS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-locations.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=topicslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TOPICS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-topics.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=commentslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_COMMENTS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-comments.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=serverslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SERVERS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-servers.png" align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=folderslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_FOLDERS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-folder.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_FOLDERS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=podcastlist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_PODCASTS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-podcast.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=sharelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-social.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=templateslist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_TEMPLATES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-templates.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=medialist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MEDIAIMAGES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mediaimages.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MEDIAIMAGES');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=mimetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CMN_MIME_TYPES');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mimetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CMN_MIME_TYPES'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index.php?option=com_biblestudy&amp;view=cssedit" style = "text-decoration:none;" title = "<?php echo JText::_('JBS_CSS_CSS_EDIT');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-css.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('JBS_CSS_CSS_EDIT'); ?> </span></a> </div>
          </div>
        </div></td>
    </tr>
  </table>
</div>
<!-- BEGIN: STATS -->
<div class="fbstatscover">
  <?php 
   

   include_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.stats.class.php');

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
        <td><?php echo JText::_('JBS_CPL_TOTAL_COMMENTS'); ?> </td>
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
      	<td><?php echo JText::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td><td><strong><?php echo jbStats::total_downloads() ; ?></strong></td>
  	 	<td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
        <td><strong><?php echo jbStats::get_top_downloads() ;?></strong></td>
     </tr>
      <tr>
      	<td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td><td><strong><?php echo jbStats::get_downloads_ninety() ; ?></strong></td>
  	 	<td></td>
        <td><strong></strong></td>
     </tr>
     <tr>
	 	<td> <?php echo JText::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td><td><strong><?php echo jbStats::top_score() ; ?></strong></td>
 		<td></td><td></td>
	</tr>
          </tbody>
        </table>
     </div>   
