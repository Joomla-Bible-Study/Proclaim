<?php
/**
 * Custom view
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');

/** @var JBSMListing $JBSMListing */
$JBSMListing = new JBSMListing;
/** @var Joomla\Registry\Registry $params */
$params = $this->params;
?>
<div id="biblestudy" class="noRefTagger">
    <!-- This div is the container for the whole page -->
<?php
switch ($params->get('module_wrapcode'))
{
	case '0':
		// Do Nothing
		break;
	case 'T':
		// Table
		?><table id="bsmsmoduletable" width="100%"><?php
		break;
	case 'D':
		// DIV
		?><div class="bsmsmoduletable"><?php
		break;
}

if ($params->get('module_headercode'))
{
	echo $params->get('module_headercode');
}
else
{
	include_once($path1 . 'helper.php'); /* Todo Tom you need ot look into this error. */
	$header = $JBSMListing->getHeader($list[0], $params, $admin_params, $templatemenuid, $params->get('use_headers'), $ismodule);
	echo $header;
}

foreach ($list as $row)
{
	$listing = $JBSMListing->getListingExp($row, $params, $templatemenuid);
	echo $listing;
}

switch ($params->get('module_wrapcode'))
{
	case '0':
		// Do Nothing
		break;
	case 'T':
		// Table
		?></table><?php
		break;
	case 'D':
		// DIV
		?></div><?php
		break;
}
?>
</div>
<div style="clear: both;"></div>
<div class="modulelistingfooter">
    <br/>

	<?php
	$link_text = $params->get('pagetext');

	if ($params->get('show_link') > 0)
	{
		$t = $params->get('t');

		if (!$t)
		{
			$t = JFactory::getApplication()->input->getInt('t', '1');
		}
		$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t=' . $t);
		?>
        <a href="<?php echo $link; ?>">
			<?php echo $link_text . '<br />'; ?>
        </a>
		<?php
	} // End of if view_link not 0
	?>
</div>
<!--end of footer div-->
