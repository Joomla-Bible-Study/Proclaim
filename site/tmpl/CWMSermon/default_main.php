<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMIcon;

defined('_JEXEC') or die;

//HtmlHelper::addIncludePath(JPATH_COMPONENT . '/helpers');
//HtmlHelper::_('behavior.tooltip');
//HtmlHelper::_('behavior.caption');

// Create shortcuts to some parameters.

/** @type Joomla\Registry\Registry $params */
$params = $this->item->params;
$user = Factory::getUser();
$canEdit = $params->get('access-edit');

$JViewLegacy = new JViewLegacy;

$JViewLegacy->loadHelper('title');
$JViewLegacy->loadHelper('teacher');
$row = $this->item;
?>

<?php
if ($this->item->params->get('showpodcastsubscribedetails') === '1')
{
	?>
	<div class="row-fluid">
		<div class="span12">
			<?php echo $this->subscribe; ?>
		</div>
	</div>
<?php
}
if ($this->item->params->get('showrelated') === '1')
{
	?>
	<div class="row-fluid">
		<div class="span12">
			<?php echo $this->related; ?>
		</div>
	</div>
<?php
}
?>
<?php if (!$this->print) : ?>
	<?php if ($canEdit || $params->get('show_print_view') || $params->get('show_email_icon')) : ?>
		<div class="btn-group pull-right buttonheading">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <i class="icon-cog"></i>
				<span class="caret"></span> </a>
			<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
			<ul class="dropdown-menu actions">
				<?php if ($params->get('show_print_view')) : ?>
					<li class="print-icon"> <?php echo CWMIcon::print_popup($this->item, $params); ?> </li>
				<?php endif; ?>
				<?php if ($params->get('show_email_icon')) : ?>
					<li class="email-icon"> <?php echo CWMIcon::email( $this->item, $params); ?> </li>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
					<li class="edit-icon"> <?php echo CWMIcon::edit( $this->item, $params); ?> </li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>
<?php else : ?>
	<div id="pop-print" class="btn hidden-print">
		<?php echo HtmlHelper::_('icon.print_screen', $this->item, $params); ?>
	</div>
<?php endif; ?>

<?php
// Social Networking begins here
if ($this->item->params->get('socialnetworking') > 0)
{
	?>
	<?php
	echo $this->page->social;
}
// End Social Networking
?>
	<!-- Begin Fluid layout -->

<?php $listing = new CWMListing;
$list = $listing->getFluidListing($this->item, $this->item->params, $this->template, $type = 'sermon');
echo $list;
?>

	<!-- End Fluid Layout -->

<?php
echo $this->passage;

echo $this->item->studytext;

?>
<?php
if ($this->item->params->get('showrelated') === '2')
{
	echo $this->related;
}
?>
<?php
if ($this->item->params->get('showpodcastsubscribedetails') === '2')
{
	echo $this->subscribe;
}
