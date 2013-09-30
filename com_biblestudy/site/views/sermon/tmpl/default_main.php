<?php
/**
 * Default Main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers');
//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
JLoader::register('jbsMedia', BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');
JHTML::_('behavior.tooltip');
$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');

// Create shortcuts to some parameters.
$params  = $this->item->params;
//$images  = json_decode($this->item->images);
//$urls    = json_decode($this->item->urls);
$user    = JFactory::getUser();
$canEdit = $params->get('access-edit');

$JViewLegacy = new JViewLegacy;

$JViewLegacy->loadHelper('title');
$JViewLegacy->loadHelper('teacher');
$JBSMTeacher = new JBSMTeacher;
$row         = $this->item;
?>
<div id="bsmHeader">
	<?php
	if ($this->item->params->get('showpodcastsubscribedetails') == 1)
	{
		echo $this->subscribe;
	}
	if ($this->item->params->get('showrelated') == 1)
	{
		echo $this->related;
	}
	?>
	<?php if (!$this->print) : ?>
	<?php if ($canEdit || $params->get('show_print_view') || $params->get('show_email_icon')) : ?>
        <div class="btn-group pull-right buttonheading">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <i class="icon-cog"></i> <span class="caret"></span> </a>
			<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
            <ul class="dropdown-menu actions">
				<?php if ($params->get('show_print_view')) : ?>
                <li class="print-icon"> <?php echo JHtml::_('icon.print_popup', $this->item, $params); ?> </li>
				<?php endif; ?>
				<?php if ($params->get('show_email_icon')) : ?>
                <li class="email-icon"> <?php echo JHtml::_('icon.email', $this->item, $params); ?> </li>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
                <li class="edit-icon"> <?php echo JHtml::_('icon.edit', $this->item, $params); ?> </li>
				<?php endif; ?>
            </ul>
        </div>
		<?php endif; ?>
	<?php else : ?>
    <div class="pull-right">
		<?php echo JHtml::_('icon.print_screen', $this->item, $params); ?>
    </div>
	<?php endif; ?>

	<?php
	// Social Networking begins here
	if ($this->item->admin_params->get('socialnetworking') > 0)
	{
		?>
        <div id="bsms_share">
			<?php
			echo $this->page->social;
			?>
        </div>
		<?php
	} // End Social Networking
	?>
    <!-- Begin Fluid layout -->
    <div class="container-fluid">
        <?php $listing = new JBSMListing;
        $list = $listing->getFluidListing($this->item, $this->item->params, $this->item->admin_params, $this->template);
        echo $list;
        ?>
    </div>
    <!-- End Fluid Layout -->
	<?php
	if ($this->item->params->get('show_teacher_view') > 0)
	{
		//$teacher = $JBSMTeacher->getTeacher($this->item->params, $row->teacher_id, $this->item->admin_params);
		//echo $teacher;
		?>
        </td>
    <td>
        <?php
	}
	if ($this->item->params->get('title_line_1') + $this->item->params->get('title_line_2') > 0)
	{
		//$title = $JBSMTeacher->getTitle($this->item->params, $row, $this->item->admin_params, $this->template);
		//echo $title;
	}
	?>
</div><!-- header -->
<div>
    <table class="table table-striped" id="bsmsdetailstable">
        <thead>
		<?php
		if ($this->item->params->get('use_headers_view') > 0 || $this->item->params->get('list_items_view') < 1)
		{
			/*$JViewLegacy->loadHelper('header');
			$header = $JBSMTeacher->getHeader(
				$row, $this->item->params, $this->item->admin_params, $this->template,
				$showheader = $this->item->params->get('use_headers_view'), $ismodule = 0
			);
			echo $header;*/
		}
		?>
        </thead>
        <tbody>
		<?php
		if ($this->item->params->get('list_items_view') == 1)
		{
			?> <!-- Media table listing view -->
			<?php
		//	$media   = new jbsMedia;
		//	$listing = $media->getMediaTable($row, $this->item->params, $this->item->admin_params);
		//	echo $listing;
			?>
			<?php
		}
		if ($this->item->params->get('list_items_view') == 0)
		{
			?><!-- List items view -->
			<?php
		//	$oddeven = 'bsodd';
		//	$listing = $JBSMTeacher->getListing($row, $this->item->params, $oddeven, $this->item->admin_params, $this->template, $ismodule = 0);
		//	echo $listing;
		}
		?>
        </tbody>
    </table>
	<?php
	echo $this->passage;

	echo $this->item->studytext;

	?>
	<?php
	if ($this->item->params->get('showrelated') == 2)
	{
		echo $this->related;
	}
	?>
	<?php
	if ($this->item->params->get('showpodcastsubscribedetails') == 2)
	{
		echo $this->subscribe;
	}
	?>
</div>
