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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use CWM\Component\Proclaim\Site\Helper\CWMListing;

$show_link = $params->get('show_link', 1);

$Listing = new CWMListing;
?>
<div class="container-fluid JBSM">
	<?php if ($params->get('pageheader'))
	{
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php echo HtmlHelper::_('content.prepare', $params->get('pageheader'), '', 'com_proclaim.module'); ?>
			</div>
		</div>
	<?php
}
	?>
	<div class="row-fluid">
		<div class="span12">
			<?php
			$list = $Listing->getFluidListing($items, $params, $template, $type = "sermons");
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
