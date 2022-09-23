<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Lib\CWMStats;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

defined('_JEXEC') or die;

// Load the tooltip behavior.
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect')
	->useStyle('com_proclaim.cwmcore');

$msg   = '';
$input = new Input;
$msg   = $input->get('msg');

if ($msg)
{
	echo $msg;
}

$simple = CWMHelper::getSimpleView();
?>
<!-- Header -->
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cpanel'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div class="row">
		<?php if ($this->hasPostInstallationMessages): ?>
			<div class="alert alert-info">
				<h3>
					<?php echo Text::_('JBS_CPL_PIM_TITLE'); ?>
				</h3>

				<p>
					<?php echo Text::_('JBS_CPL_PIM_DESC'); ?>
				</p>
				<a href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $this->extension_id); ?>"
				   class="btn btn-primary btn-large">
					<?php echo Text::_('JBS_CPL_PIM_BUTTON'); ?>
				</a>
			</div>
		<?php elseif (is_null($this->hasPostInstallationMessages)): ?>
			<div class="alert alert-error">
				<h3>
					<?php echo Text::_('JBS_CPL_PIM_ERROR_TITLE'); ?>
				</h3>

				<p>
					<?php echo Text::_('JBS_CPL_PIM_ERROR_DESC'); ?>
				</p>
				<a href="https://www.christianwebministries.org/jbs-documentation.html"
				   class="btn btn-primary btn-large">
					<?php echo Text::_('JBS_CPL_PIM_ERROR_BUTTON'); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ($simple->mode === 1 && $simple->display === 1)
		{
			?>
			<div class="alert alert-info">
				<h3>
					<?php echo Text::_('JBS_CPANEL_SIMPLE_MODE_ON'); ?>
				</h3>

				<p>
					<?php echo Text::_('JBS_CPANEL_SIMPLE_MODE_DESC'); ?>
				</p>
				<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmadmin.edit&id=1'); ?>"
				   class="btn btn-primary btn-large">
					<?php echo Text::_('JBS_CPANEL_SIMPLE_MODE_LINK'); ?>
				</a>
			</div>
		<?php }
		?>
		<div class="col-lg-4">
			<div id="fbheader">
				<a href="<?php echo Route::_('index.php?option=com_proclaim&view=cpanel'); ?>"><img
							src="../media/com_proclaim/images/proclaim.jpg"
							alt="<?php echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?>"
							style="width:100px;height:100px;"/></a>
			</div>
			<div id="fbmenu">
				<strong><?php echo Text::_('JBS_CPL_VERSION_INFORMATION'); ?></strong>

				<div class="fbmainmenu"><?php echo $this->xml->version . ' (' . $this->xml->creationDate . ')'; ?></div>
			</div>
		</div>
		<div class="col-lg-8">
			<h3><?php echo Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY'); ?></h3>
			<br/>
			<p><?php echo Text::_('JBS_CPL_INTRO'); ?></p>
			<p><a href="https://www.christianwebministries.org/documentation/8-proclaim.html"
			      target="_blank">
					<?php echo Text::_('JBS_CPL_ONLINE_DOCUMENTATION'); ?></a> - <a
						href="https://www.christianwebministries.org/support/user-help-forum.html"
						target="_blank">
					<?php echo Text::_('JBS_CPL_VISIT_FAQ'); ?></a></p>
		</div>
		<div style="border:1px solid #ddd; background:#FBFBFB;" class="visible-desktop">
			<h3 style="text-align: center;">
				<?php echo Text::_('JBS_CPL_MENUE_LINKS'); ?>
			</h3>
			<div class="container">
				<div class="row">
					<div class="well well-small">
						<div id="dashboard-icons" class="col" style="white-space:normal;">
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;task=cwmadmin.edit&amp;id=1'); ?>"
							   title="<?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?>" class="btn"> <i
										class="icon-big icon-options"> </i>
								<span><?php echo Text::_('JBS_CMN_ADMINISTRATION'); ?></span></a>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessages'); ?>"
							   title="<?php echo Text::_('JBS_CMN_STUDIES'); ?>" class="btn"> <i
										class="icon-big icon-book"></i>
								<span><?php echo Text::_('JBS_CMN_STUDIES'); ?></span></a>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmediafiles'); ?>"
							   title="<?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?>" class="btn"> <i
										class="icon-big icon-video"></i>
								<span><?php echo Text::_('JBS_CMN_MEDIA_FILES'); ?> </span></a>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmteachers'); ?>"
							   title="<?php echo Text::_('JBS_CMN_TEACHERS'); ?>" class="btn"> <i
										class="icon-user icon-big"></i>
								<span><?php echo Text::_('JBS_CMN_TEACHERS'); ?></span></a>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmseries'); ?>"
							   title="<?php echo Text::_('JBS_CMN_SERIES'); ?>" class="btn"> <i
										class="icon-big icon-tree-2"></i>
								<span><?php echo Text::_('JBS_CMN_SERIES'); ?></span></a>
							<?php if (!$simple->mode): ?>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmmessagetypes'); ?>"
								   title="<?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?>" class="btn"> <i
											class="icon-big icon-list-2"></i>
									<span><?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?></span></a>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmlocations'); ?>"
								   title="<?php echo Text::_('JBS_CMN_LOCATIONS'); ?>" class="btn"> <i
											class="icon-big icon-home"></i>
									<span><?php echo Text::_('JBS_CMN_LOCATIONS'); ?></span></a>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtopics'); ?>"
								   title="<?php echo Text::_('JBS_CMN_TOPICS'); ?>" class="btn"> <i
											class="icon-big icon-tags"></i>
									<span><?php echo Text::_('JBS_CMN_TOPICS'); ?></span></a>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmcomments'); ?>"
								   title="<?php echo Text::_('JBS_CMN_COMMENTS'); ?>" class="btn"><span
											class="icon-big icon-comments-2"></span>
									<span><?php echo Text::_('JBS_CMN_COMMENTS'); ?></span></a>
							<?php endif; ?>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmservers'); ?>"
							   title="<?php echo Text::_('JBS_CMN_SERVERS'); ?>" class="btn"><span
										class="icon-big icon-database"></span>
								<span><?php echo Text::_('JBS_CMN_SERVERS'); ?></span></a>
							<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmpodcasts'); ?>"
							   title="<?php echo Text::_('JBS_CMN_PODCASTS'); ?>" class="btn"><span
										class="icon-big fa-solid fa-podcast"></span>
								<span><?php echo Text::_('JBS_CMN_PODCASTS'); ?></span></a>
							<?php if (!$simple->mode): ?>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplates'); ?>"
								   title="<?php echo Text::_('JBS_CMN_TEMPLATES'); ?>" class="btn"><span
											class="icon-big icon-grid"></span>
									<span><?php echo Text::_('JBS_CMN_TEMPLATES'); ?></span></a>
								<a href="<?php echo Route::_('index.php?option=com_proclaim&amp;view=cwmtemplatecodes'); ?>"
								   title="<?php echo Text::_('JBS_CMN_TEMPLATECODE'); ?>" class="btn"> <span
											class="icon-big fa-solid fa-file-code"></span>
									<span><?php echo Text::_('JBS_CMN_TEMPLATECODE'); ?></span></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

		</div>
		<div class="clearfix"></div>
		<!-- BEGIN: STATS -->
		<div class="fbstatscover hidden-phone">
			<table class="fbstat table table-striped table-responsive">

				<col class="col1">
				<col class="col2">
				<col class="col1">
				<col class="col2">
				<thead class="thead-light">
				<tr><p style="text-align: center;"><?php echo Text::_('JBS_CPL_GENERAL_STAT'); ?> </p></tr>
				<tr>
					<th><?php echo Text::_('JBS_CPL_STATISTIC'); ?></th>
					<th><?php echo Text::_('JBS_CPL_VALUE'); ?></th>
					<th><?php echo Text::_('JBS_CPL_STATISTIC'); ?></th>
					<th><?php echo Text::_('JBS_CPL_VALUE'); ?></th>
				</tr>
				</thead>
				<?php
				$yesterday = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
				$lastmonth = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y") - 1);
				$today     = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
				?>
				<tbody>
				<tr>
					<td><?php echo Text::_('JBS_CPL_TOTAL_MESSAGES'); ?></td>
					<td><strong><?php echo CWMStats::get_total_messages(); ?></strong></td>
					<td><?php echo Text::_('JBS_CPL_TOTAL_COMMENTS'); ?></td>
					<td><strong><?php echo CWMStats::get_total_comments(); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo Text::_('JBS_CPL_TOTAL_TOPICS'); ?></td>
					<td><strong><?php echo CWMStats::get_total_topics(); ?></strong></td>
					<td><?php echo Text::_('JBS_CPL_TOTAL_MEDIA_FILES'); ?></td>
					<td><strong><?php echo CWMStats::total_media_files(); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo Text::_('JBS_CPL_TOP5_STUDIES_HITS'); ?></td>
					<td><strong><?php echo CWMStats::get_top_studies(); ?></strong></td>
					<td><?php echo Text::_('JBS_CPL_TOP5_STUDIES_HITS_90DAYS'); ?></td>
					<td><strong><?php echo CWMStats::get_top_thirty_days(); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo Text::_('JBS_CPL_TOTAL_DOWNLOADS'); ?></td>
					<td><strong><?php echo CWMStats::total_downloads(); ?></strong></td>
					<td><?php echo Text::_('JBS_CPL_TOP5_DOWNLOADS'); ?></td>
					<td><strong><?php echo CWMStats::get_top_downloads(); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo Text::_('JBS_CPL_TOP5_DOWNLOADS_LAST_90DAYS'); ?></td>
					<td><strong><?php echo CWMStats::get_downloads_ninety(); ?></strong></td>
					<td></td>
					<td><strong></strong></td>
				</tr>
				<tr>
					<td> <?php echo Text::_('JBS_CPL_TOP_STUDIES_HITS_PLAYS_DOWNLOADS'); ?></td>
					<td><strong><?php echo CWMStats::top_score(); ?></strong></td>
					<td></td>
					<td></td>
				</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both;"></div>
	</div>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
