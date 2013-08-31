<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
if (BIBLESTUDY_CHECKREL)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.modal');
}
JHtml::_('behavior.multiselect');

$msg = '';
$input = new JInput;
$msg = $input->get('msg');

if ($msg)
{
	echo $msg;
}
?>
<!-- Header -->
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=cpanel'); ?>" method="post" name="adminForm"
      id="adminForm">
<div class="row-fluid">
<div id="j-sidebar-container" class="span2">
	<div id="fbheader">
		<a href="index.php?option=com_biblestudy&view=cpanel"><img src="../media/com_biblestudy/images/logo.png"
		                                                           border="0"
		                                                           alt="<?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"/></a>
	</div>
	<div id="fbmenu">
		<strong><?php echo JText::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>

		<div class="fbmainmenu"><?php echo $this->version . ' (' . $this->versiondate . ')'; ?></div>
	</div>
	<div id="jbspaypal">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="tfuller@livingwatersweb.com">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="item_name" value="Joomla Bible Study Team">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0"
			       name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
	<?php if (!empty($this->sidebar)): ?>
		<hr/>
		<?php echo $this->sidebar; ?>
	<?php endif; ?>
</div>
<div id="j-main-container" class="span10">
<div class="fbwelcome">
	<h3><?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>

	<p><?php echo JText::_('JBS_CPL_INTRO') . ' - <a href="http://www.joomlabiblestudy.org/jbs-documentation.html" target="_blank">' .
			JText::_('JBS_CPL_ONLINE_DOCUMENTATION') . '</a> - <a href="http://www.joomlabiblestudy.org/forum/" target="_blank">' .
			JText::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
</div>
<?php if (!BIBLESTUDY_CHECKREL)
{
	?>
	<div style="border:1px solid #ddd; background:#FBFBFB;">
		<h3 style="text-align: center">
			<?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
		</h3>

		<div id="cpanel" style="padding-left: 20px">
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-administration.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=messages"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_STUDIES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-studies.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=mediafiles"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-mp3.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=teachers"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_TEACHERS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-teachers.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_TEACHERS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=series"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_SERIES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-series.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_SERIES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=messagetypes"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-messagetype.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_MESSAGE_TYPES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=locations"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_LOCATIONS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-locations.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=topics"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_TOPICS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-topics.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=comments"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_COMMENTS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-comments.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=servers"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_SERVERS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-servers.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=folders"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_FOLDERS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-folder.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_FOLDERS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=podcasts"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_PODCASTS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-podcast.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=shares"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-social.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'); ?> </span></a>
				</div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=templates"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_TEMPLATES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-templates.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=mediaimages"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_MEDIAIMAGES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-mediaimages.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_MEDIAIMAGES'); ?> </a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=mimetypes"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_MIME_TYPES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-mimetype.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_MIME_TYPES'); ?> </span></a></div>
			</div>
			<div style="float:left;">
				<div class="icon"><a href="index.php?option=com_biblestudy&amp;view=styles"
				                     style="text-decoration:none;"
				                     title="<?php echo JText::_('JBS_CMN_STYLES'); ?>"> <img
							src="../media/com_biblestudy/images/icons/icon-48-css.png" alt="" align="middle"
							border="0"/> <span> <?php echo JText::_('JBS_CMN_STYLES'); ?> </span></a></div>
			</div>
			<?php echo LiveUpdate::getIcon(); ?>
		</div>
		<div style="clear: both;"></div>
	</div>
<?php } ?>
<div style="clear: both;"></div>
<!-- BEGIN: STATS -->
<div class="fbstatscover">
	<table cellspacing="1" border="0" width="100%" class="fbstat">
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
			<td><strong><?php echo JBSMStats::get_total_messages(); ?></strong></td>
			<td><?php echo JText::_('JBS_CPL_TOTAL_COMMENTS'); ?></td>
			<td><strong><?php echo JBSMStats::get_total_comments(); ?></strong></td>
		</tr>
		<tr>
			<td><?php echo JText::_('JBS_CPL_TOTAL_TOPICS'); ?></td>
			<td><strong><?php echo JBSMStats::get_total_topics(); ?></strong></td>
			<td><?php echo JText::_('JBS_CPL_TOTAL_MEDIA_FILES'); ?></td>
			<td><strong><?php echo JBSMStats::total_media_files(); ?></strong></td>
		</tr>
		<tr>
			<td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
			<td><strong><?php echo JBSMStats::get_top_studies(); ?></strong></td>
			<td><?php echo JText::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
			<td><strong><?php echo JBSMStats::get_top_thirty_days(); ?></strong></td>
		</tr>
		<tr>
			<td><?php echo JText::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
			<td><strong><?php echo JBSMStats::total_downloads(); ?></strong></td>
			<td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
			<td><strong><?php echo JBSMStats::get_top_downloads(); ?></strong></td>
		</tr>
		<tr>
			<td><?php echo JText::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
			<td><strong><?php echo JBSMStats::get_downloads_ninety(); ?></strong></td>
			<td></td>
			<td><strong></strong></td>
		</tr>
		<tr>
			<td> <?php echo JText::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
			<td><strong><?php echo JBSMStats::top_score(); ?></strong></td>
			<td></td>
			<td></td>
		</tr>
		</tbody>
	</table>
</div>
<div style="clear: both;"></div>
</div>
</div>
<?php echo JHtml::_('form.token'); ?>
</form>
