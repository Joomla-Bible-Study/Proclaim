<?php
/**
 * Teachers view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

?>
<div  class="container-fluid">
	<div class="row-fluid">

		<div class="span12">
			<h2><?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?></h2>
		</div>
	</div>
    <div class="row-fluid">
        <div class="span12">
            <?php if ($this->params->get('teacher_headercode')){echo JHtml::_('content.prepare', $this->params->get('teacher_headercode'),'','com_biblestudy.teachers');} ?>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <?php $listing = new JBSMListing;
            $list = $listing->getFluidListing($this->items, $this->params, $this->admin_params, $this->template, $type='teachers');
            echo $list;
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
