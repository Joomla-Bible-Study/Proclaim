<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$msg   = '';
$input = new JInput;
$msg   = $input->get('msg');

if ($msg)
{
	echo $msg;
}
?>
<!-- Header -->
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<?php if (!empty($this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<div id="fbheader">
				<a href="index.php?option=com_biblestudy&view=cpanel"><img
							src="../media/com_biblestudy/images/bible2015.png"
							border="0"
							alt="<?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"/></a>
			</div>
			<div id="fbmenu">
				<strong><?php echo JText::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>

				<div class="fbmainmenu"><?php echo $this->data->version . ' (' . $this->data->versiondate . ')'; ?></div>
			</div>

<<<<<<< HEAD
			<div id="jbspaypal">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_donations">
					<input type="hidden" name="business" value="tfuller@calvarynewberg.org">
					<input type="hidden" name="lc" value="US">
					<input type="hidden" name="item_name" value="Joomla Bible Study Team">
					<input type="hidden" name="no_note" value="0">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
					       border="0"
					       name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1"
					     height="1">
				</form>
			</div>
			<hr/>
			<?php echo $this->sidebar; ?>
			<hr/>
		</div>
		<div class="clearfix"></div>
		<div id="j-main-container" class="span10">
			<?php else : ?>
			<div id="j-main-container">
				<?php endif; ?>
				<?php if ($this->hasPostInstallationMessages): ?>
					<div class="alert alert-info">
						<h3>
							<?php echo JText::_('JBS_CPL_PIM_TITLE'); ?>
						</h3>

						<p>
							<?php echo JText::_('JBS_CPL_PIM_DESC'); ?>
						</p>
						<a href="index.php?option=com_postinstall&eid=<?php echo $this->extension_id ?>"
						   class="btn btn-primary btn-large">
							<?php echo JText::_('JBS_CPL_PIM_BUTTON'); ?>
						</a>
					</div>
				<?php elseif (is_null($this->hasPostInstallationMessages)): ?>
					<div class="alert alert-error">
						<h3>
							<?php echo JText::_('JBS_CPL_PIM_ERROR_TITLE'); ?>
						</h3>

						<p>
							<?php echo JText::_('JBS_CPL_PIM_ERROR_DESC'); ?>
						</p>
						<a href="http://www.joomlabiblestudy.org/jbs-documentation.html"
						   class="btn btn-primary btn-large">
							<?php echo JText::_('JBS_CPL_PIM_ERROR_BUTTON'); ?>
						</a>
					</div>
				<?php endif; ?>
				<div class="fbwelcome">
					<h3><?php echo JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>

					<p><?php echo JText::_('JBS_CPL_INTRO') . ' - <a href="http://www.joomlabiblestudy.org/jbs-documentation.html" target="_blank">' .
								JText::_('JBS_CPL_ONLINE_DOCUMENTATION') . '</a> - <a href="http://www.joomlabiblestudy.org/forum/" target="_blank">' .
								JText::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
				</div>
				<div style="border:1px solid #ddd; background:#FBFBFB;">
					<h3 style="text-align: center;">
						<?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
					</h3>

					<div id="cpanel" class="btn-group">
						<a href="index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?>" class="btn cpanl-img"> <span class="icon-options" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=messages"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_STUDIES'); ?>" class="btn cpanl-img"> <span class="icon-book" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=mediafiles"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?>" class="btn cpanl-img"> <span class="icon-video" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=teachers"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_TEACHERS'); ?>" class="btn cpanl-img"> <span class="icon-user" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_TEACHERS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=series"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_SERIES'); ?>" class="btn cpanl-img"> <span class="icon-tree-2" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_SERIES'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=messagetypes"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_MESSAGETYPES'); ?>" class="btn cpanl-img"> <span class="icon-list-2" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_MESSAGETYPES'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=locations"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_LOCATIONS'); ?>" class="btn cpanl-img"> <span class="icon-home" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=topics"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_TOPICS'); ?>" class="btn cpanl-img"> <span class="icon-tags" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=comments"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_COMMENTS'); ?>" class="btn cpanl-img"> <span class="icon-comments-2" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=servers"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_SERVERS'); ?>" class="btn cpanl-img"> <span class="icon-database" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=podcasts"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_PODCASTS'); ?>" class="btn cpanl-img"> <span class="icon-stack" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=templates"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_TEMPLATES'); ?>" class="btn cpanl-img"> <span class="icon-grid" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span></a>
						<a href="index.php?option=com_biblestudy&amp;view=templatecodes"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_TEMPLATECODE'); ?>" class="btn cpanl-img"> <span class="icon-stack" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_TEMPLATECODE'); ?> </span></a>
=======
    <p><?php echo JText::_('JBS_CPL_INTRO') . ' - <a href="//www.joomlabiblestudy.org/jbs-documentation.html" target="_blank">' . JText::_('JBS_CPL_ONLINE_DOCUMENTATION') . '</a> - <a href="//www.joomlabiblestudy.org/forum/" target="_blank">' . JText::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
</div>
<?php if (!BIBLESTUDY_CHECKREL)
{
	?>
<div style="border:1px solid #ddd; background:#FBFBFB;">
    <h3 style="text-align: center">
		<?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
    </h3>
>>>>>>> Joomla-Bible-Study/master

						<a href="index.php?option=com_biblestudy&amp;view=styles"
						   style="text-decoration:none;"
						   title="<?php echo JText::_('JBS_CMN_STYLES'); ?>" class="btn cpanl-img"> <span class="icon-contract-2" style="margin-left:auto; margin-right:auto; font-size:24px; padding:2px; line-height:16px;"></span> <span> <?php echo JText::_('JBS_CMN_STYLES'); ?> </span></a>
					</div>
					<div style="clear: both;"></div>
				</div>
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
						$today     = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
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
