<?php
/**
 * Default Main
 *
 * @package    Proclaim.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMIcon;

// Create shortcuts to some parameters.
/** @type Joomla\Registry\Registry $params */
$params = $this->item->params;
$user = Factory::getApplication()->getSession()->get('user');
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
		<div class="col-lg-12">
			<?php echo $this->subscribe; ?>
		</div>
	</div>
<?php
}
?>
    <div class="page-header">
        <h1 itemprop="headline">
            <?php if ($this->item->params->get('details_show_header') > 0) {
                if ($this->item->params->get('details_show_header') == 1) {
                    echo $this->item->studytitle;
                } else {
                    echo $this->item->scripture1;
                }
            }?>		</h1>
    </div>
<?php
    if ($this->item->params->get('showrelated') === '1')
{
	?>
	<div class="row-fluid">
		<div class="col-lg-12">
			<?php echo $this->related; ?>
		</div>
	</div>
<?php
}
?>


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
try
{
	$list = $listing->getFluidListing($this->item, $this->item->params, $this->template, $type = 'sermon');
}
catch (Exception $e)
{
}
echo $list;
?>

	<!-- End Fluid Layout -->

<?php
echo $this->passage;
?>
    <hr/> <?php

echo $this->item->studytext;

?>
<?php
if ($this->item->params->get('showrelated') === '2')
{
	echo $this->related;
}



