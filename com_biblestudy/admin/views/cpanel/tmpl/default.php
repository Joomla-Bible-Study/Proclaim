<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.debug.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
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
<!-- Header -->
 <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="170" valign="top">
    <!-- Begin: AdminLeft -->
        <div id="fbheader">
 <a href = "index.php?option=com_biblestudy&view=cpanel"><img src = "components/com_biblestudy/images/logo.png"  border="0" alt = "<?php echo 'Joomla Bible Study'; ?>"/></a>
 <!-- Here is where the version information will go -->
 </div>
 <div id="fbmenu">
<table><tr><td><strong>Version Information: </strong></td></tr>
<tr><td>
<?php require_once (BIBLESTUDY_PATH_LIB .DS. 'biblestudy.version.php');?>
<div class="fbmainmenu"><?php echo CBiblestudyVersion::version(); ?></div>
</td></tr>
<tr><td><div><?php echo JText::_('Current Version').': '.$this->versioncheck;?></td></tr>
<tr><td><?php echo '<a href="http://www.JoomlaBibleStudy.org">'.JText::_('Get Latest Version').'</a>';?></div></td></tr>
</table> 
 </div>
 </td><td>
<div class="fbwelcome">
 
  <h3><?php echo 'Joomla Bible Study';?></h3>
  <p><?php echo JText::_( 'From here you can access all of the different views and tasks for creating and maintaining your sermons, messages, and studies. You can also find statistics about your studies located in one convenient place.' ).' - <a href="http://www.joomlabiblestudy.org/jbsdocs" target="_blank">'.JText::_('Online Documentation').'</a> - <a href="http://www.joomlabiblestudy.org/forums.html" target="_blank">'.JText::_('Visit our Forum with questions');?></a></p>
</div>
<div style="border:1px solid #ddd; background:#FBFBFB;">
  <table class = "thisform">
  <caption><h3>
    <?php echo JText::_('Menu Links'); ?>
    </caption></h3>
    <tr class = "thisform">
      <td width = "100%" valign = "top" class = "thisform"><div id = "cpanel">
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=admin&amp;layout=form" style = "text-decoration:none;" title = "<?php echo JText::_('Administration');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-administration.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Administration'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=studieslist" style = "text-decoration:none;" title = "<?php echo JText::_('Studies/Sermons');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-studies.png" align = "middle" border = "0"/> <span> <?php echo JText::_('Studies/Sermons'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=mediafileslist" style = "text-decoration:none;" title = "<?php echo JText::_('Media Files');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mp3.png" align = "middle" border = "0"/> <span> <?php echo JText::_('Media Files'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=templateslist" style = "text-decoration:none;" title = "<?php echo JText::_('Template Settings');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-templates.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Template Settings'); ?> </span> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=cssedit" style = "text-decoration:none;" title = "<?php echo JText::_('Edit CSS');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-css.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Edit CSS'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=podcastlist" style = "text-decoration:none;" title = "<?php echo JText::_('Podcasts');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-podcast.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Podcasts'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=serieslist" style = "text-decoration:none;" title = "<?php echo JText::_('Series');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-series.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Series'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=teacherlist" style = "text-decoration:none;" title = "<?php echo JText::_('Teachers');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-teachers.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Teachers');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=bookslist" style = "text-decoration:none;" title = "<?php echo JText::_('Bible Books');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-biblebooks.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Bible Books'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=medialist" style = "text-decoration:none;" title = "<?php echo JText::_('Media Images');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mediaimages.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Media Images');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=serverslist" style = "text-decoration:none;" title = "<?php echo JText::_('Servers');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-servers.png" align = "middle" border = "0"/> <span> <?php echo JText::_('Servers'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=folderslist" style = "text-decoration:none;" title = "<?php echo JText::_('Server Folders');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-folder.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Server Folders'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=messagetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('Message Types');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-messagetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Message Types'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=locationslist" style = "text-decoration:none;" title = "<?php echo JText::_('Locations');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-locations.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Locations'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=topicslist" style = "text-decoration:none;" title = "<?php echo JText::_('Topics');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-topics.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Topics'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=mimetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('Mime Types');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-mimetype.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Mime Types'); ?> </a> </div>
          </div>
          <div style = "float:left;">
          <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=commentslist" style = "text-decoration:none;" title = "<?php echo JText::_('Comments');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-comments.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Comments'); ?> </a> </div>
          </div>
          <div style = "float:left;">
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=sharelist" style = "text-decoration:none;" title = "<?php echo JText::_('Social Networking Links');?>"> <img src = "components/com_biblestudy/images/icons/icon-48-social.png"  align = "middle" border = "0"/> <span> <?php echo JText::_('Social Networking Links'); ?> </a> </div>
          </div>
        </div></td>
    </tr>
  </table>
</div>
<!-- BEGIN: STATS -->
<div class="fbstatscover">
  <?php 
   
//   include_once (JPATH_COMPONENT_ADMINISTRATOR .'/lib/biblestudy.stats.class.php');
   include_once ('..'.DS.'components'.DS.'com_biblestudy'.DS.'lib'.DS.'biblestudy.stats.class.php');
//   include_once (JURI::base().DS.'lib' .DS. 'biblestudy.stats.class.php');
//   include_once ('..');
    ?>
  <table cellspacing="1"  border="0" width="100%" class="fbstat">
    <caption>
    <?php echo JText::_('General Statistics'); ?>
    </caption>
    <col class="col1">
    <col class="col2">
    <col class="col1">
    <col class="col2">
    <thead>
      <tr>
        <th><?php echo JText::_('Statistic');?></th>
        <th><?php echo JText::_('Value');?></th>
        <th><?php echo JText::_('Statistic');?></th>
        <th><?php echo JText::_('Value');?></th>
      </tr>
    </thead>
    <?php
	$yesterday = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	$lastmonth = mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")-1);
	$today = 	mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	?>
    <tbody>
      
      <tr>
        <td><?php echo JText::_('Total Messages'); ?></td>
        <td><strong><?php echo jbStats::get_total_messages() ;?></strong></td>
        <td><?php echo JText::_('Total Comments'); ?> </td>
        <td><strong><?php echo jbStats::get_total_comments() ;?></strong></td>
      </tr>
      <tr>
        <td><?php echo JText::_('Total Topics'); ?></td>
        <td><strong><?php echo jbStats::get_total_topics() ;?></strong></td>
        <td><?php echo JText::_('Total Media Files'); ?></td>
        <td><strong><?php echo jbStats::total_mediafiles() ; ?></strong></td>
        </tr>
      <tr>
      	<td><?php echo JText::_('Top 5 Studies Hits'); ?></td>
        <td><strong><?php echo jbStats::get_top_studies() ; ?></strong></td>
  	 	<td><?php echo JText::_('Top 5 Studies Hits 90 Days'); ?></td>
        <td><strong><?php echo jbStats::get_topthirtydays() ;?></strong></td>
     </tr>
    <tr>
      	<td><?php echo JText::_('Total Downloads'); ?></td><td><strong><?php echo jbStats::total_downloads() ; ?></strong></td>
  	 	<td><?php echo JText::_('Top 5 Downloads'); ?></td>
        <td><strong><?php echo jbStats::get_top_downloads() ;?></strong></td>
     </tr>
      <tr>
      	<td><?php echo JText::_('Top Downloads Last 90 Days'); ?></td><td><strong><?php echo jbStats::get_downloads_ninety() ; ?></strong></td>
  	 	<td></td>
        <td><strong></strong></td>
     </tr>
     <tr>
	 	<td> <?php echo JText::_('Top Studies (hits, plays, downloads)'); ?></td><td><strong><?php echo jbStats::top_score() ; ?></strong></td>
 		<td></td><td></td>
	</tr>
		<?php 
	?>
          </tbody>
        </table>
     </div>  
	</td></tr></table> <!-- Close out supbbotty -->
</td></tr> <!-- Close out main -->
</table>
</div> 
