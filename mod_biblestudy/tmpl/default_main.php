<?php
/**
 * Main view
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

$show_link = $params->get('show_link', 1);

JLoader::register('JBSMHelper', BIBLESTUDY_PATH_ADMIN_HELPERS . 'helper.php');
JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/listing.php');
$JBSMListing = new JBSMListing;
?>
<<<<<<< HEAD
<div class="container-fluid">
	<?php if (($params->get('pageheader')))
	{ ?>
		<div class="row-fluid">
			<div class="span12">
				<?php echo JHtml::_('content.prepare', $params->get('pageheader'), '', 'com_biblestudy.module'); ?>
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
=======
<div id="biblestudy" class="noRefTagger">
	<?php if($params->get('pageheader') != '<div>&nbsp;</div>') : ?>
		<div id="jbsmoduleheader"><?php echo $params->get('pageheader'); ?></div>
	<?php endif; ?>
	<!-- This div is the container for the whole page -->
	<table id="bsmsmoduletable">
		<?php
		$header = $JBSMListing->getHeader($list[0], $params, $admin_params, $template, $params->get('use_headers'), $ismodule);
		echo $header;
		?>
		<tbody>
		<?php
		$class1 = 'bsodd';
		$class2 = 'bseven';
		$oddeven = $class1;
>>>>>>> Joomla-Bible-Study/master

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
