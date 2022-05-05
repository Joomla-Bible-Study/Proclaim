<?php
/**
 * Teachers view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$listing = new CWMListing;
$classelement = $listing->createelement($this->params->get('teachers_element'));
?>
<div class="container">
	<div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
		<?php if ($classelement) : ?>
			<<?php echo $classelement; ?> class="componentheading">
		<?php endif; ?>
		<?php echo $this->params->get('teacher_title', Text::_('JBS_TCH_OUR_TEACHERS')); ?>
		<?php if ($classelement) : ?>
	</<?php echo $classelement; ?> >
	<?php endif; ?>
</div>
<div class="row">
	<div class="col-12">
		<?php
		if ($this->params->get('teacher_headercode'))
		{
			echo HtmlHelper::_('content.prepare', $this->params->get('teacher_headercode'), '', 'com_proclaim.teachers');
		}
		?>
	</div>
</div>
<div class="row">
	<div class="col-12">
		<?php
		echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'teachers');
		?>
	</div>
</div>
<?php foreach ($this->items as $item){ ?>
<div class="row">
    <div class="table-responsive">
        <table class="table w-auto table-borderless">
            <tr><td rowspan="6"><img src="<?php echo Uri::base().$item->teacher_thumbnail; ?>"></td></tr>
            <tr><td>Tom Fuller, Pastor</td><td>Calvary Chapel</td><td>email address here</td></tr>
            <tr><td colspan="3">The end, row 6 The end, row 6 The end, row 6 The end, row 6 The end, row 6 The end, row 6 The end, row 6 The end, row 6 The end, row 6</td></tr>
            <tr></tr>
            <tr></tr>
            <tr><td>Something in row 5</td></tr>
                    </table>
    </div>
</div>
    <hr>
<?php } ?>
<div class="listingfooter">
	<?php
	echo $this->page->pagelinks;
	echo $this->page->counter;
	?>
</div>
<!--end of bsfooter div-->
</div>
