<?php
/**
 * Teachers view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
$listing      = new JBSMListing;
$classelement = $listing->createelement($this->params->get('teachers_element'));
?>
<div class="container-fluid">
	<div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
		<?php if ($classelement) : ?>
			<<?php echo $classelement; ?> class="componentheading">
		<?php endif; ?>
		<?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?>
		<?php if ($classelement) : ?>
	</<?php echo $classelement; ?> >
	<?php endif; ?>
</div>
<div class="row-fluid">
	<div class="span12">
		<?php
		if ($this->params->get('teacher_headercode'))
		{
			echo JHtml::_('content.prepare', $this->params->get('teacher_headercode'), '', 'com_proclaim.teachers');
		}
		?>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<?php
		echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'teachers');
		?>
	</div>
</div>

<div class="listingfooter">
	<?php
	echo $this->page->pagelinks;
	echo $this->page->counter;
	?>
</div>
<!--end of bsfooter div-->
</div>
