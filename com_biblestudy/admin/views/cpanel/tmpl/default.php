<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
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

			<div id="jbspaypal">
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7R9D3SCEYNAHE"
				   target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0"
				                        alt="PayPal - The safer, easier way to pay online!"> </a>
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

					<p><?php echo JText::_('JBS_CPL_INTRO'); ?> - <a
								href="http://www.joomlabiblestudy.org/jbs-documentation.html" target="_blank">
							<?php echo JText::_('JBS_CPL_ONLINE_DOCUMENTATION'); ?></a> - <a
								href="http://www.joomlabiblestudy.org/forum/" target="_blank">
							<?php echo JText::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
				</div>
				<div style="border:1px solid #ddd; background:#FBFBFB;" class="visible-desktop">
					<h3 style="text-align: center;">
						<?php echo JText::_('JBS_CPL_MENUE_LINKS'); ?>
					</h3>
					<div class="row-fluid">
						<div class="span12">
							<div class="well well-small">
								<div id="dashboard-icons" class="btn-group" style="white-space:normal;">
									<a href="index.php?option=com_biblestudy&amp;task=admin.edit&amp;id=1"
									   title="<?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?>" class="btn"> <i
												class="icon-big icon-options"> </i>
										<span><br/> <?php echo JText::_('JBS_CMN_ADMINISTRATION'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=messages"
									   title="<?php echo JText::_('JBS_CMN_STUDIES'); ?>" class="btn"> <i
												class="icon-big icon-book"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_STUDIES'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=mediafiles"
									   title="<?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?>" class="btn"> <i
												class="icon-big icon-video"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=teachers"
									   title="<?php echo JText::_('JBS_CMN_TEACHERS'); ?>" class="btn"> <i
												class="icon-user icon-big"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_TEACHERS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=series"
									   title="<?php echo JText::_('JBS_CMN_SERIES'); ?>" class="btn"> <i
												class="icon-big icon-tree-2"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_SERIES'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=messagetypes"
									   title="<?php echo JText::_('JBS_CMN_MESSAGETYPES'); ?>" class="btn"> <i
												class="icon-big icon-list-2"></i><br/>
										<span> <?php echo JText::_('JBS_CMN_MESSAGETYPES'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=locations"
									   title="<?php echo JText::_('JBS_CMN_LOCATIONS'); ?>" class="btn"> <i
												class="icon-big icon-home"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_LOCATIONS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=topics"
									   title="<?php echo JText::_('JBS_CMN_TOPICS'); ?>" class="btn"> <i
												class="icon-big icon-tags"></i>
										<span><br/> <?php echo JText::_('JBS_CMN_TOPICS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=comments"
									   title="<?php echo JText::_('JBS_CMN_COMMENTS'); ?>" class="btn"> <span
												class="icon-big icon-comments-2"></span><br/>
										<span> <?php echo JText::_('JBS_CMN_COMMENTS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=servers"
									   title="<?php echo JText::_('JBS_CMN_SERVERS'); ?>" class="btn"> <span
												class="icon-big icon-database"></span>
										<span><br/> <?php echo JText::_('JBS_CMN_SERVERS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=podcasts"
									   title="<?php echo JText::_('JBS_CMN_PODCASTS'); ?>" class="btn"> <span
												class="icon-big icon-stack"></span>
										<span><br/> <?php echo JText::_('JBS_CMN_PODCASTS'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=templates"
									   title="<?php echo JText::_('JBS_CMN_TEMPLATES'); ?>" class="btn"> <span
												class="icon-big icon-grid"></span>
										<span><br/> <?php echo JText::_('JBS_CMN_TEMPLATES'); ?> </span></a>
									<a href="index.php?option=com_biblestudy&amp;view=templatecodes"
									   title="<?php echo JText::_('JBS_CMN_TEMPLATECODE'); ?>" class="btn"> <span
												class="icon-big icon-stack"></span>
										<span><br/> <?php echo JText::_('JBS_CMN_TEMPLATECODE'); ?> </span></a>
								</div>
							</div>
						</div>
					</div>

				</div>
				<div class="clearfix"></div>
				<!-- BEGIN: STATS -->
				<div class="fbstatscover hidden-phone">
					<table cellspacing="1" border="0" width="100%" class="fbstat table">
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
