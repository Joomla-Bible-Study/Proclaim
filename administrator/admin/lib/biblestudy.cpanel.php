<?php
/**
* @version $Id: kunena.cpanel.php 502 2009-03-06 18:05:01Z fxstein $
* Kunena Component
* @package Kunena
*
* @Copyright (C) 2008 - 2009 Kunena Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.com
*
* Based on FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on Joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/
/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die('Restricted access');
?>
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
<div class="fbwelcome">
  <h3><?php echo 'Joomla Bible Study'?></h3>
  <p><?php echo 'From here you can access all of the different views and tasks for creating and maintaining your sermons, messages, and studies. You can also find statistics about your studies located in one convenient place.';?></p>
</div>
<div style="border:1px solid #ddd; background:#FBFBFB;">
  <table class = "thisform">
    <tr class = "thisform">
      <td width = "100%" valign = "top" class = "thisform"><div id = "cpanel">
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=admin&amp;layout=form" style = "text-decoration:none;" title = "<?php echo JText::_('Administration');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Administration'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=studieslist" style = "text-decoration:none;" title = "<?php echo JText::_('Studies/Sermons');?>"> <img src = "components/com_biblestudy/images/openbible.gif" align = "middle" border = "0"/> <span> <?php echo JText::_('Studies/Sermons'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=mediafileslist" style = "text-decoration:none;" title = "<?php echo JText::_('Media Files');?>"> <img src = "components/com_biblestudy/images/openbible.gif" align = "middle" border = "0"/> <span> <?php echo JText::_('Media Files'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=templateslist" style = "text-decoration:none;" title = "<?php echo JText::_('Template Display Settings');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Template Display Settings'); ?> </span> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=cssedit" style = "text-decoration:none;" title = "<?php echo JText::_('Edit CSS');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Edit CSS'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=podcastlist" style = "text-decoration:none;" title = "<?php echo JText::_('Podcasts');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Podcasts'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=serieslist" style = "text-decoration:none;" title = "<?php echo JText::_('Series');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Series'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=teacherlist" style = "text-decoration:none;" title = "<?php echo JText::_('Teachers');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Teachers');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=bookslist" target = "_blank" style = "text-decoration:none;" title = "<?php echoJText::_('Bible Books');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echoJText::_('Bible Books'); ?> </span></a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=medialist" style = "text-decoration:none;" title = "<?php echo JText::_('Media Images');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Media Images');?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index3.php?option=com_biblestudy&amp;view=serverslist" style = "text-decoration:none;" title = "<?php echo JText::_('Servers');?>"> <img src = "components/com_biblestudy/images/openbible.gif" align = "middle" border = "0"/> <span> <?php echo JText::_('Servers'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=folderslist" style = "text-decoration:none;" title = "<?php echo JText::_('Server Folders');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Server Folders'); ?> </a> </div>
          </div>
          <div style = "float:left;">
            <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=messagetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('Message Types');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Message Types'); ?> </a> </div>
          </div>
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=locationslist" style = "text-decoration:none;" title = "<?php echo JText::_('Locations');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Locations'); ?> </a> </div>
          </div>
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=topicslist" style = "text-decoration:none;" title = "<?php echo JText::_('Topics');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Topics'); ?> </a> </div>
          </div>
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=mimetypelist" style = "text-decoration:none;" title = "<?php echo JText::_('Mime Types');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Mime Types'); ?> </a> </div>
          </div>
          <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=commentslist" style = "text-decoration:none;" title = "<?php echo JText::_('Comments');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Comments'); ?> </a> </div>
          </div>
           <div class = "icon"> <a href = "index2.php?option=com_biblestudy&amp;view=sharelist" style = "text-decoration:none;" title = "<?php echo JText::_('Social Networking Links');?>"> <img src = "components/com_biblestudy/images/openbible.gif"  align = "middle" border = "0"/> <span> <?php echo JText::_('Social Networking Links'); ?> </a> </div>
          </div>
        </div></td>
    </tr>
  </table>
</div>
<!-- BEGIN: STATS -->
<div class="fbstatscover">
  <?php 
   
   include_once (JPATH_COMPONENT_ADMINISTRATOR .'/lib/biblestudy.stats.class.php');
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
	?>
    <tbody>
      <tr>
        <td><?php echo _STATS_TOTAL_MEMBERS; ?> </td>
        <td><strong><?php echo jbStats::get_total_members(); ?></strong></td>
        <td><?php echo _STATS_TOTAL_CATEGORIES; ?> </td>
        <td><strong><?php echo jbStats::get_total_categories();?></strong></td>
      </tr>
      <tr>
        <td><?php echo JText::_('Total Messages'); ?></td>
        <td><strong><?php echo jbStats::get_total_messages() ;?></strong></td>
        <td><?php echo JText::_('Total Comments'); ?> </td>
        <td><strong><?php echo jbStats::get_total_comments() ;?></strong></td>
      </tr>
      <tr>
        <td><?php echo JText::_(); ?></td>
        <td><strong><?php echo jbStats::get_total_topics() ;?></strong></td>
        <td><?php /* echo _STATS_LATEST_MEMBER; ?> </td>
        <td><strong><?php echo jbStats::get_latest_member() ;?></strong></td>
      </tr>
      <tr>
        <td><?php echo _STATS_TODAY_TOPICS; ?></td>
        <td><strong><?php echo jbStats::get_total_topics(date("Y-m-d 00:00:01"),date("Y-m-d 23:59:59")) ;?></strong></td>
        <td><?php echo _STATS_YESTERDAY_TOPICS; ?> </td>
        <td><strong><?php //echo jbStats::get_total_topics(date("Y-m-d 00:00:01",$yesterday),date("Y-m-d 23:59:59",$yesterday)) ;?></strong></td>
      </tr>
      <tr>
        <td><?php echo _STATS_TODAY_REPLIES; ?></td>
        <td><strong><?php //echo jbStats::get_total_messages(date("Y-m-d 00:00:01"),date("Y-m-d 23:59:59")) ;?></strong></td>
        <td><?php echo _STATS_YESTERDAY_REPLIES; ?></td>
        <td><strong>
          <?php
//	echo jbStats::get_total_messages(date("Y-m-d 00:00:01",$yesterday),date("Y-m-d 23:59:59",$yesterday)) ;
	?>
          </strong></td>
      </tr>
    </tbody>
  </table>
  <!-- B: UserStat -->
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="49%" valign="top"><!-- -->
        <table cellspacing="1"  border="0" width="100%" class="fbstat">
          <caption>
          <?php echo _STATS_TOP_POSTERS; ?>
          </caption>
          <col class="col1">
          <col class="col2">
          <col class="col2">
          <thead>
            <tr>
              <th><?php echo _KUNENA_USRL_USERNAME;?></th>
              <th></th>
              <th><?php echo _KUNENA_USRL_HITS;?></th>
            </tr>
          </thead>
          <tbody>
            <?php
	$KUNENA_top_posters=jbStats::get_top_posters();
	foreach ($KUNENA_top_posters as $KUNENA_poster) {
		if ($KUNENA_poster->posts == $KUNENA_top_posters[0]->posts) {
			$barwidth = 100;
		}
		else {
			$barwidth = round(($KUNENA_poster->posts * 100) / $KUNENA_top_posters[0]->posts);
		}
	?>
            <tr>
              <td><?php echo $KUNENA_poster->username;?> </td>
              <td ><img style="margin-bottom:1px" src="<?php echo KUNENA_DIRECTURL.'/template/default/images/bar.gif'; ?>" alt="" height="15" width="<?php echo $barwidth;?>"> </td>
              <td ><?php echo $KUNENA_poster->posts;?></td>
            </tr>
            <?php
	}
	?>
          </tbody>
        </table>
        <!-- / -->
      </td>
      <td width="1%">&nbsp;</td>
      <td width="49%" valign="top"><!--  -->
        <table cellspacing="1"  border="0" width="100%" class="fbstat">
          <caption>
          <?php echo  _STATS_POPULAR_PROFILE; ?>
          </caption>
          <col class="col1">
          <col class="col2">
          <col class="col2">
          <thead>
            <tr>
              <th><?php echo _KUNENA_USRL_USERNAME;?></th>
              <th></th>
              <th><?php echo _KUNENA_USRL_HITS;?></th>
            </tr>
          </thead>
          <tbody>
            <?php
		$fb_top_profiles=jbStats::get_top_profiles();
		foreach ($fb_top_profiles as $fb_profile) {
			if ($fb_profile->uhits == $fb_top_profiles[0]->uhits)
				$barwidth = 100;
			else
				$barwidth = round(($fb_profile->uhits * 100) / $fb_top_profiles[0]->uhits);
	?>
            <tr>
              <td><?php echo $fb_profile->username; ?></td>
              <td ><img style="margin-bottom:1px" src="<?php echo '../images/bar.gif'; ?>" alt="" height="15" width="<?php echo $barwidth;?>"> </td>
              <td ><?php echo $fb_profile->uhits;?></td>
            </tr>
            <?php
		} */
	?>
          </tbody>
        </table>
        <!-- / -->
      </td>
    </tr>
  </table>
  <!-- F: UserStat -->
  <!-- Begin : Top popular topics -->
  <table cellspacing="1"  border="0" width="100%" class="fbstat">
    <caption>
    <?php /*echo _STATS_POPULAR_TOPICS; ?>
    </caption>
    <col class="col1">
    <col class="col2">
    <col class="col2">
    <thead>
      <tr>
        <th><?php echo _KUNENA_USERPROFILE_TOPICS;?></th>
        <th></th>
        <th><?php echo _KUNENA_USRL_HITS;?></th>
      </tr>
    </thead>
    <tbody>
      <?php
	$KUNENA_top_posts=jbStats::get_top_studies();
	foreach ($KUNENA_top_posts as $KUNENA_post) {
		if ($KUNENA_post->hits == $KUNENA_top_posts[0]->hits) {
			$barwidth = 100;
		}
		else {
			$barwidth = round(($KUNENA_post->hits * 100) / $KUNENA_top_posts[0]->hits);
		}
		$link = KUNENA_LIVEURL.'&func=view&id='.$KUNENA_post->id.'&catid='.$KUNENA_post->catid;
	?>
      <tr>
        <td ><a href="<?php echo $link;?>"><?php echo $KUNENA_post->subject;?></a> </td>
        <td ><img src="<?php echo KUNENA_DIRECTURL.'/template/default/images/bar.gif'; ?>" alt="" style="margin-bottom:1px" height="15" width="<?php echo $barwidth;?>"> </td>
        <td ><?php echo $KUNENA_post->hits;?></td>
      </tr>
      <?php } */?>
    </tbody>
  </table>
  <!-- Finish : Top popular topics -->
</div>
<!-- FINISH: STATS -->
