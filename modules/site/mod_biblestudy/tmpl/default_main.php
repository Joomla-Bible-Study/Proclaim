<?php
/**
 * Main view
 *
 * @package     Proclaim
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

$show_link = $params->get('show_link', 1);

JLoader::register('JBSMHelper', BIBLESTUDY_PATH_ADMIN_HELPERS . 'helper.php');
JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/CWMListing.php');
$JBSMListing = new JBSMListing;

// Load CSS framework for displaying properly.
JHtml::_('biblestudy.framework');
JHtml::_('biblestudy.loadCss', $params, null, 'font-awesome');

?>
<div class="container-fluid JBSM">
	<?php if ($params->get('pageheader'))
	{
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php echo JHtml::_('content.prepare', $params->get('pageheader'), '', 'com_proclaim.module'); ?>
			</div>
		</div>
	<?php
}
	?>
	<div class="row-fluid">
		<div class="span12">
			<?php
			$list = $JBSMListing->getFluidListing($items, $params, $template, $type = "sermons");
			echo $list;
			?>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<?php
			if ($params->get('show_link') > 0)
			{
				echo $link;
			}
			?>
		</div>
	</div>
	<!--end of footer div-->
</div> <!--end container -->
<div style="clear: both;"></div>
